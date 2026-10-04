<?php
/**
 * Premium page header with brand bar and tab navigation.
 *
 * @var string $active_tab
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $active_tab ) ) {
	$active_tab = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : 'data-tracker';
}

$tabs = array(
	'data-tracker'             => array( 'label' => __( 'Dashboard', 'data-tracker-woocommerce' ), 'icon' => 'dashicons-dashboard' ),
	'data-tracker-connections' => array( 'label' => __( 'Connections', 'data-tracker-woocommerce' ), 'icon' => 'dashicons-plugins-checked' ),
	'data-tracker-tracking'    => array( 'label' => __( 'Tracking', 'data-tracker-woocommerce' ), 'icon' => 'dashicons-chart-line' ),
	'data-tracker-test'        => array( 'label' => __( 'Test Tracking', 'data-tracker-woocommerce' ), 'icon' => 'dashicons-clipboard' ),
	'data-tracker-settings'    => array( 'label' => __( 'Settings', 'data-tracker-woocommerce' ), 'icon' => 'dashicons-admin-generic' ),
);

$connected_count = 0;
$total_count     = count( $tabs );

if ( function_exists( 'dtw' ) ) {
	$connected_count = count( dtw()->platforms()->connected() );
	$total_count     = count( dtw()->platforms()->all() );
}
?>
<div class="dtw-app">
	<div class="dtw-topbar">
		<div class="dtw-brand">
			<span class="dtw-brand__mark" aria-hidden="true">
				<svg width="34" height="34" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect width="34" height="34" rx="9" fill="#1D4ED8"/>
					<path d="M7 19l5-6 4 4 6-7 5 5" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
					<circle cx="27" cy="10" r="2.2" fill="#fff"/>
				</svg>
			</span>
			<div class="dtw-brand__text">
				<span class="dtw-brand__name"><?php esc_html_e( 'Data Tracker', 'data-tracker-woocommerce' ); ?></span>
				<span class="dtw-brand__sub"><?php esc_html_e( 'for WooCommerce', 'data-tracker-woocommerce' ); ?></span>
			</div>
			<span class="dtw-brand__version">v<?php echo esc_html( DTW_VERSION ); ?></span>
		</div>
		<div class="dtw-topbar__actions">
			<span class="dtw-chip dtw-chip--count">
				<span class="dtw-chip__dot" aria-hidden="true"></span>
				<?php
				/* translators: 1: connected count, 2: total count */
				printf( esc_html__( '%1$d of %2$d platforms connected', 'data-tracker-woocommerce' ), (int) $connected_count, (int) $total_count );
				?>
			</span>
			<a class="dtw-btn dtw-btn--ghost" href="<?php echo esc_url( admin_url( 'admin.php?page=data-tracker-test' ) ); ?>">
				<span class="dashicons dashicons-clipboard" aria-hidden="true"></span>
				<?php esc_html_e( 'Test Tracking', 'data-tracker-woocommerce' ); ?>
			</a>
		</div>
	</div>

	<nav class="dtw-tabs" aria-label="<?php esc_attr_e( 'Data Tracker navigation', 'data-tracker-woocommerce' ); ?>">
		<?php foreach ( $tabs as $slug => $tab ) : ?>
			<a
				class="dtw-tab <?php echo $active_tab === $slug ? 'is-active' : ''; ?>"
				href="<?php echo esc_url( admin_url( 'admin.php?page=' . $slug ) ); ?>"
				aria-current="<?php echo $active_tab === $slug ? 'page' : 'false'; ?>"
			>
				<span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>" aria-hidden="true"></span>
				<?php echo esc_html( $tab['label'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<div class="dtw-page">