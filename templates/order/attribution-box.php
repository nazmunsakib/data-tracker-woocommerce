<?php
/**
 * Order attribution meta box template.
 *
 * @package DataTracker
 * @var array $display
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="dtw-order-attribution">
	<?php if ( ! $display['has_data'] ) : ?>
		<p><?php esc_html_e( 'No traffic source information captured for this order yet.', 'data-tracker-woocommerce' ); ?></p>
		<p class="description"><?php esc_html_e( 'This appears automatically when a customer arrives through UTM links or advertising.', 'data-tracker-woocommerce' ); ?></p>
	<?php else : ?>
		<table class="widefat striped dtw-order-attribution__table">
			<tbody>
				<?php if ( ! empty( $display['data']['source'] ) ) : ?>
					<tr>
						<th><?php esc_html_e( 'Traffic Source', 'data-tracker-woocommerce' ); ?></th>
						<td><?php echo esc_html( $display['data']['source'] ); ?></td>
					</tr>
				<?php endif; ?>
				<?php if ( ! empty( $display['data']['medium'] ) ) : ?>
					<tr>
						<th><?php esc_html_e( 'Medium', 'data-tracker-woocommerce' ); ?></th>
						<td><?php echo esc_html( $display['data']['medium'] ); ?></td>
					</tr>
				<?php endif; ?>
				<?php if ( ! empty( $display['data']['campaign'] ) ) : ?>
					<tr>
						<th><?php esc_html_e( 'Campaign', 'data-tracker-woocommerce' ); ?></th>
						<td><?php echo esc_html( $display['data']['campaign'] ); ?></td>
					</tr>
				<?php endif; ?>
				<tr>
					<th><?php esc_html_e( 'First Touch', 'data-tracker-woocommerce' ); ?></th>
					<td><?php echo esc_html( $display['first_label'] ? $display['first_label'] : __( 'Direct', 'data-tracker-woocommerce' ) ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Last Touch', 'data-tracker-woocommerce' ); ?></th>
					<td><?php echo esc_html( $display['last_label'] ? $display['last_label'] : __( 'Direct', 'data-tracker-woocommerce' ) ); ?></td>
				</tr>
			</tbody>
		</table>
	<?php endif; ?>
</div>