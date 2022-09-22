<?php

/**
 * Processes and sends submitted forms to webhook
 *
 * @link       https://www.reenhanced.com/
 * @since      1.0.0
 *
 * @package    Power_Forms_7
 * @subpackage Power_Forms_7/includes
 */

/**
 * Processor for submitted forms
 *
 * This class defines all code necessary to process and send forms to webhooks
 *
 * @since      1.0.0
 * @package    Power_Forms_7
 * @subpackage Power_Forms_7/includes
 */
class Power_Form_7_Connector {

	public $contact_form;
	public $submission;

	/**
	 * Returns webhook urls for contact form.
	 *
	 * Returns the webhook urls for the contact form specified
	 *
	 * @since    1.0.0
	 * @param    WPCF7_ContactForm $contact_form The contact form to look up
	 * @return   string[]  An array of urls for the webhooks. If empty, no webhooks exist for contact form
	 */
	public static function get_webhook_urls( WPCF7_ContactForm $contact_form) {
		return $contact_form->prop( 'pf7_webhooks' );
	}

	public function __construct( WPCF7_ContactForm $contact_form, WPCF7_Submission $submission = null ) {
		$this->contact_form = $contact_form;
		$this->submission   = $submission;
	}

	public function to_json() {
		$meta        = array();
		$posted_data = array();

		if (!empty($this->submission)) {
			foreach ( array(
				'timestamp',
				'remote_ip',
				'user_agent',
				'url',
				'current_user_id' ) as $meta_key) {
					$meta[$meta_key] = $this->submission->get_meta($meta_key);
			}

			// TODO: Support additional meta values
			// Reference: https://github.com/takayukister/contact-form-7/blob/bdedf40684/modules/flamingo.php#L51

			$posted_data = $this->extract_form_data();
		}

		return array_merge($posted_data, $meta);
	}

	/**
	 * Gets the form data from the submission
	 *
	 * @return array Key-Value array of form data
	 */
	public function extract_form_data() {
		$data = array();

		$uploaded_files = ( ! empty( $this->submission ) ) ? $this->submission->uploaded_files() : [];

		// Upload Info
		$wp_upload_dir = wp_get_upload_dir();
		$upload_path   = PF7_UPLOAD_DIR . '/' . $this->contact_form->id() . '/' . uniqid();

		$upload_url = $wp_upload_dir['baseurl'] . '/' . $upload_path;
		$upload_dir = $wp_upload_dir['basedir'] . '/' . $upload_path;

		$tags = $this->contact_form->scan_form_tags();
		foreach ( $tags as $tag ) {
			if ( empty( $tag->name ) ) {
				continue;
			}

			// Field type info
			$field_type = Power_Form_7::get_field_type( $tag->name, $tag->basetype );

			// Regular Tags
			$value = ( ! empty( $_POST[ $tag->name ] ) ) ? $_POST[ $tag->name ] : '';

			if ( is_array( $value ) ) {
				foreach ( $value as $key => $v ) {
						$value[ $key ] = stripslashes( $v );
				}
			}

			if ( is_string( $value ) ) {
				$value = stripslashes( $value );

				// Typecast array items send to us as strings into array
				if ( 'array' === $field_type['type'] ) {
					$value = array( $value );
				}

				// Typecast number items to numbers
				if ( 'number' === $field_type['type'] ) {
					if ( strpos( $value, '.' ) !== false ) {
						$value = floatval( $value );
					} else {
						$value = intval( $value );
					}
				}
			}

			// Files
			if ( 'file' === $tag->basetype && ! empty( $uploaded_files[ $tag->name ] ) ) {
				$files = $uploaded_files[ $tag->name ];

				$copied_files = [];
				foreach ( (array) $files as $file ) {
					wp_mkdir_p( $upload_dir );

					$filename = wp_unique_filename( $upload_dir, $tag->name . '-' . basename( $file ) );

					if ( ! copy( $file, $upload_dir . '/' . $filename ) ) {
						Power_Form_7::log(__METHOD__ . "() - FAILED copy of file to {$upload_dir}/{$filename}: " . print_r( $file, 1 ) );
						$this->submission->set_status( 'mail_failed' );
						$this->submission->set_response( $this->contact_form->message( 'upload_failed' ) );

						$copied_files[] = "Could not attach {$filename}. Please ensure contact form 7 can send email.";
						continue;
					} else {
						$copied_files[] = $upload_url . '/' . $filename;
					}
				}

				$value = $copied_files;
			}

			// Support to Pipes
			$pipes = $tag->pipes;
			if ( WPCF7_USE_PIPE && $pipes instanceof WPCF7_Pipes && ! $pipes->zero() ) {
				if ( is_array( $value) ) {
					$new_value = [];

					foreach ( $value as $v ) {
						$new_value[] = $pipes->do_pipe( wp_unslash( $v ) );
					}

					$value = $new_value;
				} else {
					$value = $pipes->do_pipe( wp_unslash( $value ) );
				}
			}

			// Support to Free Text on checkbox and radio
			if ( $tag->has_option( 'free_text' ) && in_array( $tag->basetype, [ 'checkbox', 'radio' ] ) ) {
				$free_text_label = end( $tag->values );
				$free_text_name  = $tag->name . '_free_text';
				$free_text_value = ( ! empty( $_POST[ $free_text_name ] ) ) ? $_POST[ $free_text_name ] : '';

				if ( is_array( $value ) ) {
					foreach ($value as $key => $v) {
						if ($v !== $free_text_label) {
							continue;
						}

						$value[$key] = stripslashes($free_text_value);
					}
				}

				if ( is_string( $value ) && $value === $free_text_label ) {
					$value = stripslashes($free_text_value);
				}
			}

			$key          = $tag->name;
			$data[ $key ] = $value;
		}

		/**
		 * You can filter data retrieved from Contact Form tags with 'pf7_extract_contact_form_data'
		 *
		 * @param $data             Array 'field => data'
		 * @param $contact_form     ContactForm obj from 'wpcf7_mail_sent' action
		 * @since 2.0.0
		 */
		return apply_filters( 'pf7_extract_contact_form_data', $data, $this->contact_form );
	}
	
	/**
	 * Sends the data of this instance to all webhooks registered for the given form
	 */
	public function send_to_azure() {
		$webhook_urls = self::get_webhook_urls($this->contact_form);

		Power_Form_7::log(__METHOD__ . '() - json to send to webhooks: ' . var_export( $this->to_json(), 1 ) );
		Power_Form_7::log(__METHOD__ . '() - webhooks: ' . print_r( $webhook_urls, 1 ) );

		$this->submission->add_result_props(
			array(
				'power-form-7' => $webhook_urls
			)
		);
		
		$content_type = 'application/json';

		$blog_charset = get_option( 'blog_charset' );
		if ( ! empty( $blog_charset ) ) {
				$content_type .= '; charset=' . get_option( 'blog_charset' );
		}

		$args = array(
				'method'    => 'POST',
				'body'      => json_encode($this->to_json()),
				'headers'   => array(
						'Content-Type'  => $content_type,
				),
		);

		$results = array();

		foreach ($webhook_urls as $webhook_url) {
			$results[] = wp_remote_post($webhook_url, apply_filters('pf7_post_request_args', $args));
		}

		$this->submission->add_result_props( array(
				'power-form-7-results' => array(
					'results' => $results
				)
			)
		);

		Power_Form_7::log(__METHOD__ . '() - results: ' . print_r( $results, 1 ) );

		return $results;
	}

}
