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
      'type' => 'object',
      'properties' => array(
        'id' => array(
          'type' => 'integer',
          'format' => 'int32',
          'x-ms-summary' => 'Form ID',
          'description' => 'The Contact Form 7 Form ID'
        ),
        'slug' => array(
          'type' => 'string',
          'x-ms-summary' => 'Slug',
          'description' => 'The Contact Form 7 slug for the form'
        ),
        'title' => array(
          'type' => 'string',
          'x-ms-summary' => 'Form Name',
          'description' => 'The title or name of the Contact Form 7 form'
        ),
        'locale' => array(
          'type' => 'string',
          'x-ms-summary' => 'Form Locale',
          'description' => 'The saved locale of the Contact Form 7 form'
        ),
        'properties' => array(
          'type' => 'object',
          'properties' => array(
            'form' => array(
              'type' => 'object',
              'properties' => array(
                'fields' => array(
                  'type' => 'array',
                  'items' => array(
                    'type' => 'object',
                    'properties' => $this->get_properties_for_fields($form)
                  )
                )
              )
            )
          )
        )
      )
    );

    // TODO: This must describe the API as it comes from contact form 7 for each form.


    return $properties;
  }

  private function get_properties_for_fields(WPCF7_ContactForm $form) {
    $cf7_props = $form->get_properties();
    $fields = $form->scan_form_tags();

    $this->plugin()->log_debug( __METHOD__ . '($form) - fields: ' . print_r( $fields, 1 ) );

    $field_props = array();

    return $field_props;
  }

  private function plugin() {
    return $this->_api->plugin();
  }

  // TODO: Provide schema definitions for the given form id
  // TODO: Forms are available via API at: https://github.com/takayukister/contact-form-7/blob/master/includes/rest-api.php


}