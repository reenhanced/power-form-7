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

	/**
	 * Returns webhook urls for contact form.
	 *
	 * Returns the webhook urls for the contact form specified
	 *
	 * @since    1.0.0
   * @param    WPCF7_Contact_Form $contact_form The contact form to look up
   * @return   string[]  An array of urls for the webhooks. If empty, no webhooks exist for contact form
	 */
  public static function get_webhook_url(WPCF7_Contact_Form $contact_form) {
    $webhook_urls = array();

    // TODO: Lookup webhook urls

    return $webhook_urls;
  }

	public function __construct($contact_form, $result) {
    // TODO: Assign contact_form and result to instance variables.
    // TODO: Write a class to return a json version of the $result.
    // TODO: Write a class to send the json version of the result to all webhooks.
	}

}
