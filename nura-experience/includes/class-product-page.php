<?php
/**
 * NURA Product Page.
 *
 * Upgrades the single-product page without overriding WooCommerce templates:
 *   - A clean attribute "spec strip" in the summary (Hair Type, Construction,
 *     Texture, Length, Density, Colour, Lace, Cap Size, Origin).
 *   - Structured content tabs: Hair details (+ what's included + length guide),
 *     Care & install, Shipping & returns, Warranty.
 *   - "Complete your NURA look" cross-sell row (cross-sells -> upsells -> Wig
 *     Care fallback).
 *   - Fixes the hardcoded "100% human hair" reassurance line so it reflects the
 *     product's real Hair Type attribute (no false claim on blends/synthetic).
 *
 * Native WooCommerce variations already update price/image/stock on selection,
 * so no custom variation JS is needed here.
 *
 * @package NURA_Experience
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NURAX_Product_Page {

	public function __construct() {
		// Attribute spec strip inside the summary (after excerpt, before add-to-cart).
		add_action( 'woocommerce_single_product_summary', array( $this, 'spec_strip' ), 25 );

		// Complete-the-look cross-sell row.
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'complete_look' ), 12 );

		// Structured content tabs.
		add_filter( 'woocommerce_product_tabs', array( $this, 'tabs' ) );

		// Replace the theme's hardcoded trust line with an attribute-aware one.
		add_action( 'wp', array( $this, 'retarget_trust' ) );
	}

	/** Map of attribute taxonomy => label, in display order. */
	private function attr_map() {
		return array(
			'pa_hair-type'    => __( 'Hair Type', 'nura-experience' ),
			'pa_construction' => __( 'Construction', 'nura-experience' ),
			'pa_texture'      => __( 'Texture', 'nura-experience' ),
			'pa_length'       => __( 'Length', 'nura-experience' ),
			'pa_density'      => __( 'Density', 'nura-experience' ),
			'pa_colour'       => __( 'Colour', 'nura-experience' ),
			'pa_lace'         => __( 'Lace Type', 'nura-experience' ),
			'pa_cap-size'     => __( 'Cap Size', 'nura-experience' ),
			'pa_origin'       => __( 'Hair Origin', 'nura-experience' ),
		);
	}

	/** Non-empty attribute rows for a product: [ label => value ]. */
	private function specs( $product ) {
		$rows = array();
		foreach ( $this->attr_map() as $tax => $label ) {
			$val = $product->get_attribute( $tax );
			if ( '' !== (string) $val ) {
				$rows[ $label ] = $val;
			}
		}
		return $rows;
	}

	/** Compact key-spec strip in the product summary. */
	public function spec_strip() {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}
		$rows = $this->specs( $product );
		if ( empty( $rows ) ) {
			return;
		}
		echo '<ul class="nura-specs">';
		foreach ( $rows as $label => $val ) {
			echo '<li><span class="nura-specs__k">' . esc_html( $label ) . '</span><span class="nura-specs__v">' . esc_html( $val ) . '</span></li>';
		}
		echo '</ul>';
	}

	/** Swap the theme trust strip for an attribute-aware version on product pages. */
	public function retarget_trust() {
		if ( function_exists( 'is_product' ) && is_product() ) {
			remove_action( 'woocommerce_single_product_summary', 'nura_trust_strip', 35 );
			add_action( 'woocommerce_single_product_summary', array( $this, 'trust_strip' ), 35 );
		}
	}

	/** Reassurance strip whose first line reflects the real Hair Type. */
	public function trust_strip() {
		global $product;
		$hair  = ( $product instanceof WC_Product ) ? $product->get_attribute( 'pa_hair-type' ) : '';
		$first = $hair
			? sprintf( /* translators: %s: hair type, e.g. 100% Human Hair */ __( '%s, quality guaranteed', 'nura-experience' ), $hair )
			: __( 'Premium quality, quality guaranteed', 'nura-experience' );

		$items = array(
			$first,
			__( 'Same-day delivery in Nairobi', 'nura-experience' ),
			__( 'Pay with M-Pesa, card or on delivery', 'nura-experience' ),
			__( 'Free virtual wig consultation', 'nura-experience' ),
		);
		echo '<ul class="nura-trust">';
		foreach ( $items as $item ) {
			echo '<li>' . esc_html( $item ) . '</li>';
		}
		echo '</ul>';
	}

	/** Add structured content tabs. */
	public function tabs( $tabs ) {
		$tabs['nura_hair'] = array(
			'title'    => __( 'Hair details', 'nura-experience' ),
			'priority' => 15,
			'callback' => array( $this, 'tab_hair' ),
		);
		$tabs['nura_care'] = array(
			'title'    => __( 'Care & install', 'nura-experience' ),
			'priority' => 25,
			'callback' => array( $this, 'tab_care' ),
		);
		$tabs['nura_shipping'] = array(
			'title'    => __( 'Shipping & returns', 'nura-experience' ),
			'priority' => 30,
			'callback' => array( $this, 'tab_shipping' ),
		);
		$tabs['nura_warranty'] = array(
			'title'    => __( 'Warranty', 'nura-experience' ),
			'priority' => 35,
			'callback' => array( $this, 'tab_warranty' ),
		);
		return $tabs;
	}

	public function tab_hair() {
		global $product;
		if ( $product instanceof WC_Product ) {
			$rows = $this->specs( $product );
			if ( $rows ) {
				echo '<table class="nura-spec-table"><tbody>';
				foreach ( $rows as $label => $val ) {
					echo '<tr><th>' . esc_html( $label ) . '</th><td>' . esc_html( $val ) . '</td></tr>';
				}
				echo '</tbody></table>';
			}
		}
		echo '<h4>' . esc_html__( "What's included", 'nura-experience' ) . '</h4>';
		echo '<ul class="nura-inc"><li>' . esc_html__( 'Your NURA unit', 'nura-experience' ) . '</li><li>' . esc_html__( 'Provenance note', 'nura-experience' ) . '</li><li>' . esc_html__( 'Written longevity guarantee', 'nura-experience' ) . '</li><li>' . esc_html__( 'Satin storage bag', 'nura-experience' ) . '</li></ul>';
		echo '<p class="nura-note">' . esc_html__( 'Length guide: lengths are measured straightened, tip to tip. Curly and wavy textures appear shorter once styled.', 'nura-experience' ) . '</p>';
	}

	public function tab_care() {
		echo '<h4>' . esc_html__( 'How to care for your NURA wig', 'nura-experience' ) . '</h4>';
		echo '<ul class="nura-inc">';
		echo '<li>' . esc_html__( 'Wash and revamp every 6-8 weeks with a sulfate-free shampoo and a moisturising conditioner.', 'nura-experience' ) . '</li>';
		echo '<li>' . esc_html__( 'Air-dry on a wig stand; avoid excessive heat and always use a heat protectant.', 'nura-experience' ) . '</li>';
		echo '<li>' . esc_html__( 'Store in the satin bag provided to keep the hair and lace fresh.', 'nura-experience' ) . '</li>';
		echo '</ul>';
		echo '<h4>' . esc_html__( 'How to install', 'nura-experience' ) . '</h4>';
		echo '<p>' . esc_html__( 'Glueless and wear-and-go units fit straight out of the box using the adjustable straps and combs. For lace units, book a NURA installation and our stylists will fit and style it for you.', 'nura-experience' ) . '</p>';
	}

	public function tab_shipping() {
		echo '<ul class="nura-inc">';
		echo '<li>' . esc_html__( 'Free same-day delivery in Nairobi on orders over KES 10,000.', 'nura-experience' ) . '</li>';
		echo '<li>' . esc_html__( 'Countrywide delivery in 1-3 business days.', 'nura-experience' ) . '</li>';
		echo '<li>' . esc_html__( 'International shipping available on request.', 'nura-experience' ) . '</li>';
		echo '<li>' . esc_html__( 'Pay with M-Pesa, card, or on delivery within Nairobi.', 'nura-experience' ) . '</li>';
		echo '<li>' . esc_html__( 'Unworn units in original condition may be returned within 7 days; custom units are made to order.', 'nura-experience' ) . '</li>';
		echo '</ul>';
	}

	public function tab_warranty() {
		echo '<p>' . esc_html__( 'Every NURA unit ships with a provenance note and a written longevity guarantee. Register your unit in The NURA Circle to store your warranty certificate, care schedule and revamp reminders.', 'nura-experience' ) . '</p>';
	}

	/** "Complete your NURA look" cross-sell / care row. */
	public function complete_look() {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}
		$ids = $product->get_cross_sell_ids();
		if ( empty( $ids ) ) {
			$ids = $product->get_upsell_ids();
		}
		if ( empty( $ids ) && function_exists( 'wc_get_products' ) ) {
			$ids = wc_get_products( array(
				'status'   => 'publish',
				'limit'    => 4,
				'category' => array( 'wig-care' ),
				'return'   => 'ids',
			) );
		}
		$ids = array_values( array_diff( array_map( 'absint', (array) $ids ), array( $product->get_id() ) ) );
		$ids = array_slice( $ids, 0, 4 );
		if ( empty( $ids ) ) {
			return;
		}
		echo '<section class="nura-complete"><div class="nura-container">';
		echo '<div class="nura-shead"><p class="nura-eyebrow">' . esc_html__( 'Finish the ritual', 'nura-experience' ) . '</p><h2>' . esc_html__( 'Complete your NURA look', 'nura-experience' ) . '</h2></div>';
		echo do_shortcode( '[products ids="' . esc_attr( implode( ',', $ids ) ) . '" columns="4" limit="4" class="nura-complete-products"]' );
		echo '</div></section>';
	}
}
