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
	 * The option group for our settings
	 * 
	 */
	public $option_group = 'wpcf7-pf7';

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

	public function get_settings_page_slug() {
		return $this->option_group;
	}


	/**
	 * Register the settings page menu item
	 * 
	 * @since 1.0.0
	 */
	public function admin_menu() {
		$settings = add_submenu_page('wpcf7',
							__( 'Power Form 7 Settings', 'power-form-7'),
							__( 'Power Automate Settings', 'power-form-7'),
							'wpcf7_manage_integration',
							$this->get_settings_page_slug(),
							array($this, 'settings_page')
						);

		add_action('load-'. $settings, array($this, 'load_settings_page'));
	}

	public function admin_init() {
		// Register settings page
		register_setting($this->get_settings_page_slug(), $this->plugin()->get_option_name(), array(
			'type' => 'object'
		));

		add_settings_section(
			$this->plugin()->get_option_name(),
			'Power Automate',
			array($this, 'settings_section_callback'),
			$this->get_settings_page_slug()
		);

		add_settings_field(
			'pf7_enabled',
			'Enabled?',
			array($this, 'enabled_checkbox'),
			$this->get_settings_page_slug(),
			$this->plugin()->get_option_name()
		);

		add_settings_field(
			'pf7_license_key',
			'License Key',
			array($this, 'license_key_input'),
			$this->get_settings_page_slug(),
			$this->plugin()->get_option_name()
		);

		add_settings_field(
			'pf7_flow_user',
			'Power Automate User',
			array($this, 'users_dropdown'),
			$this->get_settings_page_slug(),
			$this->plugin()->get_option_name()
		);
	}

	public function settings_section_callback($args) {
		?>
			<p>Configure your Contact Form 7 Power Automate Integration here.</p>
			<p>
			This plugin requires a license to use.
			Please visit the <a href="https://reenhanced.com/products/power-form-7">Power Form 7 product page</a> to obtain a license if you need one.
			</p>
		<?php
	}

	public function enabled_checkbox() {
		$enabled = $this->plugin()->get_app_setting('enabled', true);
		?>
		<input name="<?php echo $this->option_name('enabled') ?>" type="checkbox" value="TRUE" <?php echo ($enabled) ? "checked" : ""; ?> />
		<?php
	}

	public function license_key_input() {
		$key = $this->plugin()->get_app_setting('license_key');
		$valid = false;
		?>
		<input name="<?php echo $this->option_name('license_key') ?>" type="password" value="<?php echo $key ?>" autocomplete="new-password" />
		<?php
		  if (!empty($key)) {
				if ($valid) {
					echo "🆗";
				} else {
					echo "⛔";
				}
			}
	}

	public function users_dropdown() {
		$users_list = get_users( [ 'role__in' => [ 'administrator' ] ] );
		$pf7_user   = $this->plugin()->get_app_setting('flow_user');
		?>
		<select name="<?php echo $this->option_name('flow_user') ?>">
			<option <?php echo (empty($pf7_user)) ? "selected" : "" ?> value="">Select User</option>
			<?php foreach ($users_list as $user) {
				$selected = "";
				if ($user->ID == $pf7_user) {
					$selected = "selected";
				}
				echo "<option ". $selected . " value='" . $user->ID . "'>" . esc_html($user->display_name) . "</option>";
			} ?>
		</select>
		<p><em>Please select the administrator account that will be used by Power Automate</em></p><?php
	}

	public function load_settings_page() {
		// This is where we handle submissions and data set on our settings page.
		// Validation, License activation, etc
	}

	public function settings_page() {
		// Get list of users here
		// Get settings that we've saved
		// Fields: Enable, License Key, Power Automate User Identity

		// Custom settings page: https://developer.wordpress.org/plugins/settings/custom-settings-page/
		?>
		<div class="wrap" id="wpcf7-pf7">
			<h1><span><i class="fa fa-cogs"></i> <?php echo esc_html__(get_admin_page_title()) ?></span></h1>

			<?php
				// check if the user have submitted the settings
				// WordPress will add the "settings-updated" $_GET parameter to the url
				if ( isset( $_GET['settings-updated'] ) ) {
						// add settings saved message with the class of "updated"
						add_settings_error( $this->option_group.'_messages', $this->option_group .'_message', __( 'Settings Saved', 'power-form-7' ), 'updated' );
				}
		
				// show error/update messages
				settings_errors( $this->option_group.'_messages' );

				var_export($this->plugin()->get_app_settings());
			?>

			<form action="options.php" method="post">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<?php 
				settings_fields($this->option_group);

				do_settings_sections($this->option_group);

				submit_button("Save Settings!");
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Configures the settings which should be rendered
	 *
	 * @return array
	 */
	public function app_settings_fields() {
			return array(
					array(
							'title'  => esc_html__( 'Power Automate Integration Settings', 'power-form-7' ),
							'description' => 'Configure your Contact Form 7 Power Automate Integration here. This plugin requires a license to use. Please visit the <a href="https://reenhanced.com/products/power-form-7">Power Form 7 product page</a> to obtain a license if you need one.',
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
											'label'             => esc_html__( 'License Key', 'power-form-7' ),
											'type'              => 'text',
											'name'              => 'license_key',
											'required'          => true,
											'tooltip'           => esc_html__( 'This is your license key from reenhanced.com', 'power-form-7' ),
											'class'             => 'large',
											'validation_callback' => array($this, 'license_validation'),
											'feedback_callback'   => array($this, 'license_feedback'),
											'error_message'       => __('Invalid License', 'power-form-7'),
											'default_value'       => ''
									),
									array(
											'label'   => 'Power Automate User',
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

									)
							),
					),
			);
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

			$primary_button_link = admin_url( 'admin.php?page=' . $this->get_settings_page_slug() );

			$message = sprintf( '<img src="%s" style="vertical-align:text-bottom;margin-right:5px;"/>', GFCommon::get_base_url() . '/images/exclamation.png' );

			switch ( $license_status ) {
				case 'expired':
					/* translators: %s is the title of the plugin */
					$message     .= sprintf( esc_html__( 'Your %s license has expired.', 'power-form-7' ), $this->_title );
					$add_buttons = false;
					break;
				case 'invalid':
					/* translators: %s is the title of the plugin */
					$message .= sprintf( esc_html__( 'Your %s license is invalid.', 'power-form-7' ), $this->_title );
					break;
				case 'deactivated':
					/* translators: %s is the title of the plugin */
					$message .= sprintf( esc_html__( 'Your %s license is inactive.', 'power-form-7' ), $this->_title );
					break;
				/** @noinspection PhpMissingBreakStatementInspection */
				case '':
					$license_status = 'site_inactive';
				// break intentionally left blank
				case 'inactive':
				default:
					/* translators: %s is the title of the plugin */
					$message .= sprintf( esc_html__( 'Your %s license has not been activated.', 'power-form-7' ), $this->_title );
					break;
			}

			$message .= ' ' . esc_html__( "This means your forms are not connected to Power Automate.", 'power-form-7' );

			$url = 'https://reenhanced.com/products/power-form-7/?utm_source=admin_notice&utm_medium=admin&utm_content=' . $license_status . '&utm_campaign=Admin%20Notice#pricing';

			// Show a different notice on settings page for inactive licenses (hide the buttons)
			if ( $add_buttons && ! $this->is_app_settings() ) {
				$message .= '<br /><br />' . esc_html__( '%sActivate your license%s or %sget a license here%s', 'gravityflow' );
				$message = sprintf( $message, '<a href="' . esc_url( $primary_button_link ) . '" class="button button-primary">', '</a>', '<a href="' . esc_url( $url ) . '" class="button button-secondary">', '</a>' );
			}

			$key = 'power-form-7_license_notice_' . date( 'Y' ) . date( 'z' );

			$notice = array(
				'key'          => $key,
				'capabilities' => 'power-form-7_settings',
				'type'         => 'error',
				'text'         => $message,
			);

			$notices = array( $notice );

			GFCommon::display_dismissible_message( $notices );
		}
	}


	private function option_name($name) {
		return $this->plugin()->get_option_name().'['.$name.']';
	}

	private $_plugin;
	private function plugin() {
		if ($this->_plugin == null) {
			$this->_plugin = Power_Form_7::get_instance();
		}

		return $this->_plugin;
	}
}