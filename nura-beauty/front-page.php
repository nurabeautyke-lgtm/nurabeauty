<?php
/**
 * Homepage - NURA "focused luxury" v1.6.0.
 *
 * Deliberately lean. Sections, in order: hero, trust bar, Shop NURA (4 pillars),
 * Fresh Arrivals, Real NURA Women (social proof), House Favourites, brand story,
 * featured Wig Care, the NURA Experience (services summary) and the NURA Stylist
 * CTA. The full catalogue architecture - every texture, construction, price band,
 * care line and service - lives BEHIND the homepage in Shop / Category / Filter
 * pages, not on it. Category tiles resolve to their archive when it exists and
 * fall back to the shop page otherwise, so no link is ever broken.
 *
 * @package NURA_Beauty
 */
get_header();

$shop_url   = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/shop/' );
$wa         = nura_opt( 'nura_whatsapp' );
$finder_url = home_url( '/ai-wig-finder/' );

/** Resolve a product-category URL by slug, with a safe shop fallback. */
if ( ! function_exists( 'nura_home_cat_url' ) ) {
	function nura_home_cat_url( $slug ) {
		if ( taxonomy_exists( 'product_cat' ) ) {
			$t = get_term_by( 'slug', sanitize_title( $slug ), 'product_cat' );
			if ( $t && ! is_wp_error( $t ) ) {
				$l = get_term_link( $t );
				if ( ! is_wp_error( $l ) ) {
					return $l;
				}
			}
		}
		return function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/shop/' );
	}
}
/** Category thumbnail URL by slug, or '' when none is set. */
if ( ! function_exists( 'nura_home_cat_img' ) ) {
	function nura_home_cat_img( $slug ) {
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			return '';
		}
		$t = get_term_by( 'slug', sanitize_title( $slug ), 'product_cat' );
		if ( $t && ! is_wp_error( $t ) ) {
			$id = get_term_meta( $t->term_id, 'thumbnail_id', true );
			if ( $id ) {
				return wp_get_attachment_image_url( $id, 'medium_large' );
			}
		}
		return '';
	}
}
/** Render a grid of tiles: each item = [ label, url, (sub), (img) ]. */
if ( ! function_exists( 'nura_home_tiles' ) ) {
	function nura_home_tiles( $items, $mod = 'nura-tiles--4' ) {
		echo '<div class="nura-tiles ' . esc_attr( $mod ) . '">';
		foreach ( $items as $it ) {
			$img   = ! empty( $it['img'] ) ? $it['img'] : '';
			$style = $img ? ' style="background-image:url(\'' . esc_url( $img ) . '\')"' : '';
			$cls   = 'nura-tile' . ( $img ? ' has-img' : '' );
			echo '<a class="' . esc_attr( $cls ) . '"' . $style . ' href="' . esc_url( $it['url'] ) . '">';
			echo '<span class="nura-tile__label">' . esc_html( $it['label'] );
			if ( ! empty( $it['sub'] ) ) {
				echo '<small>' . esc_html( $it['sub'] ) . '</small>';
			}
			echo '</span></a>';
		}
		echo '</div>';
	}
}

$wigs_url = nura_home_cat_url( 'wigs' );

/*
 * Curated hero. Copy is set here (not the Customizer) so the homepage's primary
 * message stays consistent and on-brand. Every slide drives the same two clear
 * actions - Shop Wigs and Find Your Wig - so the hero stays single-minded.
 */
