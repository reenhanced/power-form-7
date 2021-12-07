<?php

class Pf7_Webhooks_Controller extends WP_Rest_Controller {
  protected $_api;

  public function __construct($api) {
    $this->_api = $api;
  }

  public function register_routes() {
    $this->_api->register_route('webhooks/(?P<form_id>\d+)', array(
      array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => array($this, 'list_webhooks'),
        'permission_callback' => function(WP_REST_Request $request) {
          $form_id = (int) $request->get_param('form_id');

          if ( current_user_can( 'wpcf7_edit_contact_form', $form_id ) ) {
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
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => array($this, 'create_webhook'),
        'permission_callback' => function(WP_REST_Request $request) {
          $form_id = (int) $request->get_param('form_id');

          if ( current_user_can( 'wpcf7_edit_contact_form', $form_id ) ) {
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
          $id = (int) $request->get_param('form_id');

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

  public function list_webhooks(WP_REST_Request $request) {
    $form_id = (int) $request->get_param('form_id');
    $contact_form = wpcf7_contact_form($form_id);

    if (!$contact_form) {
      return new WP_Error('wpcf7_not_found',
          __('The requested contact form was not found', 'power-form-7'),
          array('status' => 404));
    }

    $webhooks = $contact_form->prop('pf7_webhooks');
    $webhooks = $contact_form->get_properties();

    $response = new WP_REST_Response($webhooks);

    return $response;
  }

  public function create_webhook(WP_REST_Request $request) {
    $form_id = (int) $request->get_param('form_id');
    $contact_form = wpcf7_contact_form($form_id);

    if (!$contact_form) {
      return new WP_Error('wpcf7_not_found',
          __('The requested contact form was not found', 'power-form-7'),
          array('status' => 404));
    }

    $callback_url = $request->get_param('callback_url');
    if (empty($callback_url)) {
      return new WP_Error('pf7_callback_empty',
          __('Callback URL must be provided', 'power-form-7'),
          array('status' => 500));
    }
    $md5 = md5($callback_url);

    $webhooks = $contact_form->prop('pf7_webhooks');
    $webhooks[$md5] = $callback_url;

    $contact_form->set_properties(array('pf7_webhooks' => $webhooks));
    $contact_form->save();

    $response = new WP_REST_Response($webhooks);
    $response->set_status(201);
    $response->header('Location', $this->_api->rest_url("webhooks/{$form_id}?md5={$md5}"));

    return $response;
  }

  public function destroy_webhook(WP_REST_Request $request) {
    $form_id = (int) $request->get_param('form_id');
    $contact_form = wpcf7_contact_form($form_id);

    if (!$contact_form) {
      return new WP_Error('wpcf7_not_found',
          __('The requested contact form was not found', 'power-form-7'),
          array('status' => 404));
    }

    $md5 = $request->get_param('md5');
    if (empty($md5)) {
      return new WP_Error('pf7_webhook_error',
          __('md5 param must be provided to remove webhook', 'power-form-7'),
          array('status' => 500));
    }

    $webhooks = $contact_form->prop('pf7_webhooks');
    unset($webhooks[$md5]);

    $contact_form->set_properties(array('pf7_webhooks' => $webhooks));
    $contact_form->save();

    $response = new WP_REST_Response($webhooks);
    $response->set_status(201);

    return $response;
  }
}