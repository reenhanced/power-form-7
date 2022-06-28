<?php

class Pf7_Forms_Controller extends WP_Rest_Controller {
	protected $_api;

	public function __construct( $api ) {
		$this->_api = $api;
	}

	public function register_routes() {
		$this->_api->register_route('forms/(?P<id>\d+)', array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array($this, 'get_form'),
				'permission_callback' => function ( WP_REST_Request $request) {
					$id = (int) $request->get_param('id');

					// links permissions to cf7 permissions
					if (current_user_can('wpcf7_edit_contact_form', $id)) {
						return true;
					} else {
						return new WP_Error(
							'wpcf7_forbidden',
							__('You are not allowed to access the requested contact form.', 'contact-form-7'),
							array('status' => 403)
						);
					}
				}
			)
		));
	}

	public function get_form( WP_REST_Request $request ) {
		$id      = (int) $request->get_param('id');
		$context = $request->get_param( 'context', 'view' );
		$form    = wpcf7_contact_form($id);

		if (!$form) {
			return new WP_Error(
				'pf7_not_found',
				__('The requested resource was not found', 'power-form-7'),
				array('status' => 404)
			);
		}

		return rest_ensure_response($this->get_form_schema($form, $context));
	}

	private function get_form_schema( WPCF7_ContactForm $form, $context = 'view' ) {
		$cf7_props = $form->get_properties();

		$properties = array(
			'remote_ip' => array(
				'type' => 'string',
				'x-ms-summary' => 'Remote IP',
				'description' => 'IP Address of form submitter',
				'x-ms-visibility' => 'advanced'
			),
			'user_agent' => array(
				'type' => 'string',
				'x-ms-summary' => 'User Agent',
				'description' => 'User Agent of submitter',
				'x-ms-visibility' => 'advanced'
			),
			'url' => array(
				'type' => 'string',
				'x-ms-summary' => 'Form URL',
				'description' => 'URL from where the form was submitted',
				'x-ms-visibility' => 'advanced'
			),
			'current_user_id' => array(
				'type' => 'number',
				'x-ms-summary' => 'User ID',
				'description' => 'User ID of Wordpress user (0 if not logged in)',
				'x-ms-visibility' => 'advanced'
			)
		);

		$tags     = $form->scan_form_tags();
		$required = array();

		foreach ((array) $tags as $tag) {
			$skip   = false;
			$type   = 'string';
			$format = null;

			switch ($tag->basetype) {
				case 'text':
					$type = 'string';
					break;
				case 'email':
					$type = 'string';
					$format = 'email';
					break;
				case 'url':
					$type = 'string';
					$format = 'uri';
					break;
				case 'tel':
					$type = 'string';
					$format = 'phone';
					break;
				case 'date':
					$type = 'string';
					$format = 'date-time';
					break;
				case 'number':
				case 'range':
					$type = 'number';
					break;
				case 'checkbox':
					$type = ( 'edit' === $context ) ? 'array' : 'string';
					break;
				case 'select':
					$type = ( 'edit' === $context ) ? 'array' : 'string';
					break;
				case 'radio':
					$type = ( 'edit' === $context ) ? 'array' : 'string';
					break;
				case 'acceptance':
					$type = 'number';
					break;
				case 'textarea':
					$type = 'string';
					break;
				case 'file':
					$type = 'string';
					$format = 'uri';
					break;
				case 'submit':
				default:
					$this->plugin()->log_debug(__METHOD__ . '() - SKIPPED TYPE ' . $type . ' - ' . print_r($tag->name, 1));
					$skip = true;
					break;
			}

			if ($skip) {
				break;
			}

			$prop = array(
				'type' => $type,
				'x-ms-summary' => $tag->name,
				'x-ms-visibility' => 'important'
			);

			if (isset($format)) {
				$prop['format'] = $format;
			}

			if ($tag->is_required()) {
				$required[] = $tag->name;
			}

			if ( 'array' === $type ) {
				$prop['items'] = array(
					'type' => 'string'
				);
				if (isset($tag->values)) {
					$prop['items']['enum'] = array();
					foreach ($tag->values as $key => $value) {
						$prop['items']['enum'][] = $value;
					}
				}
			}

			$properties[$tag->name] = $prop;
		}

		$response = array('schema' => array(
			'type' => 'object',
			'properties' => $properties
		));

		if (!empty($required)) {
			$response['schema']['required'] = $required;
		}

		return $response;
	}

	private function plugin() {
		return $this->_api->plugin();
	}
}
