<?php
/**
 * Homepage - NURA "layered discovery" v1.4.0.
 *
 * Sections: hero slider, trust bar, Shop NURA category tiles, Find Your Perfect
 * Wig, Shop by Texture / Construction / Budget, product rails, editorial story,
 * Wig Care, Beauty, Services, reviews, NURA Journal, Real NURA Women,
 * book-a-fitting and the exclusive features. All copy/images pull from the
 * Customizer (nura_opt) and live WooCommerce data. Category tiles resolve to
 * their archive when it exists and fall back to the shop page otherwise, so no
 * link is ever broken before the catalogue is populated.
 *
 * @package NURA_Beauty
 */
get_header();

$shop_url = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/shop/' );
$wa       = nura_opt( 'nura_whatsapp' );

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

$slides = array(
	array(
		'img'      => ( nura_opt( 'nura_hero_image' ) ? nura_opt( 'nura_hero_image' ) : NURA_URI . 'assets/images/hero.jpg' ),
		'eyebrow'  => ( nura_opt( 'nura_hero_eyebrow' ) ? nura_opt( 'nura_hero_eyebrow' ) : __( 'The House of Radiant Confidence', 'nura-beauty' ) ),
		'title'    => ( nura_opt( 'nura_hero_title' ) ? nura_opt( 'nura_hero_title' ) : __( 'Luxury Human Hair Wigs, Made for You', 'nura-beauty' ) ),
		'sub'      => ( nura_opt( 'nura_hero_subtitle' ) ? nura_opt( 'nura_hero_subtitle' ) : __( 'Verified human hair, HD lace and glueless units, hand-crafted in Nairobi and delivered across Kenya.', 'nura-beauty' ) ),
		'cta'      => ( nura_opt( 'nura_hero_cta' ) ? nura_opt( 'nura_hero_cta' ) : __( 'Shop the Collection', 'nura-beauty' ) ),
		'cta_url'  => $shop_url,
		'cta2'     => ( nura_opt( 'nura_hero_cta2' ) ? nura_opt( 'nura_hero_cta2' ) : __( 'Book a Consultation', 'nura-beauty' ) ),
		'cta2_url' => home_url( '/book-appointment/' ),
	),
	array(
		'img'      => NURA_URI . 'assets/images/hero-2.jpg',
		'eyebrow'  => __( 'Bridal & Occasion', 'nura-beauty' ),
		'title'    => __( 'Your Crown for the Big Day', 'nura-beauty' ),
		'sub'      => __( 'Bespoke bridal units, hand-finished and fitted for a flawless, photograph-ready finish.', 'nura-beauty' ),
		'cta'      => __( 'Explore Bridal', 'nura-beauty' ),
		'cta_url'  => $shop_url,
		'cta2'     => __( 'Book a Fitting', 'nura-beauty' ),
		'cta2_url' => home_url( '/book-appointment/' ),
	),
	array(
		'img'      => NURA_URI . 'assets/images/hero-3.jpg',
		'eyebrow'  => __( 'The Confidence Line', 'nura-beauty' ),
		'title'    => __( 'Wear Your Crown', 'nura-beauty' ),
		'sub'      => __( 'Statement HD-lace units with an invisible hairline and undeniable presence.', 'nura-beauty' ),
		'cta'      => __( 'Shop New In', 'nura-beauty' ),
		'cta_url'  => $shop_url,
		'cta2'     => __( 'Try it On', 'nura-beauty' ),
		'cta2_url' => home_url( '/virtual-try-on/' ),
	),
);

