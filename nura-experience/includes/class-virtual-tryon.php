<?php
/**
 * Virtual Try-On - manual visual preview.
 *
 * Lets a shopper upload their own photo and place the product image over it on a
 * canvas, then drag to position, scale and blend it. Everything runs in the
 * browser - the photo is never uploaded or stored. The overlay is the product's
 * own photo, so it shows as a rectangular image rather than a cut-out: this is a
 * quick visual preview to picture the look, NOT automated face-tracking or AR
 * fitting.
 *
 * An auto-aligned, face-landmark provider can be attached later via the
 * window.nuraxTryonProvider JS hook (the UI stays the same), but nothing tracks a
 * face by default.
 *
 * @package NURA_Experience
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NURAX_Virtual_TryOn {

	public function __construct() {
		add_shortcode( 'nura_virtual_tryon', array( $this, 'render' ) );
		// Try-On button on single product pages.
		add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'product_button' ) );
	}

	public function render( $atts ) {
		$atts       = shortcode_atts( array( 'product' => 0 ), $atts );
		$product_id = absint( $atts['product'] );
		// Allow the product to arrive from the "Try it on" button as ?tryon={id}.
		// We deliberately do NOT use ?product=: "product" is a reserved WooCommerce
		// query var, so /virtual-try-on/?product=ID makes WordPress resolve a product
		// by that slug, fail, and return a 404.
		if ( ! $product_id && isset( $_GET['tryon'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only lookup of a product id for display.
			$product_id = absint( wp_unslash( $_GET['tryon'] ) );
		}
		$overlay = '';
		if ( $product_id ) {
			$overlay = wp_get_attachment_image_url( get_post_thumbnail_id( $product_id ), 'nura-portrait' );
		}
		ob_start(); ?>
		<div class="nurax-tryon" data-nurax-tryon data-overlay="<?php echo esc_url( $overlay ); ?>">
			<div class="nurax-tryon__stage">
				<canvas data-nurax-canvas width="600" height="750"></canvas>
				<p class="nurax-tryon__hint"><?php esc_html_e( 'Upload a front-facing photo to begin', 'nura-experience' ); ?></p>
			</div>
			<div class="nurax-tryon__controls">
				<label class="nura-btn"><?php esc_html_e( 'Upload photo', 'nura-experience' ); ?><input type="file" accept="image/*" data-nurax-photo hidden></label>
				<label><?php esc_html_e( 'Size', 'nura-experience' ); ?> <input type="range" min="20" max="200" value="100" data-nurax-scale></label>
				<label><?php esc_html_e( 'Blend', 'nura-experience' ); ?> <input type="range" min="40" max="100" value="100" data-nurax-opacity></label>
				<button class="nura-btn nura-btn--ghost" data-nurax-reset><?php esc_html_e( 'Reset', 'nura-experience' ); ?></button>
			</div>
			<p><small><?php esc_html_e( 'A quick visual preview: drag, resize and blend the wig over your photo to picture the look. Your photo stays in your browser and is never uploaded or shared.', 'nura-experience' ); ?></small></p>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * "Try it on" button under add-to-cart, linking to the Virtual Try-On page with this product.
	 */
	public function product_button() {
		global $product;
		if ( ! $product ) {
			return;
		}
		$page = get_page_by_path( 'virtual-try-on' );
		if ( ! $page ) {
			return;
		}
		// Use a custom query var, not the reserved WooCommerce "product" var (which
		// would 404 the try-on page). render() reads ?tryon to preload the overlay.
		$url = add_query_arg( 'tryon', $product->get_id(), get_permalink( $page->ID ) );
		printf(
			'<a class="nura-btn nura-btn--ghost nurax-tryon-btn" href="%s">%s</a>',
			esc_url( $url ),
			esc_html__( 'Try it on', 'nura-experience' )
		);
	}
}
