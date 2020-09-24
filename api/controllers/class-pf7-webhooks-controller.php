<?php

class Pf7_Webhooks_Controller extends WP_Rest_Controller {
  protected $_api;

  public function __construct($api) {
    $this->_api = $api;
  }

  public function register_routes() {
    $this->_api->register_route('webhooks', array(
      array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => array($this, 'create_webhook'),
        'permission_callback' => function(WP_REST_Request $request) {
          if ( current_user_can( 'wpcf7_edit_contact_forms') ) {
            return true;
          } else {
            return new WP_Error( 'wpcf7_forbidden',
              __( "You are not allowed to access the requested contact form.", 'contact-form-7' ),
              array( 'status' => 403 )
            );
          }
        }
      ),
      array(
        'methods' => WP_REST_Server::DELETABLE,
        'callback' => array($this, 'destroy_webhook'),
        'permission_callback' => function(WP_REST_Request $request) {
          $id = (int) $request->get_param('id');

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

  public function create_webhook(WP_REST_Request $request) {
    // TODO: Find contact form specified by param form_id passed.
    // TODO: Add the callback_url to a 'pf7_webhooks' array in form properties.
    // TODO: Return a header with location param for removing callback_url

    // Reference: https://github.com/Vizir/cf7-to-zapier/blob/master/modules/cf7/class-module-cf7.php#L158
  }

  public function destroy_webhook(WP_REST_Request $request) {
    // TODO: get for by specified form_id
    // TODO: Remove callback_url from list of 'pf7_webhooks' properties
    // TODO: Return status 200
  }

  // TODO: Create webhook will get form_id and callback_url. (When form_id is submitted, data will be sent to callback_url)
  // TODO: Forms are available via API at: https://github.com/takayukister/contact-form-7/blob/master/includes/rest-api.php
}