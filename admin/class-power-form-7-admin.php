<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.reenhanced.com/
 * @since      1.0.0
 *
 * @package    Power_Form_7
 * @subpackage Power_Form_7/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Power_Form_7
 * @subpackage Power_Form_7/admin
 * @author     Nick Hance <nhance@reenhanced.com>
 */
class Power_Form_7_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Power_Form_7_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Power_Form_7_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/power-form-7-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Power_Form_7_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Power_Form_7_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/power-form-7-admin.js', array( 'jquery' ), $this->version, false );

	}

  	/**
	 * Adds the invalid license admin notice.
	 *
	 * @since 2.2.4
	 */
	public function action_admin_notices() {

		$suppress_on_multisite = ! is_main_site();

		if ( is_multisite() && $suppress_on_multisite ) {
			return;
		}

		$is_saving_license_key = isset( $_POST['_gaddon_setting_license_key'] ) && isset( $_POST['_gravity-forms-power-automate_save_settings_nonce'] );

		$license_details = false;

		if ( $is_saving_license_key ) {
			$posted_license_key = sanitize_text_field( rgpost( '_gaddon_setting_license_key' ) );
			if ( wp_verify_nonce( $_POST['_gravity-forms-power-automate_save_settings_nonce'], 'gravity-forms-power-automate_save_settings' ) ) {
				$license_details = $posted_license_key ? $this->activate_license( $posted_license_key ) : false;
			}
			if ( $license_details ) {
				$expiration = DAY_IN_SECONDS + rand( 0, DAY_IN_SECONDS );
				set_transient( 'gravity-forms-power-automate_license_details', $license_details, $expiration );
			}
		} else {
			$license_details = get_transient( 'gravity-forms-power-automate_license_details' );
			if ( ! $license_details ) {
				$last_check = get_option( 'gravity-forms-power-automate_last_license_check' );
				if ( $last_check > time() - 5 * MINUTE_IN_SECONDS ) {
					return;
				}

				$license_details = $this->check_license();
				if ( $license_details ) {
					$expiration = DAY_IN_SECONDS + rand( 0, DAY_IN_SECONDS );
					set_transient( 'gravity-forms-power-automate_license_details', $license_details, $expiration );
					update_option( 'gravity-forms-power-automate_last_license_check', time() );
				}
			}
		}

		$license_status = $license_details ? $license_details->status : '';

		if ( $license_status != 'valid' ) {

			$add_buttons = ! is_multisite();

			$primary_button_link = admin_url( 'admin.php?page=gf_settings&subview=gravity-forms-power-automate' );

			$message = sprintf( '<img src="%s" style="vertical-align:text-bottom;margin-right:5px;"/>', GFCommon::get_base_url() . '/images/exclamation.png' );

			switch ( $license_status ) {
				case 'expired':
					/* translators: %s is the title of the plugin */
					$message     .= sprintf( esc_html__( 'Your %s license has expired.', 'gravity-forms-power-automate' ), $this->_title );
					$add_buttons = false;
					break;
				case 'invalid':
					/* translators: %s is the title of the plugin */
					$message .= sprintf( esc_html__( 'Your %s license is invalid.', 'gravity-forms-power-automate' ), $this->_title );
					break;
				case 'deactivated':
					/* translators: %s is the title of the plugin */
					$message .= sprintf( esc_html__( 'Your %s license is inactive.', 'gravity-forms-power-automate' ), $this->_title );
					break;
				/** @noinspection PhpMissingBreakStatementInspection */
				case '':
					$license_status = 'site_inactive';
				// break intentionally left blank
				case 'inactive':
				default:
					/* translators: %s is the title of the plugin */
					$message .= sprintf( esc_html__( 'Your %s license has not been activated.', 'gravity-forms-power-automate' ), $this->_title );
					break;
			}

			$message .= ' ' . esc_html__( "This means your forms are not connected to Power Automate.", 'gravity-forms-power-automate' );

			$url = 'https://reenhanced.com/products/gravity-flow-power-automate/?utm_source=admin_notice&utm_medium=admin&utm_content=' . $license_status . '&utm_campaign=Admin%20Notice#pricing';

			// Show a different notice on settings page for inactive licenses (hide the buttons)
			if ( $add_buttons && ! $this->is_app_settings() ) {
				$message .= '<br /><br />' . esc_html__( '%sActivate your license%s or %sget a license here%s', 'gravityflow' );
				$message = sprintf( $message, '<a href="' . esc_url( $primary_button_link ) . '" class="button button-primary">', '</a>', '<a href="' . esc_url( $url ) . '" class="button button-secondary">', '</a>' );
			}

			$key = 'gravity-forms-power-automate_license_notice_' . date( 'Y' ) . date( 'z' );

			$notice = array(
				'key'          => $key,
				'capabilities' => 'gravity-forms-power-automate_settings',
				'type'         => 'error',
				'text'         => $message,
			);

			$notices = array( $notice );

			GFCommon::display_dismissible_message( $notices );
		}
	}

	/**
	 * Configures the settings which should be rendered on the Forms > Settings > Power Automate Integration tab.
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {
			return array(
					array(
							'title'  => esc_html__( 'Power Automate Integration Settings', 'gravity-forms-power-automate' ),
							'description' => 'This plugin requires a license to use. Please visit the <a href="https://reenhanced.com/products/gravity-forms-power-automate">Gravity Forms Power Automate product page</a> to obtain a license if you need one.',
							'fields' => array(
									array(
											'label'   => 'Enable Power Automate Integration',
											'type'    => 'checkbox',
											'name'    => 'enabled',
											'tooltip' => 'Check this box to enable integration with Power Automate',
											'choices' => array(
													array(
															'label' => 'Enabled',
															'name'  => 'enabled',
															'default_value' => 1,
													),
											),
									),
									array(
											'label'             => esc_html__( 'License Key', 'gravity-forms-power-automate' ),
											'type'              => 'text',
											'name'              => 'license_key',
											'required'          => true,
											'tooltip'           => esc_html__( 'This is your license key from reenhanced.com', 'gravity-forms-power-automate' ),
											'class'             => 'large',
											'validation_callback' => array($this, 'license_validation'),
											'feedback_callback'   => array($this, 'license_feedback'),
											'error_message'       => __('Invalid License', 'gravity-forms-power-automate'),
											'default_value'       => ''
									),
							),
					),
			);
	}
}