$reviews = function_exists( 'nura_recent_reviews' ) ? nura_recent_reviews( 3 ) : array();
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
							<a class="nura-btn nura-btn--gold" href="<?php echo esc_url( $s['cta_url'] ); ?>"><?php echo esc_html( $s['cta'] ); ?></a>
							<a class="nura-btn nura-btn--ghost" href="<?php echo esc_url( $s['cta2_url'] ); ?>"><?php echo esc_html( $s['cta2'] ); ?></a>
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
				<li><?php echo esc_html( nura_opt( 'nura_trust_1' ) ); ?></li>
				<li><?php echo esc_html( nura_opt( 'nura_trust_2' ) ); ?></li>
				<li><?php echo esc_html( nura_opt( 'nura_trust_3' ) ); ?></li>
				<li><?php echo esc_html( nura_opt( 'nura_trust_4' ) ); ?></li>
			</ul>
		</div>
	</div>

	<!-- SHOP NURA - six category tiles -->
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
					array( 'label' => __( 'Wigs', 'nura-beauty' ),               'sub' => __( 'Human hair & lace', 'nura-beauty' ), 'url' => nura_home_cat_url( 'wigs' ),            'img' => nura_home_cat_img( 'wigs' ) ),
					array( 'label' => __( 'Human Hair', 'nura-beauty' ),         'sub' => __( '100% human hair', 'nura-beauty' ),   'url' => nura_home_cat_url( 'human-hair-wigs' ), 'img' => nura_home_cat_img( 'human-hair-wigs' ) ),
					array( 'label' => __( 'Hair & Extensions', 'nura-beauty' ),  'sub' => __( 'Bundles, closures', 'nura-beauty' ), 'url' => nura_home_cat_url( 'hair-extensions' ), 'img' => nura_home_cat_img( 'hair-extensions' ) ),
					array( 'label' => __( 'Wig Care', 'nura-beauty' ),           'sub' => __( 'Wash, style, revamp', 'nura-beauty' ), 'url' => nura_home_cat_url( 'wig-care' ),      'img' => nura_home_cat_img( 'wig-care' ) ),
					array( 'label' => __( 'Makeup', 'nura-beauty' ),             'sub' => __( 'Face, eyes, lips', 'nura-beauty' ),  'url' => nura_home_cat_url( 'beauty' ),          'img' => nura_home_cat_img( 'beauty' ) ),
					array( 'label' => __( 'Beauty Accessories', 'nura-beauty' ), 'sub' => __( 'Caps, stands, tools', 'nura-beauty' ), 'url' => nura_home_cat_url( 'accessories' ),   'img' => nura_home_cat_img( 'accessories' ) ),
				),
				'nura-tiles--3'
			);
			?>
		</div>
	</section>

	<!-- FIND YOUR PERFECT WIG -->
	<section class="section section--ivory" id="find-your-wig">
		<div class="nura-container">
			<div class="nura-shead nura-reveal">
				<p class="nura-eyebrow"><?php esc_html_e( 'Find your perfect wig', 'nura-beauty' ); ?></p>
				<h2><?php esc_html_e( 'Where would you like to start?', 'nura-beauty' ); ?></h2>
			</div>
			<?php
			nura_home_tiles(
				array(
					array( 'label' => __( 'I know what I want', 'nura-beauty' ), 'sub' => __( 'Browse all wigs', 'nura-beauty' ),      'url' => nura_home_cat_url( 'wigs' ) ),
					array( 'label' => __( 'Shop by texture', 'nura-beauty' ),    'sub' => __( 'Straight, wave, curls', 'nura-beauty' ), 'url' => '#shop-texture' ),
					array( 'label' => __( 'Shop by budget', 'nura-beauty' ),     'sub' => __( 'Find your price', 'nura-beauty' ),      'url' => '#shop-budget' ),
					array( 'label' => __( "I'm not sure", 'nura-beauty' ),       'sub' => __( 'Use the AI Wig Finder', 'nura-beauty' ), 'url' => home_url( '/ai-wig-finder/' ) ),
				),
				'nura-tiles--4 nura-tiles--paths'
			);
			?>
		</div>
	</section>

	<!-- RAIL: FRESH ARRIVALS -->
	<?php if ( class_exists( 'WooCommerce' ) ) : ?>
	<?php nura_rail( __( 'New in', 'nura-beauty' ), __( 'Fresh arrivals', 'nura-beauty' ), '[products limit="12" columns="12" orderby="date" order="DESC" visibility="visible" class="nura-rail-products"]', $shop_url ); ?>
	<?php endif; ?>

	<!-- SHOP BY TEXTURE -->
	<section class="section" id="shop-texture">
		<div class="nura-container">
			<div class="nura-shead nura-reveal">
				<p class="nura-eyebrow"><?php esc_html_e( 'By texture', 'nura-beauty' ); ?></p>
				<h2><?php esc_html_e( 'Shop by texture', 'nura-beauty' ); ?></h2>
			</div>
			<?php
			nura_home_tiles(
				array(
					array( 'label' => __( 'Straight', 'nura-beauty' ),   'url' => nura_home_cat_url( 'straight-wigs' ),   'img' => nura_home_cat_img( 'straight-wigs' ) ),
					array( 'label' => __( 'Body Wave', 'nura-beauty' ),  'url' => nura_home_cat_url( 'body-wave-wigs' ),  'img' => nura_home_cat_img( 'body-wave-wigs' ) ),
					array( 'label' => __( 'Loose Wave', 'nura-beauty' ), 'url' => nura_home_cat_url( 'loose-wave-wigs' ), 'img' => nura_home_cat_img( 'loose-wave-wigs' ) ),
					array( 'label' => __( 'Water Wave', 'nura-beauty' ), 'url' => nura_home_cat_url( 'water-wave-wigs' ), 'img' => nura_home_cat_img( 'water-wave-wigs' ) ),
					array( 'label' => __( 'Deep Wave', 'nura-beauty' ),  'url' => nura_home_cat_url( 'deep-wave-wigs' ),  'img' => nura_home_cat_img( 'deep-wave-wigs' ) ),
					array( 'label' => __( 'Curly', 'nura-beauty' ),      'url' => nura_home_cat_url( 'curly-wigs' ),      'img' => nura_home_cat_img( 'curly-wigs' ) ),
					array( 'label' => __( 'Kinky', 'nura-beauty' ),      'url' => nura_home_cat_url( 'kinky-wigs' ),      'img' => nura_home_cat_img( 'kinky-wigs' ) ),
					array( 'label' => __( 'Afro', 'nura-beauty' ),       'url' => nura_home_cat_url( 'afro-wigs' ),       'img' => nura_home_cat_img( 'afro-wigs' ) ),
				),
				'nura-tiles--4'
			);
			?>
		</div>
	</section>

	<!-- SHOP BY CONSTRUCTION -->
	<section class="section section--ivory" id="shop-construction">
		<div class="nura-container">
			<div class="nura-shead nura-reveal">
				<p class="nura-eyebrow"><?php esc_html_e( 'By construction', 'nura-beauty' ); ?></p>
				<h2><?php esc_html_e( 'Shop by construction', 'nura-beauty' ); ?></h2>
			</div>
			<?php
			nura_home_tiles(
				array(
					array( 'label' => __( 'HD Lace', 'nura-beauty' ),    'url' => nura_home_cat_url( 'hd-lace-wigs' ),    'img' => nura_home_cat_img( 'hd-lace-wigs' ) ),
					array( 'label' => __( 'Lace Front', 'nura-beauty' ), 'url' => nura_home_cat_url( 'lace-front-wigs' ), 'img' => nura_home_cat_img( 'lace-front-wigs' ) ),
					array( 'label' => __( 'Glueless', 'nura-beauty' ),   'url' => nura_home_cat_url( 'glueless-wigs' ),   'img' => nura_home_cat_img( 'glueless-wigs' ) ),
					array( 'label' => __( 'Closure', 'nura-beauty' ),    'url' => nura_home_cat_url( 'closure-wigs' ),    'img' => nura_home_cat_img( 'closure-wigs' ) ),
					array( 'label' => __( '360 Lace', 'nura-beauty' ),   'url' => nura_home_cat_url( '360-lace-wigs' ),   'img' => nura_home_cat_img( '360-lace-wigs' ) ),
					array( 'label' => __( 'Headband', 'nura-beauty' ),   'url' => nura_home_cat_url( 'headband-wigs' ),   'img' => nura_home_cat_img( 'headband-wigs' ) ),
					array( 'label' => __( 'Full Lace', 'nura-beauty' ),  'url' => nura_home_cat_url( 'full-lace-wigs' ),  'img' => nura_home_cat_img( 'full-lace-wigs' ) ),
					array( 'label' => __( 'Bridal', 'nura-beauty' ),     'url' => nura_home_cat_url( 'bridal-wigs' ),     'img' => nura_home_cat_img( 'bridal-wigs' ) ),
				),
				'nura-tiles--4'
			);
			?>
		</div>
	</section>

	<!-- SHOP BY BUDGET -->
	<section class="section" id="shop-budget">
		<div class="nura-container">
			<div class="nura-shead nura-reveal">
				<p class="nura-eyebrow"><?php esc_html_e( 'By budget', 'nura-beauty' ); ?></p>
				<h2><?php esc_html_e( 'Shop by price', 'nura-beauty' ); ?></h2>
			</div>
			<?php
			nura_home_tiles(
				array(
					array( 'label' => __( 'Under KES 2,000', 'nura-beauty' ),    'url' => add_query_arg( 'max_price', 2000, $shop_url ) ),
					array( 'label' => __( 'Under KES 5,000', 'nura-beauty' ),    'url' => add_query_arg( 'max_price', 5000, $shop_url ) ),
					array( 'label' => __( 'KES 5,000 - 10,000', 'nura-beauty' ), 'url' => add_query_arg( array( 'min_price' => 5000, 'max_price' => 10000 ), $shop_url ) ),
					array( 'label' => __( 'KES 10,000 - 20,000', 'nura-beauty' ),'url' => add_query_arg( array( 'min_price' => 10000, 'max_price' => 20000 ), $shop_url ) ),
					array( 'label' => __( 'KES 20,000 - 50,000', 'nura-beauty' ),'url' => add_query_arg( array( 'min_price' => 20000, 'max_price' => 50000 ), $shop_url ) ),
					array( 'label' => __( 'KES 50,000+', 'nura-beauty' ),        'url' => add_query_arg( 'min_price', 50000, $shop_url ) ),
				),
				'nura-tiles--3 nura-tiles--budget'
			);
			?>
		</div>
	</section>

	<!-- EDITORIAL STORY -->
	<section class="section section--ivory">
		<div class="nura-container nura-split nura-reveal">
			<div class="nura-split__media">
				<img src="<?php echo esc_url( NURA_URI . 'assets/images/model-editorial.jpg' ); ?>" alt="<?php echo esc_attr( nura_opt( 'nura_brand_name' ) ); ?>" loading="lazy">
			</div>
			<div class="nura-split__body">
				<p class="nura-eyebrow"><?php echo esc_html( nura_opt( 'nura_tagline' ) ); ?></p>
				<h2><?php esc_html_e( 'Hand-crafted in Nairobi, made for you', 'nura-beauty' ); ?></h2>
				<p class="nura-lede"><?php echo esc_html( nura_opt( 'nura_bio' ) ); ?></p>
				<p><?php esc_html_e( 'Every NURA unit is sewn from verified human hair and finished by hand. Each order ships with a provenance note and a written longevity guarantee, wrapped in our signature black-and-gold ritual.', 'nura-beauty' ); ?></p>
				<a class="nura-btn nura-btn--ghost" href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>"><?php esc_html_e( 'Our Story', 'nura-beauty' ); ?></a>
			</div>
		</div>
	</section>

	<!-- RAIL: MOST LOVED -->
	<?php if ( class_exists( 'WooCommerce' ) ) : ?>
	<?php nura_rail( __( 'Most loved', 'nura-beauty' ), __( 'The house favourites', 'nura-beauty' ), '[products limit="12" columns="12" orderby="popularity" class="nura-rail-products"]', $shop_url ); ?>
	<?php endif; ?>

	<!-- WIG CARE -->
	<section class="section" id="wig-care">
		<div class="nura-container">
			<div class="nura-shead nura-shead--row nura-reveal">
				<div>
					<p class="nura-eyebrow"><?php esc_html_e( 'Keep your crown radiant', 'nura-beauty' ); ?></p>
					<h2><?php esc_html_e( 'NURA Wig Care', 'nura-beauty' ); ?></h2>
				</div>
				<a class="nura-rail__viewall" href="<?php echo esc_url( nura_home_cat_url( 'wig-care' ) ); ?>"><?php esc_html_e( 'Shop wig care', 'nura-beauty' ); ?> &#8594;</a>
			</div>
			<?php
			nura_home_tiles(
				array(
					array( 'label' => __( 'Shampoo', 'nura-beauty' ),          'url' => nura_home_cat_url( 'shampoo' ) ),
					array( 'label' => __( 'Conditioner', 'nura-beauty' ),      'url' => nura_home_cat_url( 'conditioner' ) ),
					array( 'label' => __( 'Treatments', 'nura-beauty' ),       'url' => nura_home_cat_url( 'treatments' ) ),
					array( 'label' => __( 'Styling', 'nura-beauty' ),          'url' => nura_home_cat_url( 'styling-products' ) ),
					array( 'label' => __( 'Maintenance', 'nura-beauty' ),      'url' => nura_home_cat_url( 'maintenance' ) ),
					array( 'label' => __( 'Wig Care Kits', 'nura-beauty' ),    'url' => nura_home_cat_url( 'wig-care-kits' ) ),
				),
				'nura-tiles--3 nura-tiles--care'
			);
			?>
		</div>
	</section>

	<!-- BEAUTY -->
	<section class="section section--ivory" id="beauty">
		<div class="nura-container">
			<div class="nura-shead nura-shead--row nura-reveal">
				<div>
					<p class="nura-eyebrow"><?php esc_html_e( 'More than wigs', 'nura-beauty' ); ?></p>
					<h2><?php esc_html_e( 'NURA Beauty', 'nura-beauty' ); ?></h2>
				</div>
				<a class="nura-rail__viewall" href="<?php echo esc_url( nura_home_cat_url( 'beauty' ) ); ?>"><?php esc_html_e( 'Shop beauty', 'nura-beauty' ); ?> &#8594;</a>
			</div>
			<?php
			nura_home_tiles(
				array(
					array( 'label' => __( 'Face', 'nura-beauty' ),        'url' => nura_home_cat_url( 'face' ) ),
					array( 'label' => __( 'Eyes', 'nura-beauty' ),        'url' => nura_home_cat_url( 'eyes' ) ),
					array( 'label' => __( 'Lips', 'nura-beauty' ),        'url' => nura_home_cat_url( 'lips' ) ),
					array( 'label' => __( 'Cheeks', 'nura-beauty' ),      'url' => nura_home_cat_url( 'cheeks' ) ),
					array( 'label' => __( 'Makeup Tools', 'nura-beauty' ),'url' => nura_home_cat_url( 'makeup-tools' ) ),
					array( 'label' => __( 'Sets', 'nura-beauty' ),        'url' => nura_home_cat_url( 'sets' ) ),
				),
				'nura-tiles--3 nura-tiles--care'
			);
			?>
		</div>
	</section>

	<!-- SERVICES -->
	<section class="section" id="services">
		<div class="nura-container">
			<div class="nura-shead nura-reveal">
				<p class="nura-eyebrow"><?php esc_html_e( 'The full NURA experience', 'nura-beauty' ); ?></p>
				<h2><?php esc_html_e( 'NURA Services', 'nura-beauty' ); ?></h2>
			</div>
			<?php
			nura_home_tiles(
				array(
					array( 'label' => __( 'Installation', 'nura-beauty' ),     'url' => nura_home_cat_url( 'installation' ) ),
					array( 'label' => __( 'Wig Revamp', 'nura-beauty' ),       'url' => nura_home_cat_url( 'wig-revamp' ) ),
					array( 'label' => __( 'Wig Repair', 'nura-beauty' ),       'url' => nura_home_cat_url( 'wig-repair' ) ),
					array( 'label' => __( 'Colouring', 'nura-beauty' ),        'url' => nura_home_cat_url( 'wig-colouring' ) ),
					array( 'label' => __( 'Restyling', 'nura-beauty' ),        'url' => nura_home_cat_url( 'restyling' ) ),
					array( 'label' => __( 'Consultation', 'nura-beauty' ),     'url' => nura_home_cat_url( 'consultation' ) ),
					array( 'label' => __( 'Custom Wig Making', 'nura-beauty' ),'url' => nura_home_cat_url( 'custom-wig-making' ) ),
					array( 'label' => __( 'Bridal Hair', 'nura-beauty' ),      'url' => nura_home_cat_url( 'bridal-hair' ) ),
				),
				'nura-tiles--4 nura-tiles--care'
			);
			?>
		</div>
	</section>

	<!-- REVIEWS (live WooCommerce product reviews) -->
	<?php if ( ! empty( $reviews ) ) : ?>
	<section class="section">
		<div class="nura-container">
			<div class="nura-shead nura-reveal">
				<p class="nura-eyebrow"><?php esc_html_e( 'Loved by clients', 'nura-beauty' ); ?></p>
				<h2><?php esc_html_e( 'Crowned with confidence', 'nura-beauty' ); ?></h2>
			</div>
			<div class="nura-reviews nura-reveal">
				<?php
				foreach ( $reviews as $rv ) :
					$rname = isset( $rv['author'] ) ? $rv['author'] : 'NURA client';
					$rtext = isset( $rv['text'] ) ? $rv['text'] : '';
					$rrate = isset( $rv['rating'] ) ? (int) $rv['rating'] : 5;
					$rprod = isset( $rv['product'] ) ? $rv['product'] : '';
					$rurl  = isset( $rv['url'] ) ? $rv['url'] : '';
					?>
					<figure class="nura-review">
						<div class="nura-review__stars" aria-hidden="true"><?php echo str_repeat( '&#9733;', max( 1, min( 5, $rrate ) ) ); ?></div>
						<blockquote><?php echo esc_html( $rtext ); ?></blockquote>
						<figcaption>
							<span class="nura-review__avatar"><?php echo esc_html( mb_substr( $rname, 0, 1 ) ); ?></span>
							<span><strong><?php echo esc_html( $rname ); ?></strong><?php if ( $rprod ) : ?><br><small><?php if ( $rurl ) : ?><a href="<?php echo esc_url( $rurl ); ?>"><?php echo esc_html( $rprod ); ?></a><?php else : echo esc_html( $rprod ); endif; ?></small><?php endif; ?></span>
						</figcaption>
					</figure>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<!-- RAIL: GREAT VALUE -->
	<?php if ( class_exists( 'WooCommerce' ) ) : ?>
	<?php nura_rail( __( 'Great value', 'nura-beauty' ), __( 'Under KES 5,000', 'nura-beauty' ), '[products limit="12" columns="12" orderby="price" order="ASC" class="nura-rail-products"]', $shop_url ); ?>
	<?php endif; ?>

	<!-- NURA JOURNAL -->
	<?php
	$journal = get_posts( array( 'numberposts' => 3, 'post_status' => 'publish' ) );
	if ( $journal ) :
		$blog_url = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/blog/' );
		?>
	<section class="section" id="journal">
		<div class="nura-container">
			<div class="nura-shead nura-shead--row nura-reveal">
				<div>
					<p class="nura-eyebrow"><?php esc_html_e( 'Wig wisdom', 'nura-beauty' ); ?></p>
					<h2><?php esc_html_e( 'The NURA Journal', 'nura-beauty' ); ?></h2>
				</div>
				<a class="nura-rail__viewall" href="<?php echo esc_url( $blog_url ); ?>"><?php esc_html_e( 'Read the journal', 'nura-beauty' ); ?> &#8594;</a>
			</div>
			<div class="nura-journal nura-reveal">
				<?php foreach ( $journal as $post ) : setup_postdata( $post ); ?>
					<a class="nura-jcard" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
						<?php if ( has_post_thumbnail( $post ) ) : ?>
							<span class="nura-jcard__media"><?php echo get_the_post_thumbnail( $post, 'medium_large', array( 'loading' => 'lazy' ) ); ?></span>
						<?php endif; ?>
						<span class="nura-jcard__body">
							<strong><?php echo esc_html( get_the_title( $post ) ); ?></strong>
							<small><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $post->post_excerpt ? $post->post_excerpt : $post->post_content ), 18 ) ); ?></small>
						</span>
					</a>
				<?php endforeach; wp_reset_postdata(); ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<!-- REAL NURA WOMEN -->
	<?php $ig = nura_opt( 'nura_instagram' ); $ig_url = $ig ? $ig : $wa; ?>
	<section class="section section--ink text-center" id="real-women">
		<div class="nura-container nura-reveal">
			<p class="nura-eyebrow"><?php esc_html_e( 'The NURA community', 'nura-beauty' ); ?></p>
			<h2><?php esc_html_e( 'Real NURA women', 'nura-beauty' ); ?></h2>
			<p class="nura-lede" style="max-width:640px;margin-inline:auto"><?php esc_html_e( 'Tag @nurabeauty in your crown to be featured here. Real women, real hair, real confidence - across Kenya and beyond.', 'nura-beauty' ); ?></p>
			<p style="margin-top:1.4rem">
				<a class="nura-btn nura-btn--gold" href="<?php echo esc_url( $ig_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Follow @nurabeauty', 'nura-beauty' ); ?></a>
			</p>
		</div>
	</section>

	<!-- BOOK A FITTING -->
	<section class="section section--ivory" id="book-a-fitting">
		<div class="nura-container nura-book nura-reveal">
			<div class="nura-book__intro">
				<p class="nura-eyebrow"><?php esc_html_e( 'Personal service', 'nura-beauty' ); ?></p>
				<h2><?php esc_html_e( 'Book a free fitting or virtual consultation', 'nura-beauty' ); ?></h2>
				<p class="nura-lede"><?php esc_html_e( 'Tell us the look you want. Our stylists guide you to your perfect unit - by video call, WhatsApp or in our Nairobi studio.', 'nura-beauty' ); ?></p>
				<ul class="nura-book__points">
					<li><?php esc_html_e( 'Free 15-minute consultation', 'nura-beauty' ); ?></li>
					<li><?php esc_html_e( 'Same-day Nairobi delivery', 'nura-beauty' ); ?></li>
					<li><?php esc_html_e( 'M-Pesa, card or pay on delivery', 'nura-beauty' ); ?></li>
				</ul>
			</div>
			<form class="nura-book__form" data-nura-book data-wa="<?php echo esc_attr( $wa ); ?>">
				<label><?php esc_html_e( 'Full name', 'nura-beauty' ); ?><input type="text" name="name" required></label>
				<label><?php esc_html_e( 'Phone / WhatsApp', 'nura-beauty' ); ?><input type="tel" name="phone" required></label>
				<label><?php esc_html_e( 'What do you need?', 'nura-beauty' ); ?>
					<select name="service">
						<option><?php esc_html_e( 'Wig consultation', 'nura-beauty' ); ?></option>
						<option><?php esc_html_e( 'Bridal fitting', 'nura-beauty' ); ?></option>
						<option><?php esc_html_e( 'Virtual try-on help', 'nura-beauty' ); ?></option>
						<option><?php esc_html_e( 'Installation booking', 'nura-beauty' ); ?></option>
						<option><?php esc_html_e( 'Other', 'nura-beauty' ); ?></option>
					</select>
				</label>
				<label><?php esc_html_e( 'Preferred date', 'nura-beauty' ); ?><input type="date" name="date"></label>
				<label class="nura-book__full"><?php esc_html_e( 'Tell us more', 'nura-beauty' ); ?><textarea name="note" rows="3"></textarea></label>
				<button type="submit" class="nura-btn nura-btn--gold nura-book__full"><?php esc_html_e( 'Send via WhatsApp', 'nura-beauty' ); ?></button>
				<p class="nura-book__alt"><?php esc_html_e( 'Prefer to talk now?', 'nura-beauty' ); ?> <a href="<?php echo esc_url( $wa ); ?>"><?php esc_html_e( 'Chat on WhatsApp', 'nura-beauty' ); ?></a></p>
			</form>
		</div>
	</section>

	<!-- EXCLUSIVE FEATURES -->
	<section class="section section--ink">
		<div class="nura-container">
			<div class="nura-shead nura-reveal">
				<p class="nura-eyebrow"><?php esc_html_e( 'Only at NURA', 'nura-beauty' ); ?></p>
				<h2><?php esc_html_e( 'A first for Kenyan beauty', 'nura-beauty' ); ?></h2>
			</div>
			<div class="nura-grid nura-grid--3 nura-reveal">
				<?php
				$features = array(
					array( 'AI Wig Finder', 'Answer a few questions or upload a selfie and let NURA recommend the perfect wig for your face shape, skin tone, lifestyle and budget.', '/ai-wig-finder/' ),
					array( 'Virtual Try-On', 'Preview any wig on your own photo before you buy - see your crown before it arrives.', '/virtual-try-on/' ),
					array( 'The NURA Circle', 'Your luxury client portal: order history, care schedule, warranty certificates and loyalty rewards.', '/nura-circle/' ),
				);
				foreach ( $features as $f ) : ?>
					<a class="nura-feature" href="<?php echo esc_url( home_url( $f[2] ) ); ?>">
						<div class="icn">&#10022;</div>
						<h3><?php echo esc_html( $f[0] ); ?></h3>
						<p><?php echo esc_html( $f[1] ); ?></p>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- CTA -->
	<section class="section text-center">
		<div class="nura-container nura-reveal">
			<p class="nura-eyebrow"><?php esc_html_e( 'Not sure where to start?', 'nura-beauty' ); ?></p>
			<h2><?php esc_html_e( 'Meet your NURA Stylist', 'nura-beauty' ); ?></h2>
			<p class="nura-lede" style="max-width:620px;margin-inline:auto"><?php esc_html_e( 'Chat with our AI stylist any time, or book a free virtual consultation and we will guide you to your perfect unit.', 'nura-beauty' ); ?></p>
			<p style="margin-top:1.4rem">
				<a class="nura-btn nura-btn--gold" href="<?php echo esc_url( home_url( '/book-appointment/' ) ); ?>"><?php esc_html_e( 'Book Now', 'nura-beauty' ); ?></a>
				<a class="nura-btn nura-btn--ghost" href="<?php echo esc_url( $wa ); ?>"><?php esc_html_e( 'Chat on WhatsApp', 'nura-beauty' ); ?></a>
			</p>
		</div>
	</section>

</main>
<?php get_footer();
