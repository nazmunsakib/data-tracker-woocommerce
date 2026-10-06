<?php
/**
 * Frontend page context builder.
 *
 * @package DataTracker
 */

namespace DataTracker\WooCommerce;

defined( 'ABSPATH' ) || exit;

/**
 * Builds a snapshot of the current page (product, cart, checkout, order) that
 * the tracking script uses to fire events with real WooCommerce data.
 */
class PageContext {

	/**
	 * Build the current page context.
	 *
	 * @return array
	 */
	public function get() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return array(
				'page_type' => 'other',
				'currency'  => '',
				'products'  => array(),
			);
		}

		$type = $this->detect_page_type();
		$data = array(
			'page_type' => $type,
			'currency'  => function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '',
			'products'  => array(),
		);

		switch ( $type ) {
			case 'product':
				$data['product'] = $this->get_product_data();
				break;
			case 'cart':
			case 'checkout':
				$data['cart'] = $this->get_cart_data();
				break;
			case 'order-received':
				$data['order'] = $this->get_order_data();
				break;
		}

		return $data;
	}

	/**
	 * Detect the type of current page.
	 *
	 * @return string
	 */
	private function detect_page_type() {
		if ( is_admin() ) {
			return 'admin';
		}

		if ( function_exists( 'is_order_received_page' ) && is_order_received_page() && absint( get_query_var( 'order-received' ) ) ) {
			return 'order-received';
		}
		if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page() ) {
			return 'checkout';
		}
		if ( function_exists( 'is_cart' ) && is_cart() ) {
			return 'cart';
		}
		if ( function_exists( 'is_product' ) && is_product() ) {
			return 'product';
		}
		return 'other';
	}

	/**
	 * Data for the current product page.
	 *
	 * @return array
	 */
	private function get_product_data() {
		global $product;

		if ( ! $product instanceof \WC_Product ) {
			$product = wc_get_product( get_the_ID() );
		}

		if ( ! $product ) {
			return array();
		}

		return $this->product_array( $product );
	}

	/**
	 * Data for the current cart or checkout contents.
	 *
	 * @return array
	 */
	private function get_cart_data() {
		if ( ! function_exists( 'WC' ) || null === WC()->cart || WC()->cart->is_empty() ) {
			return array();
		}

		$cart     = WC()->cart;
		$items    = array();
		$products = array();

		foreach ( $cart->get_cart() as $cart_item ) {
			$item = $this->product_array( $cart_item['data'], $cart_item['quantity'] );
			$items[] = $item;
			if ( ! empty( $item['item_id'] ) ) {
				$products[ (string) $item['item_id'] ] = $item;
			}
		}

		return array(
			'items'   => $items,
			'subtotal' => $this->format_number( $cart->get_subtotal() ),
			'total'    => $this->format_number( $cart->get_total( 'edit' ) ),
			'shipping' => $this->format_number( $cart->get_shipping_total() ),
			'tax'      => $this->format_number( $cart->get_total_tax() ),
			'coupon'   => implode( ',', $cart->get_applied_coupons() ),
		);
	}

	/**
	 * Data for the current order-received page.
	 *
	 * @return array
	 */
	private function get_order_data() {
		$order_id = absint( get_query_var( 'order-received' ) );
		$order    = wc_get_order( $order_id );

		if ( ! $order ) {
			return array();
		}

		$key = (string) get_query_var( 'key' );

		if ( '' === $key || $key !== $order->get_order_key() ) {
			return array();
		}

		$items    = array();
		$products = array();

		foreach ( $order->get_items() as $order_item ) {
			$product = $order_item->get_product();
			$item    = array(
				'item_id'   => (string) $order_item->get_product_id(),
				'item_name' => $order_item->get_name(),
				'item_sku'  => $product ? $product->get_sku() : '',
				'price'     => $this->format_number( $order_item->get_subtotal() / max( 1, $order_item->get_quantity() ) ),
				'quantity'  => $order_item->get_quantity(),
				'category'  => $product ? $this->get_product_category( $product ) : '',
			);
			$items[] = $item;
			$products[ (string) $item['item_id'] ] = $item;
		}

		$coupons = $order->get_coupon_codes();

		return array(
			'id'             => $order->get_id(),
			'key'            => $order->get_order_key(),
			'status'         => $order->get_status(),
			'trackable'      => ! in_array( $order->get_status(), array( 'failed', 'cancelled', 'refunded', 'pending', 'checkout-draft' ), true ),
			'total'          => $this->format_number( $order->get_total() ),
			'subtotal'       => $this->format_number( $order->get_subtotal() ),
			'shipping'       => $this->format_number( $order->get_shipping_total() ),
			'tax'            => $this->format_number( $order->get_total_tax() ),
			'currency'       => $order->get_currency(),
			'coupon'         => implode( ',', $coupons ),
			'items'          => $items,
			'is_guest'       => ! $order->get_user_id(),
			'payment_method' => $order->get_payment_method_title(),
		);
	}

	/**
	 * Normalized representation of a product.
	 *
	 * @param \WC_Product $product  Product.
	 * @param int         $quantity Quantity.
	 * @return array
	 */
	private function product_array( $product, $quantity = 1 ) {
		return array(
			'item_id'   => (string) $product->get_id(),
			'item_name' => $product->get_name(),
			'item_sku'  => $product->get_sku(),
			'price'     => $this->format_number( (float) $product->get_price() ),
			'quantity'  => (int) $quantity,
			'category'  => $this->get_product_category( $product ),
		);
	}

	/**
	 * First product category name.
	 *
	 * @param \WC_Product $product Product.
	 * @return string
	 */
	private function get_product_category( $product ) {
		$terms = wc_get_product_terms( $product->get_id(), 'product_cat', array( 'orderby' => 'parent' ) );
		return ! empty( $terms ) ? $terms[0]->name : '';
	}

	/**
	 * Format a price as a plain float string.
	 *
	 * @param float $number Number.
	 * @return float
	 */
	private function format_number( $number ) {
		return round( (float) $number, 2 );
	}
}