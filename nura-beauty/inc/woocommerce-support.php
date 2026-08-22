<?php
/**
 * WooCommerce integration: layout, badges, product loop tuning, cart fragments.
 *
 * @package NURA_Beauty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Products per page and columns tuned for a luxury 3-up shop grid.
add_filter( 'loop_shop_per_page', function () { return 12; }, 20 );
add_filter( 'loop_shop_columns', function () { return 3; }, 20 );

// Related products render 4-up (see the matching grid override in assets/css/woocommerce.css).
add_filter( 'woocommerce_related_products_args', function ( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;
	return $args;
} );

/**
 * Image clarity fixes (v1.7.0).
 *
 * The uploaded product masters are modest resolution, so WooCommerce's default
 * 600px "single" image was being upscaled into the ~590px gallery box and looked
 * soft until the hover-zoom swapped in the full file. Render the full-size file as
 * the main gallery image so what you see matches the zoom, and serve the larger
 * "single" size (instead of the 300px thumbnail) in product loops so the shop,
 * related and homepage grids stay crisp at 3- and 4-up.
 */
add_filter( 'woocommerce_gallery_image_size', function ( $size ) {
	return 'woocommerce_single' === $size ? 'full' : $size;
} );
add_filter( 'single_product_archive_thumbnail_size', function () {
	return 'woocommerce_single';
} );

// Variation selection should swap in the full-resolution image too (not the 600px single).
add_filter( 'woocommerce_available_variation', function ( $data, $product, $variation ) {
	$image_id = $variation->get_image_id();
	if ( $image_id ) {
		$full = wp_get_attachment_image_src( $image_id, 'full' );
		if ( $full ) {
			$data['image']['src']      = $full[0];
			$data['image']['src_w']    = $full[1];
			$data['image']['src_h']    = $full[2];
			$data['image']['full_src'] = $full[0];
			$srcset = wp_get_attachment_image_srcset( $image_id, 'full' );
			$sizes  = wp_get_attachment_image_sizes( $image_id, 'full' );
			if ( $srcset ) {
				$data['image']['srcset'] = $srcset;
			}
			if ( $sizes ) {
				$data['image']['sizes'] = $sizes;
			}
		}
	}
	return $data;
}, 10, 3 );

// Remove the default WooCommerce wrappers so we control markup.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

add_action( 'woocommerce_before_main_content', function () {
	echo '<main id="primary" class="site-main woo-main"><div class="nura-container">';
}, 10 );
add_action( 'woocommerce_after_main_content', function () {
	echo '</div></main>';
}, 10 );

// Full-width shop & product pages: remove the default WooCommerce sidebar
// (the leftover Search / Recent Posts / Recent Comments widget column).
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
add_filter( 'body_class', function ( $classes ) {
	if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
		$classes[] = 'nura-woo-fullwidth';
	}
	return $classes;
} );

// Add a sale / new badge on product cards.
add_action( 'woocommerce_before_shop_loop_item_title', 'nura_product_badges', 9 );
function nura_product_badges() {
	global $product;
	if ( ! $product ) {
		return;
	}
	echo '<div class="nura-badges">';
	if ( $product->is_on_sale() ) {
		echo '<span class="nura-badge nura-badge--sale">' . esc_html__( 'Sale', 'nura-beauty' ) . '</span>';
	}
	$created = strtotime( get_the_date( 'c', $product->get_id() ) );
	if ( $created && ( time() - $created ) < ( 30 * DAY_IN_SECONDS ) ) {
		echo '<span class="nura-badge nura-badge--new">' . esc_html__( 'New', 'nura-beauty' ) . '</span>';
	}
	echo '</div>';
}

/**
 * Sticky "Add to cart" bar data on single product (rendered by JS in main.js).
 */
add_action( 'woocommerce_after_single_product_summary', 'nura_sticky_atc', 5 );
function nura_sticky_atc() {
	global $product;
	if ( ! $product ) {
		return;
	}
	printf(
		'<div class="nura-sticky-atc" data-product="%1$d" aria-hidden="true"><span class="nura-sticky-atc__title">%2$s</span><span class="nura-sticky-atc__price">%3$s</span></div>',
		absint( $product->get_id() ),
		esc_html( $product->get_name() ),
		wp_kses_post( $product->get_price_html() )
	);
}

// Refresh the floating mini-cart count without a reload.
add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	ob_start();
	?>
	<span class="nura-cart-count" data-count="<?php echo esc_attr( $count ); ?>"><?php echo esc_html( $count ); ?></span>
	<?php
	$fragments['.nura-cart-count'] = ob_get_clean();
	return $fragments;
} );

/**
 * Trust strip under the add-to-cart button (KE-specific reassurance).
 */
add_action( 'woocommerce_single_product_summary', 'nura_trust_strip', 35 );
function nura_trust_strip() {
	$items = array(
		__( '100% human hair, quality guaranteed', 'nura-beauty' ),
		__( 'Same-day delivery in Nairobi', 'nura-beauty' ),
		__( 'Pay with M-Pesa, card or on delivery', 'nura-beauty' ),
		__( 'Free virtual wig consultation', 'nura-beauty' ),
	);
	echo '<ul class="nura-trust">';
	foreach ( $items as $item ) {
		echo '<li>' . esc_html( $item ) . '</li>';
	}
	echo '</ul>';
}
