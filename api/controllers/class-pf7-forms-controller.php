<?php

class Pf7_Forms_Controller extends WP_Rest_Controller {
  protected $_api;

  public function __construct($api) {
    $this->_api = $api;
  }

  public function register_routes() {
    $this->_api->register_route('forms/(?P<id>\d+)', array(
      array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => array($this, 'get_form'),
        'permission_callback' => function(WP_REST_Request $request) {
            $id = (int) $request->get_param('id');

            // links permissions to cf7 permissions
            if ( current_user_can( 'wpcf7_edit_contact_form', $id ) ) {
              return true;
            } else {
              return new WP_Error( 'wpcf7_forbidden',
                __( "You are not allowed to access the requested contact form.", 'contact-form-7' ),
                array( 'status' => 403 )
              );
            }
          }
      )
    ));
  }

  public function get_form(WP_REST_Request $request) {
    $id = (int) $request->get_param('id');
    $form = wpcf7_contact_form($id);

    if (!$form) {
      return new WP_Error('pf7_not_found',
        __("The requested resource was not found", 'power-form-7'),
        array('status' => 404));
    }
    
    // TODO: This actually needs to describe the submission, not the form request!
    // We don't give a damn about how CF7 describes the form, only how the submission comes in.

    $response = array(
      'type' => 'object',
      'properties' => $this->get_form_schema($form)
    );

    return rest_ensure_response($response);
  }

  private function get_form_schema(WPCF7_ContactForm $form) {
    $cf7_props = $form->get_properties();

    $properties = array(
      'remote_ip' => array(
        'type' => 'string',
        'x-ms-summary' => 'Remote IP',
        'description' => 'IP Address of form submitter'
      ),
      'user_agent' => array(
        'type' => 'string',
        'x-ms-summary' => 'User Agent',
        'description' => 'User Agent of submitter'
      ),
      'url' => array(
        'type' => 'string',
        'format' => 'url',
        'x-ms-summary' => 'Form URL',
        'description' => 'URL from where the form was submitted'
      ),
      'current_user_id' => array(
        'type' => 'number',
        'x-ms-summary' => 'User ID',
        'description' => 'User ID of Wordpress user (0 if not logged in)'
      )
    );

    $tags = $form->scan_form_tags();

    $this->plugin()->log_debug( __METHOD__ . '($form) - tags: ' . print_r( $tags, 1 ) );

    foreach ((array) $tags as $tag) {
      $skip = false;
      $type = 'string';
      $format = null;

      switch ($tag->basetype) {
        case 'text':
          $type = 'string';
          break;
        case 'email':
          $type = 'string';
          break;
        case 'url':
          $type = 'string';
          $format = 'url';
        case 'tel':
          $type = 'string';
          $format = 'phone';
          break;
        case 'date':
          $type = 'string';
          $format = 'date';
          break;
        case 'number':
          $type = 'number';
          break;
        case 'checkbox':
          $type = 'array';
          break;
        case 'select':
          $type = 'array';
          break;
        case 'radio':
          $type = 'array';
          break;
        case 'acceptance':
          $type = 'number';
          break;
        case 'textarea':
          $type = 'string';
          break;
        case 'file':
        case 'submit':
        default:
          $skip = true;
          break;
      }

      if ($skip) { break; }

      $prop = array(
        'type' => $type,
        'x-ms-summary' => $tag->name
      );

      if ($tag->is_required()) {
        $prop['x-ms-visibility'] = 'important';
      }

      if ($type == 'array') {
        $prop['items'] = array(
          'type' => 'string'
        );
      }

      $properties[$tag->name] = $prop;
    }

    return $properties;
  }

  private function plugin() {
    return $this->_api->plugin();
  }
}