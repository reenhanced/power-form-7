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
 * @author     Nick Hance <nhance@reenhanced.com>
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
  public static function get_webhook_urls(WPCF7_ContactForm $contact_form) {
    return $contact_form->prop('pf7_webhooks');
  }

	public function __construct(WPCF7_ContactForm $contact_form, WPCF7_Submission $submission = null) {
    $this->contact_form = $contact_form;
    $this->submission   = $submission;
  }

  public function to_json() {
    $meta        = array();
    $posted_data = array();

    if (!empty($this->submission)) {
      $posted_data = $this->submission->get_posted_data();

      foreach(array('timestamp', 'remote_ip',
                    'user_agent', 'url',
                    'current_user_id') as $meta_key) {
                      $meta[$meta_key] = $this->submission->get_meta($meta_key);
                    }

      // TODO: Support additional meta values
      // Reference: https://github.com/takayukister/contact-form-7/blob/bdedf40684/modules/flamingo.php#L51

      // TODO: Support files
      // Reference: https://github.com/Vizir/cf7-to-zapier/blob/master/modules/cf7/class-module-cf7.php#L262

      // NOTE: Files are stripped out of the submission before we get them.
      // Reference: https://github.com/takayukister/contact-form-7/blob/28efbe9273/includes/submission.php#L98
      // TODO: Possible to get them via mail tags similar to how flamingo does it?
    }

    return array_merge($posted_data, $meta);
  }
  
  /**
   * Sends the data of this instance to all webhooks registered for the given form
   */
  public function send_to_azure() {
    $webhook_urls = self::get_webhook_urls($this->contact_form);

    Power_Form_7::log(__METHOD__ . '() - json to send to webhooks: ' . print_r( $this->to_json(), 1 ) );
    Power_Form_7::log(__METHOD__ . '() - webhooks: ' . print_r( $webhook_urls, 1 ) );
    
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

    Power_Form_7::log(__METHOD__ . '() - results: ' . print_r( $results, 1 ) );

    return $results;
  }

}
