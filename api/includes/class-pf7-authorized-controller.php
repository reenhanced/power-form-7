<?php

abstract class Pf7_Authorized_Controller extends WP_REST_Controller {
  // TODO: This will automatically ensure that all requests handled have a valid license key associated with them

  private $_api;

  public function api() {
    if ($this->_api == null) {
      $this->_api = Power_Form_7_Api::get_instance();
    }

    return $this->_api;
  }
}