<?php
/**
 * Admin bootstrap.
 *
 * @package DataTracker
 */

namespace DataTracker\Admin;

defined( 'ABSPATH' ) || exit;

use DataTracker\Core\Options;
use DataTracker\Diagnostics\EventLog;
use DataTracker\Platforms\PlatformManager;

/**
 * Registers admin menus, assets and notices, and wires the admin pages.
 */
class Admin {

	const SLUG = 'data-tracker';

	/**
	 * Options.
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * Platforms.
	 *
	 * @var PlatformManager
	 */
	private $platforms;

	/**
	 * Event log.
	 *
	 * @var EventLog
	 */
	private $event_log;

	/**
	 * Page instances.
	 *
	 * @var array
	 */
	private $pages = array();

	/**
	 * Constructor.
	 *
	 * @param Options         $options   Options.
	 * @param PlatformManager $platforms Platforms.
	 * @param EventLog        $event_log Event log.
	 */
	public function __construct( Options $options, PlatformManager $platforms, EventLog $event_log ) {
		$this->options   = $options;
		$this->platforms = $platforms;
		$this->event_log = $event_log;

		$this->pages = array(
			'connections' => new Connections( $options, $platforms ),
			'tracking'    => new Tracking( $options ),
			'test'        => new TestTracking( $options, $platforms ),
			'settings'    => new Settings( $options ),
			'wizard'      => new Wizard( $options ),
		);

		new FormHandler( $options, $platforms );

		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'admin_init', array( $this, 'suppress_foreign_notices' ), 999 );
		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
		add_filter( 'plugin_action_links_' . DTW_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );
	}

	/**
	 * Add quick links to the plugin row on the Plugins screen.
	 *
	 * @param array $links Existing action links.
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$dashboard = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=' . self::SLUG ) ),
			esc_html__( 'Dashboard', 'data-tracker-woocommerce' )
		);
		$settings = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=' . self::SLUG . '-settings' ) ),
			esc_html__( 'Settings', 'data-tracker-woocommerce' )
		);

		array_unshift( $links, $dashboard, $settings );

		return $links;
	}

	/**
	 * Register the admin menu.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_menu_page(
			__( 'Data Tracker', 'data-tracker-woocommerce' ),
			__( 'Data Tracker', 'data-tracker-woocommerce' ),
			'manage_options',
			self::SLUG,
			array( $this, 'render_dashboard' ),
			'dashicons-chart-area',
			56
		);

		add_submenu_page( self::SLUG, __( 'Dashboard', 'data-tracker-woocommerce' ), __( 'Dashboard', 'data-tracker-woocommerce' ), 'manage_options', self::SLUG, array( $this, 'render_dashboard' ) );
		add_submenu_page( self::SLUG, __( 'Connections', 'data-tracker-woocommerce' ), __( 'Connections', 'data-tracker-woocommerce' ), 'manage_options', self::SLUG . '-connections', array( $this, 'render_connections' ) );
		add_submenu_page( self::SLUG, __( 'Tracking', 'data-tracker-woocommerce' ), __( 'Tracking', 'data-tracker-woocommerce' ), 'manage_options', self::SLUG . '-tracking', array( $this, 'render_tracking' ) );
		add_submenu_page( self::SLUG, __( 'Test Tracking', 'data-tracker-woocommerce' ), __( 'Test Tracking', 'data-tracker-woocommerce' ), 'manage_options', self::SLUG . '-test', array( $this, 'render_test' ) );
		add_submenu_page( self::SLUG, __( 'Settings', 'data-tracker-woocommerce' ), __( 'Settings', 'data-tracker-woocommerce' ), 'manage_options', self::SLUG . '-settings', array( $this, 'render_settings' ) );

		add_submenu_page(
			null,
			__( 'Setup Wizard', 'data-tracker-woocommerce' ),
			__( 'Setup Wizard', 'data-tracker-woocommerce' ),
			'manage_options',
			self::SLUG . '-wizard',
			array( $this, 'render_wizard' )
		);
	}

	/**
	 * Whether the current request is one of the Data Tracker admin pages.
	 *
	 * @return bool
	 */
	public function is_dtw_page() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( $screen ) {
			return false !== strpos( (string) $screen->id, self::SLUG );
		}

		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		return 0 === strpos( $page, self::SLUG );
	}

	/**
	 * Enqueue admin assets only on Data Tracker pages.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( ! $this->is_dtw_page() ) {
			return;
		}

		wp_enqueue_style( 'dtw-admin', DTW_PLUGIN_URL . 'assets/css/admin.css', array(), DTW_VERSION );
		wp_enqueue_script( 'dtw-admin', DTW_PLUGIN_URL . 'assets/js/admin.js', array(), DTW_VERSION, true );

		wp_localize_script(
			'dtw-admin',
			'DTW_ADMIN',
			array(
				'rest_url'    => esc_url_raw( rest_url( 'data-tracker/v1' ) ),
				'rest_nonce'  => wp_create_nonce( 'wp_rest' ),
				'test_url'    => esc_url_raw( add_query_arg( 'dtw_test', '1', home_url( '/' ) ) ),
				'test_config' => $this->test_config(),
			)
		);
	}

	/**
	 * Data used to fire direct tracking test events from the Test page.
	 *
	 * @return array|null
	 */
	private function test_config() {
		$screen       = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$is_test_page = ( $screen && false !== strpos( (string) $screen->id, 'data-tracker-test' ) )
			|| ( isset( $_GET['page'] ) && 'data-tracker-test' === $_GET['page'] );

		if ( ! $is_test_page ) {
			return null;
		}

		$ga4  = $this->platforms->get( 'ga4' );
		$meta = $this->platforms->get( 'meta' );
		$ads  = $this->platforms->get( 'google_ads' );

		return array(
			'ga4_id'        => $ga4->is_connected() ? $ga4->get_measurement_id() : '',
			'meta_id'       => $meta->is_connected() ? $meta->get_pixel_id() : '',
			'ads_id'        => $ads->is_connected() ? $ads->get_conversion_id() : '',
			'ads_label'     => $ads->is_connected() ? $ads->get_conversion_label() : '',
			'consent'       => (bool) $this->options->get( 'respect_consent' ),
			'categories'    => array_values(
				array_intersect(
					array( 'product_view', 'add_to_cart', 'begin_checkout', 'purchase' ),
					(array) $this->options->get( 'enabled_events' )
				)
			),
			'frontend_nonce' => wp_create_nonce( 'dtw_frontend' ),
			'token'          => \DataTracker\Support\LogToken::get(),
			'log_url'        => esc_url_raw( rest_url( 'data-tracker/v1/events' ) ),
		);
	}

	/**
	 * Admin notices (welcome + result messages).
	 *
	 * @return void
	 */
	public function admin_notices() {
		$on_dtw = $this->is_dtw_page();

		$message = isset( $_GET['dtw_message'] ) ? sanitize_key( wp_unslash( $_GET['dtw_message'] ) ) : '';
		if ( $on_dtw && '' !== $message ) {
			$success = array(
				'connected'    => __( 'Tracking platform connected.', 'data-tracker-woocommerce' ),
				'disconnected' => __( 'Tracking platform disconnected.', 'data-tracker-woocommerce' ),
				'saved'        => __( 'Settings saved.', 'data-tracker-woocommerce' ),
				'welcome-done' => __( 'Setup complete. Your tracking is ready to test.', 'data-tracker-woocommerce' ),
			);
			$info    = array(
				'nochange' => __( 'No tracking IDs were entered, so nothing was changed. Connect Google Analytics, Meta Pixel or Google Ads to start tracking.', 'data-tracker-woocommerce' ),
			);

			if ( isset( $success[ $message ] ) ) {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $success[ $message ] ) . '</p></div>';
			} elseif ( isset( $info[ $message ] ) ) {
				echo '<div class="notice notice-info is-dismissible"><p>' . esc_html( $info[ $message ] ) . '</p></div>';
			}
		}

		if ( ! $on_dtw && (bool) $this->options->get( 'show_welcome' ) ) {
			$wizard_url = admin_url( 'admin.php?page=' . self::SLUG . '-wizard' );
			$dismiss_url = wp_nonce_url(
				add_query_arg( 'dtw_action', 'dismiss_welcome', admin_url( 'admin-post.php' ) ),
				'dtw_dismiss_welcome'
			);
			?>
			<div class="notice notice-info is-dismissible">
				<p>
					<strong><?php esc_html_e( 'Welcome to Data Tracker for WooCommerce!', 'data-tracker-woocommerce' ); ?></strong>
					<?php esc_html_e( 'Set up your ecommerce tracking in a few simple steps.', 'data-tracker-woocommerce' ); ?>
				</p>
				<p>
					<a class="button button-primary" href="<?php echo esc_url( $wizard_url ); ?>"><?php esc_html_e( 'Get Started', 'data-tracker-woocommerce' ); ?></a>
					<a class="button" href="<?php echo esc_url( $dismiss_url ); ?>"><?php esc_html_e( 'Set up later', 'data-tracker-woocommerce' ); ?></a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Render the dashboard.
	 *
	 * @return void
	 */
	public function render_dashboard() {
		$dashboard = new Dashboard( $this->options, $this->platforms, $this->event_log );
		$dashboard->render();
	}

	/**
	 * Render the connections page.
	 *
	 * @return void
	 */
	public function render_connections() {
		$this->pages['connections']->render();
	}

	/**
	 * Render the tracking page.
	 *
	 * @return void
	 */
	public function render_tracking() {
		$this->pages['tracking']->render();
	}

	/**
	 * Render the test tracking page.
	 *
	 * @return void
	 */
	public function render_test() {
		$this->pages['test']->render();
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_settings() {
		$this->pages['settings']->render();
	}

	/**
	 * Render the setup wizard.
	 *
	 * @return void
	 */
	public function render_wizard() {
		$this->pages['wizard']->render();
	}

	/**
	 * Add a body class on Data Tracker pages for admin-wide styling.
	 *
	 * @param string $classes Existing classes.
	 * @return string
	 */
	public function admin_body_class( $classes ) {
		if ( $this->is_dtw_page() ) {
			$classes .= ' dtw-admin';
		}

		return $classes;
	}

	/**
	 * Hide notices from other plugins on Data Tracker pages so the admin
	 * stays clean. The plugin's own notices are re-added.
	 *
	 * @return void
	 */
	public function suppress_foreign_notices() {
		if ( ! $this->is_dtw_page() ) {
			return;
		}

		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}
}