$slides = array(
	array(
		'img'     => NURA_URI . 'assets/images/hero.jpg',
		'eyebrow' => __( 'Premium Wigs • Beauty • Care • Confidence', 'nura-beauty' ),
		'title'   => __( 'Wear Your Crown', 'nura-beauty' ),
		'sub'     => __( 'Discover human-hair wigs and beauty essentials curated for the modern woman.', 'nura-beauty' ),
	),
	array(
		'img'     => NURA_URI . 'assets/images/hero-2.jpg',
		'eyebrow' => __( 'Bridal & Occasion', 'nura-beauty' ),
		'title'   => __( 'Your Crown for the Big Day', 'nura-beauty' ),
		'sub'     => __( 'Bespoke bridal units, hand-finished for a flawless, photograph-ready finish.', 'nura-beauty' ),
	),
	array(
		'img'     => NURA_URI . 'assets/images/hero-3.jpg',
		'eyebrow' => __( 'The Confidence Line', 'nura-beauty' ),
		'title'   => __( 'Statement HD-Lace Units', 'nura-beauty' ),
		'sub'     => __( 'An invisible hairline and undeniable presence, hand-crafted in Nairobi.', 'nura-beauty' ),
	),
);
?>
<main id="primary" class="site-main">

	<!-- HERO SLIDER -->
	<section class="nura-hero-slider" data-nura-slider>
		<div class="nura-slides">
			<?php foreach ( $slides as $i => $s ) : ?>
				<div class="nura-slide<?php echo 0 === $i ? ' is-active' : ''; ?>">
					<div class="nura-slide__media" style="background-image:url('<?php echo esc_url( $s['img'] ); ?>')" role="img" aria-label="<?php echo esc_attr( $s['title'] ); ?>"></div>
					<div class="nura-container nura-slide__inner">
						<p class="nura-eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p>
						<h1><?php echo esc_html( $s['title'] ); ?></h1>
						<p class="nura-lede"><?php echo esc_html( $s['sub'] ); ?></p>
						<div class="nura-hero__cta">
							<a class="nura-btn nura-btn--gold" href="<?php echo esc_url( $wigs_url ); ?>"><?php esc_html_e( 'Shop Wigs', 'nura-beauty' ); ?></a>
							<a class="nura-btn nura-btn--ghost" href="<?php echo esc_url( $finder_url ); ?>"><?php esc_html_e( 'Find Your Wig', 'nura-beauty' ); ?></a>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="nura-slider-nav nura-slider-nav--prev" data-slider-prev aria-label="<?php esc_attr_e( 'Previous slide', 'nura-beauty' ); ?>">&#8249;</button>
		<button type="button" class="nura-slider-nav nura-slider-nav--next" data-slider-next aria-label="<?php esc_attr_e( 'Next slide', 'nura-beauty' ); ?>">&#8250;</button>
		<div class="nura-slider-dots" data-slider-dots></div>
	</section>

	<!-- TRUST BAR -->
	<div class="nura-trustbar">
		<div class="nura-container">
			<ul>
				<li><?php esc_html_e( 'Verified Hair', 'nura-beauty' ); ?></li>
				<li><?php esc_html_e( 'Same-Day Nairobi', 'nura-beauty' ); ?></li>
				<li><?php esc_html_e( 'Secure Payments', 'nura-beauty' ); ?></li>
				<li><?php esc_html_e( 'NURA Guarantee', 'nura-beauty' ); ?></li>
			</ul>
		</div>
	</div>

	<!-- SHOP NURA - four pillars -->
	<section class="section" id="shop-nura">
		<div class="nura-container">
			<div class="nura-shead nura-reveal">
				<p class="nura-eyebrow"><?php esc_html_e( 'Shop NURA', 'nura-beauty' ); ?></p>
				<h2><?php esc_html_e( 'Everything for your crown', 'nura-beauty' ); ?></h2>
				<p><?php esc_html_e( 'Wigs, hair, care and beauty - the complete NURA house.', 'nura-beauty' ); ?></p>
			</div>
			<?php
			nura_home_tiles(
				array(
					array( 'label' => __( 'Wigs', 'nura-beauty' ),              'sub' => __( 'Human Hair • Lace • Glueless', 'nura-beauty' ), 'url' => nura_home_cat_url( 'wigs' ),            'img' => nura_home_cat_img( 'wigs' ) ),
					array( 'label' => __( 'Hair & Extensions', 'nura-beauty' ), 'sub' => __( 'Bundles • Closures • Frontals', 'nura-beauty' ), 'url' => nura_home_cat_url( 'hair-extensions' ), 'img' => nura_home_cat_img( 'hair-extensions' ) ),
					array( 'label' => __( 'Wig Care', 'nura-beauty' ),          'sub' => __( 'Wash • Treat • Maintain', 'nura-beauty' ),     'url' => nura_home_cat_url( 'wig-care' ),        'img' => nura_home_cat_img( 'wig-care' ) ),
					array( 'label' => __( 'Beauty', 'nura-beauty' ),            'sub' => __( 'Makeup • Accessories', 'nura-beauty' ),           'url' => nura_home_cat_url( 'beauty' ),          'img' => nura_home_cat_img( 'beauty' ) ),
				),
				'nura-tiles--4'
			);
			?>
		</div>
	</section>

	<!-- RAIL: FRESH ARRIVALS (4 products) -->
	<?php if ( class_exists( 'WooCommerce' ) ) : ?>
	<?php nura_rail( __( 'New in', 'nura-beauty' ), __( 'Fresh Arrivals', 'nura-beauty' ), '[products limit="4" columns="4" orderby="date" order="DESC" visibility="visible" class="nura-rail-products"]', $shop_url, __( 'View all new arrivals', 'nura-beauty' ) ); ?>
	<?php endif; ?>

	<!-- REAL NURA WOMEN (social proof, moved high) -->
	<?php $ig = nura_opt( 'nura_instagram' ); $ig_url = $ig ? $ig : $wa; ?>
	<section class="section section--ink text-center" id="real-women">
		<div class="nura-container nura-reveal">
			<p class="nura-eyebrow">@nurabeauty</p>
			<h2><?php esc_html_e( 'Real women. Real crowns.', 'nura-beauty' ); ?></h2>
			<p class="nura-lede" style="max-width:640px;margin-inline:auto"><?php esc_html_e( 'Tag @nurabeauty in your crown to be featured here - real women, real hair, real confidence, across Kenya and beyond.', 'nura-beauty' ); ?></p>
			<p style="margin-top:1.4rem">
				<a class="nura-btn nura-btn--gold" href="<?php echo esc_url( $ig_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'See the NURA community', 'nura-beauty' ); ?> &#8594;</a>
			</p>
		</div>
	</section>

	<!-- RAIL: HOUSE FAVOURITES (4 products) -->
	<?php if ( class_exists( 'WooCommerce' ) ) : ?>
	<?php nura_rail( __( 'Most loved', 'nura-beauty' ), __( 'House Favourites', 'nura-beauty' ), '[products limit="4" columns="4" orderby="popularity" class="nura-rail-products"]', $shop_url, __( 'View all', 'nura-beauty' ) ); ?>
	<?php endif; ?>

	<!-- BRAND STORY (editorial split, condensed) -->
	<section class="section section--ivory">
		<div class="nura-container nura-split nura-reveal">
			<div class="nura-split__media">
				<img src="<?php echo esc_url( NURA_URI . 'assets/images/model-editorial.jpg' ); ?>" alt="<?php echo esc_attr( nura_opt( 'nura_brand_name' ) ); ?>" loading="lazy">
			</div>
			<div class="nura-split__body">
				<p class="nura-eyebrow"><?php esc_html_e( 'The House of Radiant Confidence', 'nura-beauty' ); ?></p>
				<h2><?php esc_html_e( 'Hand-crafted in Nairobi. Made for you.', 'nura-beauty' ); ?></h2>
				<p class="nura-lede"><?php esc_html_e( 'Premium wigs, personalised service and a commitment to helping every woman feel confident in her crown.', 'nura-beauty' ); ?></p>
				<a class="nura-btn nura-btn--ghost" href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>"><?php esc_html_e( 'Discover NURA', 'nura-beauty' ); ?> &#8594;</a>
			</div>
		</div>
	</section>

	<!-- WIG CARE (featured: 3 products + CTA) -->
	<section class="section" id="wig-care">
		<div class="nura-container">
			<div class="nura-shead nura-shead--row nura-reveal">
				<div>
					<p class="nura-eyebrow"><?php esc_html_e( 'Keep your crown radiant', 'nura-beauty' ); ?></p>
					<h2><?php esc_html_e( 'NURA Wig Care', 'nura-beauty' ); ?></h2>
					<p style="margin:.4rem 0 0"><?php esc_html_e( 'Everything your wig needs to stay beautiful.', 'nura-beauty' ); ?></p>
				</div>
				<a class="nura-rail__viewall" href="<?php echo esc_url( nura_home_cat_url( 'wig-care' ) ); ?>"><?php esc_html_e( 'Shop wig care', 'nura-beauty' ); ?> &#8594;</a>
			</div>
			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
			<div class="nura-care-featured nura-reveal">
				<?php echo do_shortcode( '[products limit="3" columns="3" category="wig-care" orderby="popularity" visibility="visible"]' ); ?>
			</div>
			<?php endif; ?>
		</div>
	</section>

	<!-- THE NURA EXPERIENCE (services summary + single CTA) -->
	<section class="section section--ink text-center" id="services">
		<div class="nura-container nura-reveal">
			<p class="nura-eyebrow"><?php esc_html_e( 'More than a wig', 'nura-beauty' ); ?></p>
			<h2><?php esc_html_e( 'The NURA Experience', 'nura-beauty' ); ?></h2>
			<p class="nura-lede" style="max-width:620px;margin-inline:auto"><?php esc_html_e( 'A complete beauty experience, handled end to end by our team.', 'nura-beauty' ); ?></p>
			<p style="margin:.8rem 0 0;letter-spacing:.16em;text-transform:uppercase;font-size:.8rem;color:var(--nura-gold-soft)"><?php esc_html_e( 'Installation • Revamp • Styling • Consultation', 'nura-beauty' ); ?></p>
			<p style="margin-top:1.6rem">
				<a class="nura-btn nura-btn--gold" href="<?php echo esc_url( home_url( '/services/' ) ); ?>"><?php esc_html_e( 'Explore NURA Services', 'nura-beauty' ); ?> &#8594;</a>
			</p>
		</div>
	</section>

	<!-- NURA STYLIST (final conversion CTA) -->
	<section class="section text-center">
		<div class="nura-container nura-reveal">
			<p class="nura-eyebrow"><?php esc_html_e( 'Not sure what suits you?', 'nura-beauty' ); ?></p>
			<h2><?php esc_html_e( 'Meet NURA Stylist', 'nura-beauty' ); ?></h2>
			<p class="nura-lede" style="max-width:620px;margin-inline:auto"><?php esc_html_e( "Tell us what you're looking for and we'll help you find your perfect wig.", 'nura-beauty' ); ?></p>
			<p style="margin-top:1.4rem">
				<a class="nura-btn nura-btn--gold" href="<?php echo esc_url( $finder_url ); ?>"><?php esc_html_e( 'Find My Wig', 'nura-beauty' ); ?></a>
				<a class="nura-btn nura-btn--ghost" href="<?php echo esc_url( $wa ); ?>"><?php esc_html_e( 'Chat on WhatsApp', 'nura-beauty' ); ?></a>
			</p>
		</div>
	</section>

</main>
<?php get_footer();
