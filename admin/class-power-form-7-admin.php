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

	public function plugin_action_links($links, $plugin_file) {
		if (PF7_PLUGIN_BASE == $plugin_file) {
			$settings_link = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=wpcf7-pf7')) . '">Settings</a>';
			array_unshift($links, $settings_link);
		}
		return $links;
	}

	public function admin_init() {
		// Register settings page
		register_setting($this->get_settings_page_slug(), $this->plugin()->get_option_name(), array(
			'type' => 'object',
			'sanitize_callback' => array($this, 'settings_section_validation')
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
		<input name="<?php echo $this->option_name('enabled') ?>" type="checkbox" value="TRUE" <?php echo ($enabled == true) ? "checked" : ""; ?> />
		<?php
	}

	public function license_key_input() {
		$key = $this->plugin()->get_app_setting('license_key');
		$valid = $this->plugin()->license_status_check();
		?>
		<input name="<?php echo $this->option_name('license_key') ?>" type="password" value="<?php echo $key ?>" autocomplete="new-password" />
		<?php
		  if (!empty($key)) {
				if ($valid) {
					echo "🆗 Your license has been validated!";
				} else {
					echo "⛔";
				}
			}
	}

	public function users_dropdown() {
		//$users_list = get_users( [ 'role__in' => [ 'administrator' ] ] );
		$users_list = get_users( );
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

	private function settings_message($error_message, $error_class = 'error') {
		// Error classes: 'warning', 'error', 'updated', 'success'
		add_settings_error( $this->option_group.'_messages', $this->option_group .'_message', __( $error_message, 'power-form-7' ), $error_class );
	}

	public function settings_section_validation($dirty_settings) {
		// This runs only when the settings are changed.
		// TODO: Show error message at all times if settings are not in a valid state.

		$app_settings = $this->plugin()->get_app_settings();

		$error_count = 0;
		$clean_settings = array();
		$clean_settings['enabled'] = ($dirty_settings['enabled'] == 'TRUE');

		if (is_numeric($dirty_settings['flow_user'])) {
			$clean_settings['flow_user'] = $dirty_settings['flow_user'];
		} else {
			$error_count++;
			$this->settings_message('You must assign a Power Automate user!');
		}
		
		if ($this->license_validation($dirty_settings['license_key'])) {
			$clean_settings['license_key'] = $dirty_settings['license_key'];
		} else {
			// Errors will be added from inside validator
			$error_count++;
		}

		if ($error_count == 0) {
			$this->settings_message('Settings Saved!', 'success');
		}

		return $clean_settings;
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
				// show error/update messages
				settings_errors( $this->option_group.'_messages' );
			?>

			<form action="options.php" method="post">
				<input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']) ?>" />
				<?php 
				settings_fields($this->option_group);

				do_settings_sections($this->option_group);
				?>

				<table class="form-table" role="presentation">
					<tr>
						<th>Site Url</th>
						<td>
							<pre><?php echo get_site_url(); ?></pre>
							<p><em>In order to access this site's forms, select this in your flow.</em></p>
						</td>
					</tr>
				</table>

				<?php
				submit_button("Save Settings");
				?>
			</form>
		</div>
		<?php
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

	public function license_validation($key) {
		$existing_key = $this->plugin()->get_app_setting('license_key');

		$suppress_on_multisite = ! is_main_site();
		if ( is_multisite() && $suppress_on_multisite ) {
			return true;
		}

		$is_activating_license = empty( $existing_key ) && isset( $key );
		$is_changing_license   = isset( $existing_key ) && $existing_key != $key;

		$license_details = false;

		if ( $is_activating_license ) {
			$license_details = $this->plugin()->activate_license( $key );
			if ( $license_details ) {
				$expiration = DAY_IN_SECONDS + rand( 0, DAY_IN_SECONDS );
				set_transient( 'pf7_license_details', $license_details, $expiration );
			}
		} else if ($is_changing_license) {
			// Deactivate the old site.
			$response = $this->plugin()->perform_license_request( 'deactivate_license', $existing_key );
			$this->plugin()->log_debug( __METHOD__ . '() - response: ' . print_r( $response, 1 ) );

			set_transient( 'pf7_license_details', false );

			if ( empty( $key ) ) {
				$this->settings_message('No license is set');
				return false;
			}

			$license_details = $this->plugin()->activate_license( $key );
		} else { // Saving settings for existing license
			$license_details = get_transient( 'pf7_license_details' );
			if ( ! $license_details ) {
				$last_check = get_option( 'pf7_last_license_check', 0 );
				if ( $last_check > time() - 5 * MINUTE_IN_SECONDS ) {
					return true;
				}

				$license_details = $this->plugin()->check_license();
				if ( $license_details ) {
					$expiration = DAY_IN_SECONDS + rand( 0, DAY_IN_SECONDS );
					set_transient( 'pf7_license_details', $license_details, $expiration );
					update_option( 'pf7_last_license_check', time() );
				}
			}
		}

		$license_status = $license_details ? $license_details->status : '';

		if ( $license_status != 'valid' ) {
			$message = '⚠ ';

			switch ( $license_status ) {
				case 'expired':
					/* translators: %s is the title of the plugin */
					$message     .= sprintf( esc_html__( 'Your %s license has expired.', 'power-form-7' ), $this->plugin()->_short_title );
					$add_buttons = false;
					break;
				case 'invalid':
					/* translators: %s is the title of the plugin */
					$message .= sprintf( esc_html__( 'Your %s license is invalid.', 'power-form-7' ), $this->plugin()->_short_title );
					break;
				case 'deactivated':
					/* translators: %s is the title of the plugin */
					$message .= sprintf( esc_html__( 'Your %s license is inactive.', 'power-form-7' ), $this->plugin()->_short_title );
					break;
				/** @noinspection PhpMissingBreakStatementInspection */
				case '':
					$license_status = 'site_inactive';
				// break intentionally left blank
				case 'inactive':
				default:
					/* translators: %s is the title of the plugin */
					$message .= sprintf( esc_html__( 'Your %s license has not been activated.', 'power-form-7' ), $this->plugin()->_short_title );
					break;
			}

			$message .= ' ' . esc_html__( "This means your forms are not connected to Power Automate.", 'power-form-7' );

			$this->settings_message($message);
			return false;
		}

		return true;
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