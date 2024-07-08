== Power Form 7: Power Automate Connector for Contact Form 7 ==
Contributors: reenhanced
Tags: cf7, contact form, power automate, integration, contact form 7, flow, reenhanced
Requires at least: 5.4
Tested up to: 6.6.0
Stable tag: 2.2.6
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrate Contact Form 7 with Microsoft Power Automate. Trigger flows and/or submit data to any Contact Form 7 form.

== Description ==

[Contact Form 7 (CF7)](https://wordpress.org/plugins/contact-form-7/) is a form creation plugin used by 5+ million WordPress websites.

[Power Automate](https://flow.microsoft.com/) is an integration service provided by Microsoft that allows you to connect over 1000 services together easily.

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

== Changelog ==

= 2.2.7 =
* Compatibility with WordPress 6.6.0
* Removes plugin update check during license activation. Resolves issues on some sites which hooked into 'update_plugins' transient changes.

= 2.2.6 =
* Removes debug messaging related to license requests. No functional changes.
* Updating to this version is highly recommended.
* If you have previously enabled debug mode, please delete the pf7.log file in your wp-content/uploads directory.

= 2.2.5 =
* Compatibility with WordPress 6.4.1

= 2.2.2 =
* Typecasts number fields to float or int regardless of what Contact Form 7 sends

= 2.2.1 =
* If extra field types are included that can't be handled, ignore them in the output and continue

= 2.2.0 =
* Support multiple file uploads
* Resolve an issue where some dropdown and checkbox values could not be sent
* IMPORTANT: File field type changes to an array with this release. If you are using files, you will need to modify your flows.

= 2.1.1 =
* Resolves an issue where in some cases submissions were described incorrectly to Power Automate.
* Resolves type errors on some form submissions

= 2.1.0 =

* Power Form 7 will send submissions to Power Automate even if Contact Form 7 cannot send email

= 2.0.0 =

* Changes how form data is processed
* Supports files
* Supports pipes
* Compatible with Contact Form 7 v5.6

= 1.0.6 =

* Compatibility with WordPress 6.0

= 1.0.5 =

* Resolves an issue where triggers wouldn't fire in Contact Form 7 5.5.3

= 1.0.4 =

* Tested and confirmed with WordPress 5.8. Version bump only.

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
