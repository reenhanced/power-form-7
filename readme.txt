== Power Form 7: Power Automate Connector for Contact Form 7 ==
Contributors: reenhanced
Tags: cf7, contact form, power automate, integration, contact form 7, flow, reenhanced
Requires at least: 5.4
Tested up to: 5.6  
Stable tag: trunk
Requires PHP: 5.6
License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html  

Integrate Contact Form 7 with Microsoft Power Automate. Trigger flows and/or submit data to any Contact Form 7 form.

== Description ==

[Contact Form 7 (CF7)](https://wordpress.org/plugins/contact-form-7/) is a form creation plugin used by 5+ million WordPress websites.

[Power Automate](https://flow.microsoft.com/) is an integration service provided by Microsoft that allows you to connect over 400 services together easily.

[Power Form 7](https://www.powerform7.com/) is an independent gateway that allows the simplest connection between Contact Form 7 and Power Automate.

== How to Use ==

Install Power Form 7 and activate with your license key. (Get your license here: [https://www.reenhanced.com/products/power-form-7/](https://www.reenhanced.com/products/power-form-7/) Once configured, all of your Contact Form 7 forms are available for trigger or submission on Power Automate.

=== Configuration ===

= Setting up your site =

1. Install the plugin and activate it.
2. Enter your license key and choose the Power Automate user.

= Triggering a flow from a form submission =

1. Create a new flow on Power Automate
2. Choose "When a Contact Form 7 form is submitted" as your trigger
3. Choose the domain and form you wish to use as a trigger.
4. Build your flow. Form fields are available for all future actions in your flow.
5. Done! Now submit your form to trigger your flow.

= Submitting a form from Power Automate =

1. Add the action "Submit a Contact Form 7 form" to an existing flow.
2. Choose the domain and form you wish to submit.
3. The action will show you the form fields, make sure you fill all required fields with data.
4. Done! You can now submit your form from with Power Automate.

== Review ==

If you're satisified with Power Form 7, please leave a [review here](https://wordpress.org/support/plugin/power-form-7/reviews/).

=== API Disclosure ===

This plugin uses a 3rd party API by Reenhanced to facilitate communication between Power Automate/Azure and your WordPress site. The API is used in the following manner:

- Power Automate connectors rely on a single API endpoint, but you may install this plugin on many different WordPress sites.
- Power Automate will connect to the API and the request is forwarded to your WordPress site.
- Reponses are then sent back to Power Automate/Azure.
- Response and request data is not stored.
- Your license key is used to authorize the request from Power Automate and protect your system from unauthorized use. (Do not disclose your license key. It can allow API access to your WordPress site.)
- The API is also used to validate your license.

The API is bound by the privacy policy as described here: https://reenhanced.com/privacy/

=== Support ===

For support email us at support@reenhanced.com

== Installation ==

Install [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) and activate it.

* Install "Power Form 7" by plugins dashboard.

Or

* Upload the entire `power-form-7` folder to the `/wp-content/plugins/` directory.

Then

* Activate the plugin through the 'Plugins' menu in WordPress.

You will find 'Power Automate Settings' in the menu for Contact Form 7.

== Frequently Asked Questions ==

= How can I upload files and send link to webhook? =

Files are not yet supported with this plugin and is planned in a future release.

= How I can get the free text value? =

Free text is not supported in the initial release.

== Changelog ==

= 1.0.3 = 

* Adds partial compatibility with CF7 Smart Grid.

= 1.0.2 =

* Adds support for PHP 5.6

= 1.0.0 =

* Initial release

== Upgrade Notice ==

== Screenshots ==

1. You can use a form submission to trigger a flow
2. You can submit a form from within a flow
3. Screenshot of the settings page