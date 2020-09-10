<?php
/**
 * Referrer Analytics class.
 *
 * @package ReferrerAnalytics
 */

// Security Note: Blocks direct access to the plugin PHP files.
defined( 'ABSPATH' ) || die();

/**
 * Referrer Analytics class.
 */
class Referrer_Analytics {
	/**
	 * Plugin options.
	 *
	 * @var array Plugin options.
	 */
	public $options = array();

	/**
	 * Class constructor.
	 */
	public function __construct() {
		register_activation_hook( REFERRER_ANALYTICS, array( $this, 'install' ) );

		$this->options = array(
			'utm_source'              => array(
				'default' => 'host',
				'label'   => __( 'UTM Source', 'referrer-analytics' ),
				'section' => 'referrer_analytics_ga_settings',
				'type'    => 'select',
				'desc'    => __( 'Select the value that should be used for the <code>utm_source</code>.', 'referrer-analytics' ),
				'options' => array(
					'ignore' => __( 'Ignore', 'referrer-analytics' ),
					'host'   => __( 'Host', 'referrer-analytics' ),
					'type'   => __( 'Type', 'referrer-analytics' ),
					'name'   => __( 'Name', 'referrer-analytics' ),
				),
			),
			'utm_medium'              => array(
				'default' => 'type',
				'label'   => __( 'UTM Medium', 'referrer-analytics' ),
				'section' => 'referrer_analytics_ga_settings',
				'type'    => 'select',
				'desc'    => __( 'Select the value that should be used for the <code>utm_medium</code>.', 'referrer-analytics' ),
				'options' => array(
					'ignore' => __( 'Ignore', 'referrer-analytics' ),
					'host'   => __( 'Host', 'referrer-analytics' ),
					'type'   => __( 'Type', 'referrer-analytics' ),
					'name'   => __( 'Name', 'referrer-analytics' ),
				),
			),
			'utm_campaign'            => array(
				'default' => 'name',
				'label'   => __( 'UTM Campaign', 'referrer-analytics' ),
				'section' => 'referrer_analytics_ga_settings',
				'type'    => 'select',
				'desc'    => __( 'Select the value that should be used for the <code>utm_campaign</code>.', 'referrer-analytics' ),
				'options' => array(
					'ignore' => __( 'Ignore', 'referrer-analytics' ),
					'host'   => __( 'Host', 'referrer-analytics' ),
					'type'   => __( 'Type', 'referrer-analytics' ),
					'name'   => __( 'Name', 'referrer-analytics' ),
				),
			),
			'cookie_expiration'       => array(
				'default'     => 30,
				'label'       => __( 'Cookie Expiration', 'referrer-analytics' ),
				'section'     => 'referrer_analytics_general_settings',
				'type'        => 'number',
				'field_class' => 'small-text',
				'desc'        => __( 'Number of days the cookie will be stored on the user\'s computer.', 'referrer-analytics' ),
				'placeholder' => 30,
				'suffix'      => __( 'days', 'referrer-analytics' ),
			),
			'referrer_fallback_param' => array(
				'default'     => 'utm_source',
				'label'       => __( 'Referrer Fallback Parameter', 'referrer-analytics' ),
				'section'     => 'referrer_analytics_referrer_host_settings',
				'type'        => 'text',
				'field_class' => 'regular-text',
				'desc'        => __( 'The URL parameter that should be used as the referrer fallback if unable to retrieve with <code>$_SERVER[\'HTTP_REFERER\']</code>.', 'referrer-analytics' ),
				'placeholder' => __( 'e.g. utm_source', 'referrer-analytics' ),
			),
			'hosts'                   => array(
				'default'   => array(),
				'label'     => __( 'Defined Referrer Hosts', 'referrer-analytics' ),
				'section'   => 'referrer_analytics_referrer_host_settings',
				'custom_cb' => 'hosts_field',
			),
			'store_cookies'           => array(
				'default' => 'disabled',
				'label'   => __( 'Store Cookies', 'referrer-analytics' ),
				'section' => 'referrer_analytics_general_settings',
				'type'    => 'checkbox',
				'multi'   => false,
				'desc'    => __( 'Stores referrer host data in cookies that can be used for 3rd-party applications.', 'referrer-analytics' ),
				'options' => array(
					'enabled' => __( 'Enabled', 'referrer-analytics' ),
				),
			),
			'logging'                 => array(
				'default' => 'disabled',
				'label'   => __( 'Logging & Statistics', 'referrer-analytics' ),
				'section' => 'referrer_analytics_general_settings',
				'type'    => 'checkbox',
				'multi'   => false,
				'desc'    => __( 'Enables logging of user referrers and provides an admin interface to view statistics. <strong>Not recommended on sites with a large amount of traffic</strong>.', 'referrer-analytics' ),
				'options' => array(
					'enabled' => __( 'Enabled', 'referrer-analytics' ),
				),
			),
			'redirect_with_utm'       => array(
				'default' => 'enabled',
				'label'   => __( 'Redirect with UTM Data', 'referrer-analytics' ),
				'section' => 'referrer_analytics_ga_settings',
				'type'    => 'checkbox',
				'multi'   => false,
				'desc'    => __( 'When users come from another site, redirect & append the page their visting with the referring <a href="https://support.google.com/analytics/answer/1033863?hl=en" target="_blank" rel="noopener noreferrer">UTM data</a>.', 'referrer-analytics' ),
				'options' => array(
					'enabled' => __( 'Enabled', 'referrer-analytics' ),
				),
			),
			'track_all_referrers'     => array(
				'default' => 'enabled',
				'label'   => __( 'Track all Referrers', 'referrer-analytics' ),
				'section' => 'referrer_analytics_referrer_host_settings',
				'type'    => 'checkbox',
				'multi'   => false,
				'desc'    => __( 'If a user comes from a host that\'s not defined above, track it using the raw data.<br />Referrer Analytics will attempt to set \'Host\', \'Name\' & \'Type\' from it\'s list of known hosts.<br />If unable to locate, \'Host\' and \'Name\' will be the hostname of the referrer and \'Type\' will default to "referral".', 'referrer-analytics' ),
				'options' => array(
					'enabled' => __( 'Enabled', 'referrer-analytics' ),
				),
			),
			'url_referrer_fallback'   => array(
				'default' => 'enabled',
				'label'   => __( 'Use URL Referrer Fallback', 'referrer-analytics' ),
				'section' => 'referrer_analytics_referrer_host_settings',
				'type'    => 'checkbox',
				'multi'   => false,
				'desc'    => __( 'If <code>$_SERVER[\'HTTP_REFERER\']</code> is unavailable (see the <a href="https://wordpress.org/plugins/referrer-analytics/" target="_blank" rel="noopener noreferrer">plugin FAQ</a> for more information), attempt to get the referrer from the <code>utm_source</code> URL parameter.', 'referrer-analytics' ),
				'options' => array(
					'enabled' => __( 'Enabled', 'referrer-analytics' ),
				),
			),
			'disable_noreferrer'      => array(
				'default' => 'disabled',
				'label'   => __( 'Disable <code>rel="noreferrer"</code>', 'referrer-analytics' ),
				'section' => 'referrer_analytics_general_settings',
				'type'    => 'checkbox',
				'multi'   => false,
				'desc'    => __( 'Allows external link destinations to retrieve the <code>$_SERVER[\'HTTP_REFERER\']</code> by disabling WordPress from automatcially adding the <code>rel="noreferrer"</code> tag when enabled.', 'referrer-analytics' ),
				'options' => array(
					'enabled' => __( 'Enabled', 'referrer-analytics' ),
				),
			),
		);

		$this->load_options();

		add_action( 'plugins_loaded', array( $this, 'db_version_check' ) );
		add_action( 'init', array( $this, 'set_cookies' ) );
		add_action( 'template_redirect', array( $this, 'process_referrer' ) );
		add_action( 'admin_menu', array( $this, 'register_admin_pages' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );

		add_filter( 'wp_targeted_link_rel', array( $this, 'filter_link_rel' ) );
	}

	/**
	 * Register/enqueue scripts.
	 */
	public function scripts() {
		$script_suffix = '.js';
		if ( ! WP_DEBUG ) {
			$script_suffix = '.min.js';
		}

		if ( 'enabled' === $this->options['store_cookies']['value'] ) {
			wp_register_script(
				'js-cookie',
				'https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js',
				array(),
				'2.2.1',
				true
			);
			wp_enqueue_script(
				'referrer-analytics',
				plugin_dir_url( REFERRER_ANALYTICS ) . 'assets/js/referrer-analytics' . $script_suffix,
				array( 'js-cookie' ),
				REFERRER_ANALYTICS_VERSION,
				true
			);
		}
	}

	/**
	 * Register/enqueue admin scripts.
	 *
	 * @param string $hook Page hook.
	 */
	public function admin_scripts( $hook ) {
		switch ( $hook ) {
			case 'toplevel_page_referrer-analytics':
			case 'settings_page_referrer-analytics-settings':
			case 'referrer-analytics_page_referrer-analytics-log':
				wp_enqueue_style( 'referrer-analytics-admin', plugin_dir_url( REFERRER_ANALYTICS ) . '/assets/css/admin.css', false, REFERRER_ANALYTICS_VERSION );
				wp_enqueue_style( 'referrer-analytics-charts', plugin_dir_url( REFERRER_ANALYTICS ) . '/assets/css/Chart.min.css', false, '2.9.3' );
				wp_enqueue_script( 'referrer-analytics-charts', plugin_dir_url( REFERRER_ANALYTICS ) . '/assets/js/Chart.bundle.min.js', array(), '2.9.3', false );
				break;
		}
	}

	/**
	 * Validates saved settings.
	 *
	 * @param array $input Submitted input values.
	 */
	public function validate_settings( $input ) {
		$options = array();
		foreach ( $this->options as $key => $option ) {
			if ( ! empty( $input[ $key ] ) ) {
				$options[ $key ] = $input[ $key ];
			} else {
				$options[ $key ] = $option['default'];
			}
		}

		return $options;
	}

	/**
	 * Registers the plugin settings.
	 */
	public function register_settings() {
		register_setting( 'referrer_analytics', 'referrer_analytics_options', array( $this, 'validate_settings' ) );

		add_settings_section( 'referrer_analytics_general_settings', __( 'General Settings', 'referrer-analytics' ), array( $this, 'general_settings' ), 'referrer_analytics' );
		add_settings_section( 'referrer_analytics_referrer_host_settings', __( 'Referrer Host Settings', 'referrer-analytics' ), array( $this, 'host_settings' ), 'referrer_analytics' );
		add_settings_section( 'referrer_analytics_ga_settings', __( 'Google Analytics Settings', 'referrer-analytics' ), array( $this, 'ga_settings' ), 'referrer_analytics' );

		foreach ( $this->options as $key => $option ) {
			add_settings_field(
				$key,
				$option['label'],
				array( $this, ! empty( $option['custom_cb'] ) ? $option['custom_cb'] : 'settings_field' ),
				'referrer_analytics',
				$option['section'],
				array(
					'label_for' => $key,
					'type'      => ! empty( $option['type'] ) ? $option['type'] : false,
					'multi'     => ! empty( $option['multi'] ) ? true : false,
					'desc'      => ! empty( $option['desc'] ) ? $option['desc'] : false,
					'options'   => ! empty( $option['options'] ) ? $option['options'] : false,
				)
			);
		}
	}

	/**
	 * Output for a settings field.
	 *
	 * @param array $args Field arguments.
	 */
	public function settings_field( $args ) {
		$value = $this->options[ $args['label_for'] ]['value'];

		switch ( $args['type'] ) {
			case 'url':
			case 'text':
			case 'password':
			case 'number':
			case 'email':
				?>
				<input
					<?php if ( ! empty( $args['field_class'] ) ) : ?>
						class="<?php echo esc_attr( $args['field_class'] ); ?>"
					<?php endif; ?>
					type="<?php echo esc_attr( $args['type'] ); ?>"
					value="<?php echo esc_attr( $value ); ?>"
					<?php if ( ! empty( $args['placeholder'] ) ) : ?>
						placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>"
					<?php endif; ?>
					id="<?php echo esc_attr( $args['label_for'] ); ?>"
					name="referrer_analytics_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
				>
				<?php
				if ( ! empty( $args['suffix'] ) ) :
					echo ' ' . esc_html( $args['suffix'] );
				endif;
				break;
			case 'select':
				?>
				<select name="referrer_analytics_options[<?php echo esc_attr( $args['label_for'] ); ?>]" id="<?php echo esc_attr( $args['label_for'] ); ?>">
					<?php foreach ( $args['options'] as $key => $label ) : ?>
						<option
							value="<?php echo esc_attr( $key ); ?>"
							<?php if ( $key === $value ) : ?>
								selected="selected"
							<?php endif; ?>
						>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<?php
				break;
			case 'checkbox':
				$field_key = 'referrer_analytics_options[' . esc_attr( $args['label_for'] ) . ']';
				if ( $args['multi'] ) {
					$field_key .= '[' . $key . ']';
				}
				?>
				<?php foreach ( $args['options'] as $key => $label ) : ?>
					<label for="<?php echo esc_attr( $args['label_for'] . $key ); ?>">
						<input
							type="checkbox"
							id="<?php echo esc_attr( $args['label_for'] . $key ); ?>"
							name="<?php echo esc_attr( $field_key ); ?>"
							value="<?php echo esc_attr( $key ); ?>"
							<?php if ( $key === $value ) : ?>
								checked="checked"
							<?php endif; ?>
						/> <?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
				<?php
				break;
		}

		if ( ! empty( $args['desc'] ) ) :
			?>
			<p class="description">
				<?php
				echo wp_kses(
					$args['desc'],
					array(
						'a'      => array(
							'target' => array(),
							'rel'    => array(),
						),
						'code'   => array(),
						'br'     => array(),
						'strong' => array(),
					)
				);
				?>
			</p>
			<?php
		endif;
	}

	/**
	 * Hosts field output.
	 */
	public function hosts_field() {
		?>
		<div class="referrer-analytics-referrer-header">
			<div>
				<label><?php esc_html_e( 'Host', 'referrer-analytics' ); ?></label>
				<small><?php esc_html_e( 'The host name of the referrer (i.e. www.google.com).', 'referrer-analytics' ); ?></small>
			</div>
			<div>
				<label><?php esc_html_e( 'Type', 'referrer-analytics' ); ?></label>
				<small><?php esc_html_e( 'Define a type for the referrer (i.e. organic, referral, etc.).', 'referrer-analytics' ); ?></small>
			</div>
			<div>
				<label><?php esc_html_e( 'Name', 'referrer-analytics' ); ?></label>
				<small><?php esc_html_e( 'Readable name for the referrer (i.e. Google, Bing, etc.).', 'referrer-analytics' ); ?></small>
			</div>
			<div>
				<label><?php esc_html_e( 'Primary URL', 'referrer-analytics' ); ?></label>
				<small><?php esc_html_e( 'Primary URL for the referrer', 'referrer-analytics' ); ?></small>
			</div>
		</div>
		<?php
		$cnt = 0;
		if ( $this->options['hosts']['value'] ) :
			foreach ( $this->options['hosts']['value'] as $key => $host ) :
				if ( empty( $host['host'] ) ) {
					continue;
				}
				?>
				<div class="referrer-analytics-referrer-option">
					<input
						type="text"
						name="referrer_analytics_options[hosts][<?php echo esc_attr( $cnt ); ?>][host]"
						value="<?php echo esc_attr( trim( $host['host'] ) ); ?>"
						placeholder="<?php esc_html_e( 'Host (i.e. www.google.com)', 'referrer_analytics' ); ?>"
						class="referrer-analytics-input"
					/>

					<input
						type="text"
						name="referrer_analytics_options[hosts][<?php echo esc_attr( $cnt ); ?>][type]"
						value="<?php echo esc_attr( trim( $host['type'] ) ); ?>"
						placeholder="<?php esc_html_e( 'Type (i.e. organic)', 'referrer_analytics' ); ?>"
						class="referrer-analytics-input"
					/>

					<input
						type="text"
						name="referrer_analytics_options[hosts][<?php echo esc_attr( $cnt ); ?>][name]"
						value="<?php echo esc_attr( trim( $host['name'] ) ); ?>"
						placeholder="<?php esc_html_e( 'Name (i.e. Google)', 'referrer_analytics' ); ?>"
						class="referrer-analytics-input"
					/>

					<input
						type="url"
						name="referrer_analytics_options[hosts][<?php echo esc_attr( $cnt ); ?>][primary_url]"
						value="<?php echo esc_attr( trim( $host['primary_url'] ) ); ?>"
						placeholder="<?php esc_html_e( 'Name (i.e. https://www.google.com)', 'referrer_analytics' ); ?>"
						class="referrer-analytics-input"
					/>
				</div>
				<?php
				$cnt++;
			endforeach;
		endif;
		?>
		<div class="referrer-analytics-referrer-option">
			<input
				type="text"
				name="referrer_analytics_options[hosts][<?php echo esc_attr( $cnt ); ?>][host]"
				value=""
				placeholder="<?php esc_html_e( 'Host (i.e. www.google.com)', 'referrer_analytics' ); ?>"
				class="referrer-analytics-input"
			/>

			<input
				type="text"
				name="referrer_analytics_options[hosts][<?php echo esc_attr( $cnt ); ?>][type]"
				value=""
				placeholder="<?php esc_html_e( 'Type (i.e. organic)', 'referrer_analytics' ); ?>"
				class="referrer-analytics-input"
			/>

			<input
				type="text"
				name="referrer_analytics_options[hosts][<?php echo esc_attr( $cnt ); ?>][name]"
				value=""
				placeholder="<?php esc_html_e( 'Name (i.e. Google)', 'referrer_analytics' ); ?>"
				class="referrer-analytics-input"
			/>

			<input
				type="url"
				name="referrer_analytics_options[hosts][<?php echo esc_attr( $cnt ); ?>][primary_url]"
				value=""
				placeholder="<?php esc_html_e( 'Name (i.e. https://www.google.com)', 'referrer_analytics' ); ?>"
				class="referrer-analytics-input"
			/>
		</div>
		<?php
	}

	/**
	 * Output for the general settings section.
	 */
	public function general_settings() {
		esc_html_e( 'Configure how Referrer Analytics operates on your site below.', 'referrer-analytics' );
	}

	/**
	 * Output for the hosts settings section.
	 */
	public function host_settings() {
		esc_html_e( 'Manage defined referrer hosts and settings below.', 'referrer-analytics' );
	}

	/**
	 * Output for the GA settings section.
	 */
	public function ga_settings() {
		esc_html_e( 'Manage how Referrer Analtyics uses Google Analytics UTM parameters below.', 'referrer-analytics' );
	}

	/**
	 * Registers admin dashboard pages.
	 */
	public function register_admin_pages() {
		add_submenu_page(
			'options-general.php',
			__( 'Referrer Analytics Settings', 'referrer-analytics' ),
			__( 'Referrer Analytics', 'referrer-analytics' ),
			'manage_options',
			'referrer-analytics-settings',
			array( $this, 'admin_settings' )
		);

		if ( 'enabled' === $this->options['logging']['value'] ) {
			add_menu_page(
				__( 'Referrer Analytics Log', 'referrer-analytics' ),
				__( 'Referrer Analytics', 'referrer-analytics' ),
				'manage_options',
				'referrer-analytics',
				array( $this, 'dashboard' ),
				'dashicons-chart-area'
			);

			add_submenu_page(
				'referrer-analytics',
				__( 'Referrer Analytics Dashboard', 'referrer-analytics' ),
				__( 'Dashboard', 'referrer-analytics' ),
				'manage_options',
				'referrer-analytics',
				array( $this, 'dashboard' )
			);

			add_submenu_page(
				'referrer-analytics',
				__( 'Referrer Analytics Log', 'referrer-analytics' ),
				__( 'Referrer Log', 'referrer-analytics' ),
				'manage_options',
				'referrer-analytics-log',
				array( $this, 'log_dashboard' )
			);
		}
	}

	/**
	 * Output for dashboard page.
	 */
	public function dashboard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->sync_log();

		$log    = $this->get_log();
		$colors = array( '#00D8BA', '#309f8f', '#00483E', '#00A0FC', '#1071a9', '#1e3457', '#FFBC14', '#e9c56a', '#8F6600', '#e42d47', '#5C000D' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php require plugin_dir_path( REFERRER_ANALYTICS ) . '/templates/callout.php'; ?>
			<?php if ( ! $log ) : ?>
				<?php esc_html_e( 'No referrer data available yet.', 'referrer-analytics' ); ?>
			<?php else : ?>
				<h2><?php esc_html_e( 'Referrer Statistics', 'referrer-analytics' ); ?></h2>
				<div class="referrer-analytics-boxes">
					<?php
					$period     = 7;
					$type       = 'referrer_name';
					$type_title = __( 'Referrers', 'referrer-analytics' );
					$count      = 5;
					require plugin_dir_path( REFERRER_ANALYTICS ) . '/templates/line-chart.php';

					$period = 30;
					require plugin_dir_path( REFERRER_ANALYTICS ) . '/templates/line-chart.php';

					$count  = 10;
					$period = 7;
					require plugin_dir_path( REFERRER_ANALYTICS ) . '/templates/pie-chart.php';

					$period = 30;
					require plugin_dir_path( REFERRER_ANALYTICS ) . '/templates/pie-chart.php';

					$period = 7;
					$count  = 10;
					require plugin_dir_path( REFERRER_ANALYTICS ) . '/templates/popular-list.php';

					$period = 30;
					require plugin_dir_path( REFERRER_ANALYTICS ) . '/templates/popular-list.php';

					$period = 365;
					require plugin_dir_path( REFERRER_ANALYTICS ) . '/templates/popular-list.php';
					?>
				</div>
				<h2><?php esc_html_e( 'Referrer Type Statistics', 'referrer-analytics' ); ?></h2>
				<div class="referrer-analytics-boxes">
					<?php
					$type       = 'referrer_type';
					$type_title = __( 'Types', 'referrer-analytics' );
					$period     = 7;
					$count      = 5;
					require plugin_dir_path( REFERRER_ANALYTICS ) . '/templates/line-chart.php';

					$period = 30;
					require plugin_dir_path( REFERRER_ANALYTICS ) . '/templates/line-chart.php';

					$count  = 10;
					$period = 7;
					require plugin_dir_path( REFERRER_ANALYTICS ) . '/templates/pie-chart.php';

					$period = 30;
					require plugin_dir_path( REFERRER_ANALYTICS ) . '/templates/pie-chart.php';

					$period = 7;
					$count  = 10;
					require plugin_dir_path( REFERRER_ANALYTICS ) . '/templates/popular-list.php';

					$period = 30;
					require plugin_dir_path( REFERRER_ANALYTICS ) . '/templates/popular-list.php';

					$period = 365;
					require plugin_dir_path( REFERRER_ANALYTICS ) . '/templates/popular-list.php';
					?>
				</div>
				<h2><?php esc_html_e( 'Referred Destinations Statistics', 'referrer-analytics' ); ?></h2>
				<div class="referrer-analytics-boxes">
					<?php
					$type   = 'url_destination';
					$period = 7;
					require plugin_dir_path( REFERRER_ANALYTICS ) . '/templates/popular-list.php';

					$period = 30;
					require plugin_dir_path( REFERRER_ANALYTICS ) . '/templates/popular-list.php';

					$period = 365;
					require plugin_dir_path( REFERRER_ANALYTICS ) . '/templates/popular-list.php';
					?>
				</div>
				<div class="referrer-analytics-boxes">
					<?php require plugin_dir_path( REFERRER_ANALYTICS ) . '/templates/unknowns.php'; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Output for the admin settings page.
	 */
	public function admin_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php require plugin_dir_path( REFERRER_ANALYTICS ) . '/templates/callout.php'; ?>

			<form action="options.php" method="post">
			<?php
			// Output security fields for the registered setting "referrer_analytics".
			settings_fields( 'referrer_analytics' );

			// Output setting sections and their fields.
			do_settings_sections( 'referrer_analytics' );

			// Output save settings button.
			submit_button( 'Save Settings' );
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Output for the log dashboard.
	 */
	public function log_dashboard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->sync_log();
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php require plugin_dir_path( REFERRER_ANALYTICS ) . '/templates/callout.php'; ?>

			<?php
			/**
			 * Include the log table class.
			 */
			require plugin_dir_path( REFERRER_ANALYTICS ) . '/classes/class-referrer-analytics-log-table.php';

			$table_data = new Referrer_Analytics_Log_Table();

			// Fetch, prepare, sort, and filter our data...
			$table_data->prepare_items();
			?>
			<form id="log-table" method="post">
				<?php wp_nonce_field( 'referrer_analytics_nonce', 'referrer_analytics_nonce' ); ?>
				<input type="hidden" name="paged" value="1" />
				<?php $table_data->display(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Filters link rel attributes.
	 *
	 * @param string $rel_values Link rel attribute value.
	 */
	public function filter_link_rel( $rel_values ) {
		if ( 'enabled' === $this->options['disable_noreferrer']['value'] ) {
			return str_replace( 'noreferrer', '', $rel_values );
		}

		return $rel_values;
	}

	/**
	 * Installs the DB tables.
	 */
	public function install() {
		global $wpdb;

		$charset_collate      = $wpdb->get_charset_collate();
		$installed_db_version = get_option( 'referrer_analytics_db_version' );

		if ( REFERRER_ANALYTICS_DB_VERSION !== $installed_db_version ) {
			$table_name = $this->table( 'log' );
			$sql        = "CREATE TABLE $table_name (
				referrer_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				date_recorded DATETIME NOT NULL,
				referrer_url VARCHAR(255) NOT NULL,
				referrer_primary_url VARCHAR(255) NOT NULL,
				referrer_host VARCHAR(255) NOT NULL,
				referrer_type VARCHAR(255) NOT NULL,
				referrer_name VARCHAR(255) NOT NULL,
				visitor_ip VARCHAR(39) NOT NULL,
				user_id BIGINT NOT NULL,
				url_destination VARCHAR(255) NOT NULL,
				is_flagged BOOLEAN NOT NULL DEFAULT FALSE,
				PRIMARY KEY (`referrer_id`)) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			update_option( 'referrer_analytics_db_version', REFERRER_ANALYTICS_DB_VERSION );
		}
	}

	/**
	 * Check if any DB updates need to be made.
	 */
	public function db_version_check() {
		if ( get_site_option( 'referrer_analytics_db_version' ) !== REFERRER_ANALYTICS_DB_VERSION ) {
			$this->install();
		}
	}

	/**
	 * Loads the plugin options.
	 */
	public function load_options() {
		$options = get_option( 'referrer_analytics_options' );
		foreach ( $this->options as $key => $args ) {
			$this->options[ $key ]['value'] = ! empty( $options[ $key ] ) ? $options[ $key ] : $this->options[ $key ]['default'];

			// Clean the 'hosts' option of empty 'host' keys.
			if ( 'hosts' === $key && $this->options[ $key ]['value'] ) {
				foreach ( $this->options[ $key ]['value'] as $k => $host ) {
					if ( empty( $host['host'] ) ) {
						unset( $this->options[ $key ]['value'][ $k ] );
					}
				}
			}
		}
	}

	/**
	 * Process a referrer & redirects with URL parameters if available.
	 */
	public function process_referrer() {
		// Don't do anything is in the admin dashboard or has already been redirected.
		if ( is_admin() || ! empty( $_REQUEST[ REFERRER_ANALYTICS_REDIRECT_PARAM ] ) ) { // phpcs:ignore
			return;
		}

		$referrer = $this->get_referrer();
		if ( empty( $referrer ) || empty( $referrer['host'] ) || ! $referrer['external'] ) {
			return;
		}

		// Log the referrer if enabled.
		if ( 'enabled' === $this->options['logging']['value'] && $referrer['external'] ) {
			$this->log_referrer( $referrer );
		}

		$current_url    = $this->current_url();
		$utm_parameters = array(
			'utm_source',
			'utm_medium',
			'utm_campaign',
		);

		// Check if redirect with URL parameters is enabled.
		if ( 'enabled' === $this->options['redirect_with_utm']['value'] ) {
			// Check if the current URL has parameters & process.
			if ( ! empty( $current_url['query'] ) ) {
				// Check if UTM parameters are already set.
				foreach ( $utm_parameters as $key => $parameter ) {
					if ( ! empty( $current_url['query'][ $parameter ] ) && 'ignore' !== $this->options[ $parameter ]['value'] && ! empty( $referrer[ $this->options[ $parameter ]['value'] ] ) ) {
						$current_url['query'][ $parameter ] = $current_url['query'][ $parameter ];
					}
				}
			} else {
				// No existing URL parameters set, define ones based on the referrer.
				$current_url['query'] = array();
				foreach ( $utm_parameters as $key => $parameter ) {
					if ( 'ignore' !== $parameter && ! empty( $referrer[ $this->options[ $parameter ]['value'] ] ) ) {
						$current_url['query'][ $parameter ] = $referrer[ $this->options[ $parameter ]['value'] ];
					}
				}
			}

			// Only redirect is URL parameters are available.
			if ( empty( $current_url['query'] ) ) {
				return;
			}

			// Add to avoid redirect loops.
			$current_url['query'][ REFERRER_ANALYTICS_REDIRECT_PARAM ] = 1;

			// Redirect the user with the new URL parameters values.
			$redirect_url = $current_url['scheme'] . '://' . $current_url['host'] . $current_url['path'] . '?' . http_build_query( $current_url['query'] );

			wp_safe_redirect( $redirect_url );
			exit;
		}
	}

	/**
	 * Logs a referrer.
	 *
	 * @param array $referrer Referrer information to log.
	 */
	public function log_referrer( $referrer ) {
		if ( empty( $referrer ) || empty( $referrer['host'] ) || ! $this->table( 'log' ) ) {
			return false;
		}

		global $wpdb;

		$current_url = $this->current_url();

		$entry = array(
			'date_recorded'        => current_time( 'mysql' ),
			'referrer_url'         => ! empty( $referrer['url'] ) ? esc_url_raw( $referrer['url'] ) : false,
			'referrer_primary_url' => ! empty( $referrer['primary_url'] ) ? esc_url_raw( $referrer['primary_url'] ) : false,
			'referrer_host'        => $referrer['host'],
			'referrer_type'        => ! empty( $referrer['type'] ) ? sanitize_text_field( $referrer['type'] ) : false,
			'referrer_name'        => ! empty( $referrer['name'] ) ? sanitize_text_field( $referrer['name'] ) : false,
			'visitor_ip'           => $this->get_ip_address() ? wp_privacy_anonymize_ip( $this->get_ip_address() ) : 'unknown',
			'user_id'              => get_current_user_id(),
			'url_destination'      => $current_url['current'],
			'is_flagged'           => ! empty( $referrer['is_flagged'] ) ? true : false,
		);

		$wpdb->insert( // phpcs:ignore
			$this->table( 'log' ),
			$entry,
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);
	}

	/**
	 * Returns a table name.
	 *
	 * @param string $key Table key.
	 */
	public function table( $key ) {
		global $wpdb;

		$tables = array(
			'log' => $wpdb->prefix . 'referrer_analytics',
		);

		if ( empty( $tables[ $key ] ) ) {
			return false;
		}

		return $tables[ $key ];
	}

	/**
	 * Returns the current IP address.
	 */
	public function get_ip_address() {
		foreach ( array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		) as $key ) {
			if ( array_key_exists( $key, $_SERVER ) === true ) {
				foreach ( explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) ) as $ip_address ) {
					$ip_address = trim( $ip_address );

					if ( filter_var(
						$ip_address,
						FILTER_VALIDATE_IP,
						FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
					) !== false
					) {
						return $ip_address;
					}
				}
			}
		}
	}

	/**
	 * Returns referrer information.
	 */
	public function get_referrer() {
		$current_url  = $this->current_url();
		$referrer_url = ! empty( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : false;
		$referrer     = array();

		if ( $referrer_url ) {
			$parsed_referrer_url = wp_parse_url( $referrer_url );

			$host = ! empty( $parsed_referrer_url['host'] ) ? $parsed_referrer_url['host'] : false;
			if ( $host ) {
				$referrer['host']   = $host;
				$referrer['url']    = $referrer_url;
				$referrer['scheme'] = ! empty( $url['scheme'] ) ? $url['scheme'] : false;
				$referrer['path']   = ! empty( $url['path'] ) ? $url['path'] : false;
			} else {
				// No referrer available.
				return $referrer;
			}
		} elseif ( 'enabled' === $this->options['url_referrer_fallback']['value'] ) {
			// No server referrer available, attempt to get it from the URL.
			if ( ! empty( $current_url['query'] ) && ! empty( $current_url['query'][ $this->options['referrer_fallback_param']['value'] ] ) ) {
				// A URL referrer parameter was found.
				$referrer['host'] = $current_url['query'][ $this->options['referrer_fallback_param']['value'] ];
			} else {
				// No referrer available.
				return $referrer;
			}
		} else {
			// No referrer available.
			return $referrer;
		}

		// Check if the referrer is from the current host.
		if ( ! empty( $referrer['host'] ) ) {
			$referrer['external'] = $this->is_external_host( $referrer['host'] );

			// Merge known referrer information if available.
			$known_hosts = $this->get_known_hosts();
			if ( array_key_exists( $referrer['host'], $known_hosts ) ) {
				$referrer = array_merge( $referrer, $known_hosts[ $referrer['host'] ] );
			}
		}

		return $referrer;
	}

	/**
	 * Checks if a host is external.
	 *
	 * @param string $host Hostname.
	 */
	public function is_external_host( $host ) {
		$current_url = $this->current_url();
		if ( ! empty( $current_url['host'] ) && $current_url['host'] === $host || ( 'www.' . $current_url['host'] ) === $host || ( 'www' . $host ) === $current_url['host'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns known referrers.
	 */
	public function get_known_hosts() {
		$hosts = array();

		// Check if all referrers should be tracked, if so add the pre-defined plugin referrers.
		if ( 'enabled' === $this->options['track_all_referrers']['value'] ) {
			$predefined_hosts = referrer_analytics_predefined_hosts();
		}

		$defined_hosts = $this->options['hosts']['value'];

		if ( empty( $predefined_hosts ) && empty( $defined_hosts ) ) {
			return $hosts;
		}

		if ( ! empty( $predefined_hosts ) ) {
			foreach ( $predefined_hosts as $key => $host ) {
				if ( empty( $host ) || empty( $host['host'] ) ) {
					continue;
				}
				$hosts[ $host['host'] ] = $host;
			}
		}

		if ( ! empty( $defined_hosts ) ) {
			foreach ( $defined_hosts as $key => $host ) {
				if ( empty( $host ) || empty( $host['host'] ) ) {
					continue;
				}

				$hosts[ $host['host'] ] = $host;
			}
		}

		return $hosts;
	}

	/**
	 * Returns the current URL.
	 */
	public function current_url() {
		global $wp;

		$home_url = wp_parse_url( home_url() );
		$url      = array(
			'current' => "{$home_url['scheme']}://{$home_url['host']}" . add_query_arg( null, null ),
		);

		$url = array_merge( $url, wp_parse_url( $url['current'] ) );

		// Parse the URL query string if available.
		if ( ! empty( $url['query'] ) ) {
			parse_str( $url['query'], $url['query'] );
		}

		return $url;
	}

	/**
	 * Sets cookies if enabled.
	 */
	public function set_cookies() {
		// Only store cookies if enabled.
		if ( 'enabled' !== $this->options['store_cookies']['value'] ) {
			return;
		}

		// Don't set cookies in the admin dashboard or after a referred user has been redirected.
		if ( is_admin() || ! empty( $_REQUEST[ REFERRER_ANALYTICS_REDIRECT_PARAM ] ) ) { // phpcs:ignore
			return;
		}

		$referrer = $this->get_referrer();
		// Ignore empty & internal referrers.
		if ( empty( $referrer ) || ! $referrer['external'] ) {
			return;
		}

		$cookie_expiration = strtotime( current_time( 'mysql' ) ) + ( $this->options['cookie_expiration']['value'] * DAY_IN_SECONDS );

		$current_url = $this->current_url();
		if ( ! empty( $current_url['current'] ) ) {
			setcookie(
				'referrer-analytics-referrer_destination',
				$current_url['current'],
				$cookie_expiration,
				COOKIEPATH,
				COOKIE_DOMAIN
			);
		}

		foreach ( $referrer as $key => $value ) {
			setcookie(
				'referrer-analytics-referrer_' . $key,
				$value,
				$cookie_expiration,
				COOKIEPATH,
				COOKIE_DOMAIN
			);
		}
	}

	/**
	 * Parses the log.
	 *
	 * @param array  $log Log entries.
	 * @param int    $period Number of days.
	 * @param int    $count Number of items.
	 * @param string $type Type key.
	 */
	public function parse_log( $log, $period, $count, $type ) {
		$labels = array();

		$today     = new DateTime();
		$begin     = $today->sub( new DateInterval( 'P' . ( $period - 1 ) . 'D' ) );
		$end       = new DateTime();
		$end       = $end->modify( '+1 day' );
		$interval  = new DateInterval( 'P1D' );
		$daterange = new DatePeriod( $begin, $interval, $end );
		foreach ( $daterange as $date ) {
			$labels[] = $date->format( 'm/d' );
		}

		$parsed_log = array();
		foreach ( $log as $key => $entry ) {
			$entry_date = new DateTime( $entry['date_recorded'] );
			if ( $entry_date->getTimestamp() >= $begin->getTimestamp() && $entry_date->getTimestamp() <= $end->getTimestamp() ) {
				switch ( $type ) {
					case 'referrer_name':
						if ( $entry['referrer_name'] ) {
							$type_key = $entry['referrer_name'];
						} elseif ( $entry['referrer_host'] ) {
							$type_key = $entry['referrer_host'];
						} else {
							$type_key = 'N/A';
						}
						break;
					default:
						$type_key = $entry[ $type ] ? $entry[ $type ] : 'N/A';
				}

				if ( ! empty( $parsed_log[ $type_key ] ) ) {
					$parsed_log[ $type_key ]['total']++;

					if ( ! empty( $parsed_log[ $type_key ]['data'][ $entry_date->format( 'Ymd' ) ] ) ) {
						$parsed_log[ $type_key ]['data'][ $entry_date->format( 'Ymd' ) ]++;
					} else {
						$parsed_log[ $type_key ]['data'][ $entry_date->format( 'Ymd' ) ] = 1;
					}
				} else {
					$info = array(
						'label' => $type_key,
						'data'  => array(
							$entry_date->format( 'Ymd' ) => 1,
						),
						'total' => 1,
					);

					switch ( $type ) {
						case 'referrer_url':
						case 'referrer_name':
							$info['referrer_url']         = $entry['referrer_url'];
							$info['referrer_primary_url'] = $entry['referrer_primary_url'];
							break;
						case 'url_destination':
							$info['url_destination'] = $entry['url_destination'];
							break;
					}

					$parsed_log[ $type_key ] = $info;
				}
			}
		}

		// Sort the array.
		usort(
			$parsed_log,
			function( $a, $b ) {
				return $b['total'] <=> $a['total'];
			}
		);

		// Trim the array.
		$parsed_log = array_slice( $parsed_log, 0, $count );

		// Fill in the empty dates.
		foreach ( $daterange as $date ) {
			$date_key = $date->format( 'Ymd' );
			foreach ( $parsed_log as $key => $entry ) {
				if ( empty( $entry['data'][ $date_key ] ) ) {
					$parsed_log[ $key ]['data'][ $date_key ] = 0;
				}
				ksort( $parsed_log[ $key ]['data'] );
			}
		}

		return array(
			'labels' => $labels,
			'log'    => $parsed_log,
		);
	}

	/**
	 * Returns the log entries.
	 *
	 * @param array   $args Query arguments.
	 * @param boolean $get_var Get variable versus array.
	 */
	public function get_log( $args = array(), $get_var = false ) {
		global $wpdb;

		$sql     = 'SELECT';

		// Select.
		$select = '';
		if ( ! empty( $args['select'] ) ) {
			foreach ( $args['select'] as $key => $value ) {
				if ( $select ) {
					$select .= ', ';
				}
				$select .= $value;
			}
		} else {
			$select = '*';
		}

		$sql .= ' ' . $select;

		// From.
		$sql .= ' FROM ' . $this->table( 'log' );

		// Where.
		$where = '';
		if ( ! empty( $args['where'] ) ) {
			foreach ( $args['where'] as $key => $where_stmt ) {
				if ( ! $where ) {
					$where .= 'WHERE ';
				}

				foreach ( $where_stmt as $k => $array ) {
					$where .= $array['key'];
					switch ( $array['relation'] ) {
						case '=':
							$where .= ' = ';
							if ( is_numeric( $array['value'] ) ) {
								$where .= ' ' . $array['value'];
							} else {
								$where .= ' "' . $array['value'] . '"';
							}
							break;
					}
				}
			}
		}

		$sql .= ' ' . $where;

		// Limit.
		if ( ! empty( $args['limit'] ) ) {
			$sql .= ' LIMIT ' . $args['limit'];
		}

		// Offset.
		if ( ! empty( $args['offset'] ) ) {
			$sql .= ' OFFSET ' . $args['offset'];
		}

		if ( $get_var ) {
			return $wpdb->get_var( $sql ); // phpcs:ignore
		} elseif ( ! empty( $args['limit'] ) && 1 === $args['limit'] ) {
			return $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore
		} else {
			return $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore
		}
	}

	/**
	 * Syncs the log.
	 */
	public function sync_log() {
		global $wpdb;

		$known_hosts = $this->get_known_hosts();
		if ( empty( $known_hosts ) ) {
			return;
		}

		$log            = $this->get_log();
		$record_columns = array( 'name', 'type', 'primary_url' );
		foreach ( $log as $key => $entry ) {
			// Delete invalid entries.
			if ( empty( $entry['referrer_host'] ) || ! $this->is_external_host( $entry['referrer_host'] ) ) {
				$wpdb->delete( // phpcs:ignore
					$this->table( 'log' ),
					array(
						'referrer_id' => $entry['referrer_id'],
					)
				);

				continue;
			}

			$update = array();
			if ( array_key_exists( $entry['referrer_host'], $known_hosts ) ) {
				// Match found, update the record.
				foreach ( $record_columns as $k => $record ) {
					if ( ! empty( $known_hosts[ $entry['referrer_host'] ][ $record ] ) && $entry[ 'referrer_' . $record ] !== $known_hosts[ $entry['referrer_host'] ][ $record ] ) {
						$update[ 'referrer_' . $record ] = $known_hosts[ $entry['referrer_host'] ][ $record ];
					}
				}

				if ( ! empty( $known_hosts[ $entry['referrer_host'] ]['flag'] ) && ! $entry['is_flagged'] ) {
					$update['is_flagged'] = true;
				} else {
					$update['is_flagged'] = false;
				}
			} else {
				// No match found, clear the record.
				foreach ( $record_columns as $k => $record ) {
					$update[ 'referrer_' . $record ] = '';
				}

				$update['is_flagged'] = false;
			}

			if ( $update ) {
				$wpdb->update( // phpcs:ignore
					$this->table( 'log' ),
					$update,
					array( 'referrer_id' => $entry['referrer_id'] )
				);
			}
		}
	}
}
