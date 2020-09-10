<?php

class Pf7_Webhooks_Controller extends WP_Rest_Controller {
  protected $_api;

  public function __construct() {
    $this->resource_name = 'webhooks';
  }

  public function register_routes() {
    $this->api()->register_route($this->resource_name);
  }

  // TODO: Create webhook will get form_id and callback_url. (When form_id is submitted, data will be sent to callback_url)
  // TODO: Forms are available via API at: https://github.com/takayukister/contact-form-7/blob/master/includes/rest-api.php
}