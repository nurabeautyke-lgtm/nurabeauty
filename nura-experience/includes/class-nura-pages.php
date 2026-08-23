<?php
/**
 * NURA policy & trust pages - one-click importer.
 *
 * Creates the standard e-commerce trust/policy pages as DRAFTS (About, Contact,
 * Delivery, Refund & Returns, Privacy, Terms, Wig Care Guide, Payment Info, Wig
 * Installation, FAQ) with editable starter copy, and adds them to a "NURA
 * Footer" menu wired to the theme's footer menu location when one is free.
 *
 * Idempotent: a page whose slug already exists is skipped, never overwritten.
 * Pages are created as drafts, so nothing goes live until you review and
 * publish. Starter copy uses [bracketed placeholders] for anything only you can
 * confirm - it never invents policy specifics.
 *
 * @package NURA_Experience
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NURAX_Pages {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_post_nurax_make_pages', array( $this, 'handle' ) );
	}

	/** slug => array( title, content ) */
	public static function defs() {
		$biz = get_bloginfo( 'name' );
		return array(
			'about-nura' => array(
				'About NURA',
				"<h2>About " . $biz . "</h2>\n<p>[Tell your story: who NURA is, why you started, and what makes your wigs and service special.]</p>\n<p>We are a Nairobi-based house of premium wigs, hair and beauty, serving clients across Kenya.</p>",
			),
			'contact-us' => array(
				'Contact Us',
				"<h2>Contact Us</h2>\n<p><strong>WhatsApp / Phone:</strong> [your number]</p>\n<p><strong>Email:</strong> [your email]</p>\n<p><strong>Shop / Pickup:</strong> [your Nairobi address]</p>\n<p><strong>Hours:</strong> [opening hours]</p>",
			),
			'delivery-information' => array(
				'Delivery Information',
				"<h2>Delivery Information</h2>\n<p><strong>Nairobi:</strong> [delivery timeframe and fee, or pickup details].</p>\n<p><strong>Countrywide:</strong> We deliver across Kenya via [courier]. [Timeframe and fees].</p>\n<p>Orders are processed within [X] business days.</p>",
			),
			'refund-returns' => array(
				'Refund & Returns',
				"<h2>Refund &amp; Returns</h2>\n<p>[State your policy clearly. Many wig retailers accept returns only on unopened, unworn items within a set window for hygiene reasons.]</p>\n<p>To start a return, contact us at [email / WhatsApp] with your order number.</p>",
			),
			'privacy-policy' => array(
				'Privacy Policy',
				"<h2>Privacy Policy</h2>\n<p>This policy explains what information " . $biz . " collects, how it is used, and your rights. [Review and adapt to your business and Kenya's Data Protection Act, 2019.]</p>",
			),
			'terms-conditions' => array(
				'Terms & Conditions',
				"<h2>Terms &amp; Conditions</h2>\n<p>By using this website and placing an order you agree to these terms. [Add pricing, payment, delivery, liability and governing-law clauses.]</p>",
			),
			'wig-care-guide' => array(
				'Wig Care Guide',
				"<h2>Wig Care Guide</h2>\n<p>Keep your NURA wig looking new:</p>\n<ul>\n<li>Wash gently every [X] wears with a sulphate-free shampoo.</li>\n<li>Condition, air-dry on a stand, and store in a satin bag.</li>\n<li>Use heat tools only on human-hair units, on a low setting.</li>\n</ul>",
			),
			'payment-information' => array(
				'Payment Information',
				"<h2>Payment Information</h2>\n<p>We accept <strong>M-Pesa</strong>, card, and [cash / pay on delivery]. [Add your Till / Paybill number and any instructions.]</p>",
			),
			'wig-installation' => array(
				'Wig Installation',
				"<h2>Wig Installation</h2>\n<p>Book a professional install at our Nairobi studio. [Describe the service, duration and price.]</p>\n<p>Book via [WhatsApp / appointment link].</p>",
			),
			'faq' => array(
				'FAQ',
				"<h2>Frequently Asked Questions</h2>\n<p><strong>Are your wigs human hair?</strong> [Answer honestly per product.]</p>\n<p><strong>Do you deliver countrywide?</strong> Yes, across Kenya.</p>\n<p><strong>How do I pay?</strong> M-Pesa, card or [option].</p>\n<p><strong>Can I return a wig?</strong> See our Refund &amp; Returns page.</p>",
			),
		);
	}

	public function menu() {
		add_submenu_page( 'woocommerce', 'NURA Pages', 'NURA Pages', 'manage_options', 'nurax-pages', array( $this, 'page' ) );
	}

	private function status() {
		$rows = array();
		foreach ( self::defs() as $slug => $d ) {
			$existing      = get_page_by_path( $slug );
			$rows[ $slug ] = array( $d[0], $existing ? $existing->post_status : '' );
		}
		return $rows;
	}

	public function page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$rows = $this->status();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'NURA Policy & Trust Pages', 'nura-experience' ); ?></h1>
			<p style="max-width:720px"><?php esc_html_e( 'Creates the standard trust and policy pages as drafts with starter copy you can edit, then adds them to a "NURA Footer" menu. Existing pages are never overwritten, and nothing goes live until you review and publish each page.', 'nura-experience' ); ?></p>
			<?php if ( isset( $_GET['done'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
				<div class="notice notice-success"><p><?php printf( esc_html__( 'Done. %d page(s) created as drafts. Review, edit and publish them under Pages, then check Appearance > Menus.', 'nura-experience' ), (int) $_GET['done'] ); // phpcs:ignore WordPress.Security.NonceVerification ?></p></div>
			<?php endif; ?>
			<table class="widefat" style="max-width:640px;margin:1em 0">
				<thead><tr><th><?php esc_html_e( 'Page', 'nura-experience' ); ?></th><th><?php esc_html_e( 'Status', 'nura-experience' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $rows as $slug => $r ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $r[0] ); ?></strong> <code>/<?php echo esc_html( $slug ); ?>/</code></td>
						<td><?php echo $r[1] ? '<span style="color:#2e7d32;font-weight:600">' . esc_html( ucfirst( $r[1] ) ) . '</span>' : '<span style="color:#a00">' . esc_html__( 'Not created', 'nura-experience' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="nurax_make_pages">
				<?php wp_nonce_field( 'nurax_make_pages' ); ?>
				<?php submit_button( __( 'Create the pages (as drafts)', 'nura-experience' ) ); ?>
			</form>
		</div>
		<?php
	}

	public function handle() {
		if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'nurax_make_pages' ) ) {
			wp_die( esc_html__( 'Not allowed', 'nura-experience' ) );
		}

		$created  = 0;
		$page_ids = array();
		foreach ( self::defs() as $slug => $d ) {
			$existing = get_page_by_path( $slug );
			if ( $existing ) {
				$page_ids[] = (int) $existing->ID;
				continue;
			}
			$id = wp_insert_post( array(
				'post_title'   => $d[0],
				'post_name'    => $slug,
				'post_content' => $d[1],
				'post_status'  => 'draft',
				'post_type'    => 'page',
			) );
			if ( $id && ! is_wp_error( $id ) ) {
				$created++;
				$page_ids[] = (int) $id;
			}
		}

		$this->footer_menu( $page_ids );

		wp_safe_redirect( admin_url( 'admin.php?page=nurax-pages&done=' . (int) $created ) );
		exit;
	}

	/** Build/refresh a "NURA Footer" menu with these pages and assign it to a free footer location. */
	private function footer_menu( $page_ids ) {
		if ( empty( $page_ids ) ) {
			return;
		}
		$menu    = wp_get_nav_menu_object( 'NURA Footer' );
		$menu_id = $menu ? (int) $menu->term_id : wp_create_nav_menu( 'NURA Footer' );
		if ( is_wp_error( $menu_id ) || ! $menu_id ) {
			return;
		}
		$menu_id = (int) $menu_id;

		$linked = array();
		$items  = wp_get_nav_menu_items( $menu_id );
		if ( is_array( $items ) ) {
			foreach ( $items as $item ) {
				if ( 'page' === $item->object ) {
					$linked[] = (int) $item->object_id;
				}
			}
		}
		foreach ( $page_ids as $pid ) {
			if ( in_array( (int) $pid, $linked, true ) ) {
				continue;
			}
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-object'    => 'page',
				'menu-item-object-id' => (int) $pid,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			) );
		}

		// Assign to a footer location only if the theme registers one and it is empty.
		$locations = get_registered_nav_menus();
		$assigned  = get_theme_mod( 'nav_menu_locations', array() );
		if ( ! is_array( $assigned ) ) {
			$assigned = array();
		}
		$changed = false;
		foreach ( array_keys( (array) $locations ) as $loc ) {
			if ( false !== stripos( $loc, 'footer' ) && empty( $assigned[ $loc ] ) ) {
				$assigned[ $loc ] = $menu_id;
				$changed          = true;
			}
		}
		if ( $changed ) {
			set_theme_mod( 'nav_menu_locations', $assigned );
		}
	}
}
