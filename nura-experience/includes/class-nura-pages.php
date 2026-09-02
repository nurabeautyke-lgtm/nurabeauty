<?php
/**
 * NURA policy & trust pages - one-click importer + content refresher.
 *
 * Creates the standard e-commerce trust/policy pages (About, Contact, Delivery,
 * Refund & Returns, Privacy, Terms, Wig Care Guide, Payment Info, Wig
 * Installation, FAQ) with real, NURA-specific copy, and can refresh the DRAFT
 * pages to the latest copy in one click.
 *
 * Safety model:
 *  - "Create" only inserts pages whose slug does not yet exist; it never
 *    overwrites an existing page. New pages are created as drafts.
 *  - "Update drafts" only rewrites pages that are still in DRAFT status. It
 *    never touches a PUBLISHED page - so an already-live page you have edited
 *    (for example Contact Us or FAQ) is left exactly as it is.
 *  - New/updated pages stay drafts, so nothing goes live until you review and
 *    publish.
 *
 * The copy is grounded in NURA's own published details (Nairobi studio,
 * +254 714 994 898, care@nurabeauty.co.ke, M-Pesa/card/PayPal, same-day Nairobi
 * delivery, 48-hour returns window). A few store-specific values that only the
 * owner can confirm (exact Till number, precise fees) are shown at checkout or
 * left as a clearly marked note rather than invented.
 *
 * @package NURA_Experience
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NURAX_Pages {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_post_nurax_make_pages', array( $this, 'handle_create' ) );
		add_action( 'admin_post_nurax_update_pages', array( $this, 'handle_update' ) );
	}

	/** slug => array( title, content ) */
	public static function defs() {
		$biz   = get_bloginfo( 'name' );
		$phone = '+254 714 994 898';
		$email = 'care@nurabeauty.co.ke';

		$about = <<<HTML
<h2>The House of Radiant Confidence</h2>
<p>$biz is a Nairobi-based house of premium human hair wigs in Kenya, built on a simple belief: every woman deserves to wear her crown with confidence. We hand-finish and personally curate each unit, then back it with the kind of concierge service you would expect from a luxury house.</p>
<h3>What we stand for</h3>
<ul>
<li><strong>Honest hair.</strong> Our premium units are verified human hair, supplied with a provenance certificate and a written guarantee. We never sell synthetic hair as human hair.</li>
<li><strong>Personal service.</strong> From your first consultation to installation and aftercare, our team helps you choose, fit and maintain the right wig for your face shape, lifestyle and budget.</li>
<li><strong>Made for Kenya.</strong> Same-day delivery in Nairobi, fast countrywide shipping and M-Pesa checkout, designed around how our clients actually shop for wigs in Kenya.</li>
</ul>
<h3>The NURA Experience</h3>
<p>We are more than a wig shop. Our team handles installation, revamp, styling, colouring and consultation end to end, and members of <em>The NURA Circle</em> enjoy care reminders, scheduled revamps and loyalty rewards. Visit us at Imenti House, Moi Avenue, Nairobi CBD - hand-finished in Nairobi, made for you.</p>
<p>Have a question or want a recommendation? Message us on WhatsApp at <strong>$phone</strong> or email <strong>$email</strong>.</p>
HTML;

		$contact = <<<HTML
<h2>Contact Us</h2>
<p>We would love to hear from you. Reach us by WhatsApp, phone or email, or visit our Nairobi studio by appointment.</p>
<h3>Talk to us</h3>
<ul>
<li><strong>WhatsApp &amp; phone:</strong> $phone - fastest for orders, consultations and bookings.</li>
<li><strong>Email:</strong> $email</li>
<li><strong>Studio:</strong> Nairobi, Kenya (by appointment)</li>
<li><strong>Hours:</strong> Monday to Saturday, 9:00am to 6:00pm</li>
</ul>
<h3>Visit the studio</h3>
<p>Come in for a consultation, fitting, installation or styling. So we can give you our full concierge attention, please book an appointment on WhatsApp before visiting.</p>
<h3>Follow the house</h3>
<p>See transformations, tutorials and new drops on Instagram and TikTok at <strong>@nurabeauty</strong>.</p>
HTML;

		$delivery = <<<HTML
<h2>Delivery Information</h2>
<p>We deliver premium wigs, hair and beauty across Kenya and worldwide, carefully packaged and tracked.</p>
<h3>Delivery times</h3>
<ul>
<li><strong>Nairobi:</strong> same-day delivery on orders placed before 2:00pm; otherwise next day.</li>
<li><strong>Countrywide (Kenya):</strong> 1 to 3 business days via trusted couriers.</li>
<li><strong>International / diaspora:</strong> 3 to 10 business days via DHL or FedEx with full tracking, priced in USD.</li>
</ul>
<h3>Fees &amp; processing</h3>
<p>Delivery fees are calculated at checkout based on your location; free delivery is available on qualifying orders. Orders are processed within 1 business day. You will receive tracking details once your order is dispatched.</p>
<h3>Pay on delivery</h3>
<p>Pay-on-delivery is available within Nairobi. For all other locations we confirm payment (or a deposit) before dispatch. See our <a href="/payment-information/">Payment Information</a> page for options.</p>
<p>Questions about a delivery? Message us on WhatsApp at <strong>$phone</strong>.</p>
HTML;

		$returns = <<<HTML
<h2>Refund &amp; Returns</h2>
<p>Your confidence matters to us. Because wigs and hair are intimate, hygiene-sensitive products, our returns policy is designed to be fair to every client.</p>
<h3>What can be returned</h3>
<ul>
<li><strong>Eligible:</strong> unworn, unaltered units in their original packaging, returned within <strong>48 hours</strong> of delivery.</li>
<li><strong>Not eligible:</strong> units that have been worn, washed, cut, coloured or installed cannot be returned or exchanged for hygiene reasons.</li>
</ul>
<h3>Faulty or incorrect items</h3>
<p>If you receive a faulty or incorrect item, contact us within 48 hours and we will make it right with a replacement or refund. Please keep the item unused and in its original packaging.</p>
<h3>How to start a return</h3>
<p>Message us on WhatsApp at <strong>$phone</strong> or email <strong>$email</strong> with your order number and a short description (and photos, if relevant). We will confirm the next steps.</p>
<h3>Warranty</h3>
<p>Premium units carry a written longevity guarantee (up to 6 months on custom units). Warranty covers workmanship, not damage from wear, heat or improper care. Keep your care card and order confirmation as proof of purchase.</p>
HTML;

		$privacy = <<<HTML
<h2>Privacy Policy</h2>
<p>$biz respects your privacy. This policy explains what we collect, how we use it, and your rights. It is written to align with Kenya's Data Protection Act, 2019.</p>
<h3>What we collect</h3>
<ul>
<li>Contact and order details you provide (name, phone/WhatsApp, email, delivery address).</li>
<li>Payment information, processed securely by our payment providers (M-Pesa, card and PayPal). We do not store full card numbers.</li>
<li>Usage data such as pages viewed and items added to cart, via cookies and analytics, to improve the store and show relevant offers.</li>
</ul>
<h3>How we use it</h3>
<p>To process and deliver your orders, provide support and consultations, send updates you have opted into, prevent fraud, and improve our products and service.</p>
<h3>Who we share it with</h3>
<p>Only with the partners needed to serve you - couriers for delivery, payment processors for payment, and analytics/advertising providers - each under their own privacy terms. We never sell your personal data.</p>
<h3>Cookies &amp; marketing</h3>
<p>We use cookies for the cart, analytics and (with your consent) retargeting. You can manage cookies in your browser, and unsubscribe from marketing messages at any time.</p>
<h3>Your rights</h3>
<p>You may request access to, correction of, or deletion of your personal data. Email <strong>$email</strong> and we will respond within a reasonable time.</p>
HTML;

		$terms = <<<HTML
<h2>Terms &amp; Conditions</h2>
<p>By using this website and placing an order with $biz, you agree to these terms.</p>
<h3>Orders &amp; pricing</h3>
<p>All prices are in Kenyan Shillings (KES) and include applicable taxes unless stated otherwise. We may correct pricing errors and confirm or decline any order. Product availability and variation prices are shown on each product page.</p>
<h3>Payment</h3>
<p>We accept M-Pesa, Visa/Mastercard, PayPal, and pay-on-delivery within Nairobi. For units above KES 15,000, NURA Flex lets you begin with a 50% M-Pesa deposit and settle the balance before delivery, or split over instalments. Orders are dispatched once payment (or the agreed deposit) is confirmed.</p>
<h3>Delivery &amp; returns</h3>
<p>Delivery timeframes are estimates - see <a href="/delivery-information/">Delivery Information</a>. Returns are governed by our <a href="/refund-returns/">Refund &amp; Returns</a> policy.</p>
<h3>Products &amp; care</h3>
<p>Colours and textures may vary slightly from screen images. Wig longevity depends on proper care; follow our <a href="/wig-care-guide/">Wig Care Guide</a> to protect your warranty.</p>
<h3>Intellectual property &amp; liability</h3>
<p>All content on this site is the property of $biz and may not be copied without permission. To the extent permitted by law, our liability is limited to the value of your order. These terms are governed by the laws of Kenya.</p>
<p>Questions? Contact us at <strong>$email</strong> or <strong>$phone</strong>.</p>
HTML;

		$care = <<<HTML
<h2>Wig Care Guide</h2>
<p>Treat your NURA unit well and it will stay soft, full and radiant for far longer. Every unit ships with a care card and a QR code linking to a care video.</p>
<h3>Washing</h3>
<ul>
<li>Gently detangle from the ends up before washing.</li>
<li>Wash every 7 to 10 wears (or sooner with heavy product use) in cool water with a sulphate-free shampoo.</li>
<li>Condition the hair - avoid the lace and knots - then rinse thoroughly.</li>
</ul>
<h3>Drying &amp; styling</h3>
<ul>
<li>Blot with a towel and air-dry on a wig stand; never brush while soaking wet.</li>
<li>Use heat tools only on human-hair units, on a low-to-medium setting, with a heat protectant.</li>
<li>Keep the lace clean and free of heavy oils to preserve a natural hairline.</li>
</ul>
<h3>Storage</h3>
<p>Store your unit on a stand or in the satin bag it came in, away from direct sunlight and dust. This keeps the style and prevents tangling.</p>
<h3>Keep it effortless</h3>
<p>Stock up on the essentials from our <a href="/product-category/wig-care/">Wig Care</a> range, and join <em>The NURA Circle</em> for automatic care reminders and scheduled professional revamps. Need a refresh? Ask us about wash &amp; revamp at <strong>$phone</strong>.</p>
HTML;

		$payment = <<<HTML
<h2>Payment Information</h2>
<p>Shopping with NURA is secure and flexible. Choose whatever works best for you at checkout.</p>
<h3>Ways to pay</h3>
<ul>
<li><strong>M-Pesa:</strong> STK push, Buy Goods/Till, or Pochi la Biashara. Your M-Pesa prompt and Till details are shown at checkout.</li>
<li><strong>Card:</strong> Visa and Mastercard, processed securely.</li>
<li><strong>PayPal:</strong> ideal for diaspora and international orders.</li>
<li><strong>Pay on delivery:</strong> available within Nairobi.</li>
</ul>
<h3>NURA Flex (flexible payment)</h3>
<p>For units above KES 15,000, begin with a <strong>50% M-Pesa deposit</strong> and pay the balance before delivery, or split the cost over instalments. Lipa Later / Buy-Now-Pay-Later is available on eligible orders.</p>
<h3>Security</h3>
<p>Payments are handled by trusted, encrypted providers. We never see or store your full card details. If a payment fails or you need help, message us on WhatsApp at <strong>$phone</strong>.</p>
HTML;

		$install = <<<HTML
<h2>Wig Installation &amp; Services</h2>
<p>NURA is a full-service house. Beyond selling your unit, our stylists help you wear it flawlessly.</p>
<h3>Installation</h3>
<p>Book a professional installation at our Nairobi studio, or - for VIP clients - at home. We melt the lace, style and finish so your unit looks natural and photograph-ready.</p>
<h3>Our services</h3>
<ul>
<li>Installation &amp; hairline styling</li>
<li>Styling, curling and restyling</li>
<li>Colouring and customisation</li>
<li>Wash &amp; revamp (bring your unit back to life)</li>
<li>Repairs and re-ventilation</li>
<li>Custom wig making and bridal hair</li>
</ul>
<h3>Book your appointment</h3>
<p>Message us on WhatsApp at <strong>$phone</strong> or email <strong>$email</strong> to book. Studio hours are Monday to Saturday, 9:00am to 6:00pm. We recommend booking ahead so we can reserve dedicated time for you.</p>
HTML;

		$faq = <<<HTML
<h2>Frequently Asked Questions</h2>
<h3>Are NURA wigs real human hair?</h3>
<p>Yes. Our premium units are verified human hair with a provenance certificate and written guarantee. We never sell synthetic hair as human hair.</p>
<h3>How do I choose the right wig?</h3>
<p>Take our Find Your Wig quiz, chat with NURA Stylist, or book a consultation. You can also visit our Nairobi studio for a fitting.</p>
<h3>What lengths, textures and colours are available?</h3>
<p>Most units come in 10&Prime; to 30&Prime; lengths, densities of 130% to 200%, textures from straight to afro curly, and colours from natural black to blonde, ombr&eacute;, burgundy and 613. Each variation shows its own price and availability on the product page.</p>
<h3>How do I pay?</h3>
<p>M-Pesa, Visa/Mastercard, PayPal, and pay-on-delivery in Nairobi. NURA Flex is available on units above KES 15,000. See <a href="/payment-information/">Payment Information</a>.</p>
<h3>How fast is delivery?</h3>
<p>Same-day within Nairobi (order before 2:00pm), 1 to 3 business days countrywide, and 3 to 10 business days internationally with tracking. See <a href="/delivery-information/">Delivery Information</a>.</p>
<h3>Can I return or exchange a wig?</h3>
<p>Unworn, unaltered units can be returned within 48 hours of delivery. Worn, cut or installed units cannot be returned for hygiene reasons. See <a href="/refund-returns/">Refund &amp; Returns</a>.</p>
<h3>Do you install wigs?</h3>
<p>Yes - installation, styling, colouring, revamp and repairs. See <a href="/wig-installation/">Wig Installation</a>.</p>
<p>Still have a question? Chat with us on WhatsApp at <strong>$phone</strong>.</p>
HTML;

		return array(
			'about-nura'           => array( 'About NURA', $about ),
			'contact-us'           => array( 'Contact Us', $contact ),
			'delivery-information' => array( 'Delivery Information', $delivery ),
			'refund-returns'       => array( 'Refund & Returns', $returns ),
			'privacy-policy'       => array( 'Privacy Policy', $privacy ),
			'terms-conditions'     => array( 'Terms & Conditions', $terms ),
			'wig-care-guide'       => array( 'Wig Care Guide', $care ),
			'payment-information'  => array( 'Payment Information', $payment ),
			'wig-installation'     => array( 'Wig Installation', $install ),
			'faq'                  => array( 'FAQ', $faq ),
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
			<p style="max-width:760px"><?php esc_html_e( 'These are your standard trust and policy pages, written with real NURA details. "Create" adds any missing page as a draft. "Update drafts" refreshes pages that are still drafts to the latest copy - it never changes a page you have already published (such as Contact Us or FAQ). Nothing goes live until you review and publish it.', 'nura-experience' ); ?></p>

			<?php if ( isset( $_GET['created'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
				<div class="notice notice-success"><p><?php printf( esc_html__( 'Created %d new draft page(s).', 'nura-experience' ), (int) $_GET['created'] ); // phpcs:ignore WordPress.Security.NonceVerification ?></p></div>
			<?php endif; ?>
			<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
				<div class="notice notice-success"><p><?php printf( esc_html__( 'Refreshed %d draft page(s) to the latest content. Review under Pages, then publish.', 'nura-experience' ), (int) $_GET['updated'] ); // phpcs:ignore WordPress.Security.NonceVerification ?></p></div>
			<?php endif; ?>

			<table class="widefat" style="max-width:640px;margin:1em 0">
				<thead><tr><th><?php esc_html_e( 'Page', 'nura-experience' ); ?></th><th><?php esc_html_e( 'Status', 'nura-experience' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $rows as $slug => $r ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $r[0] ); ?></strong> <code>/<?php echo esc_html( $slug ); ?>/</code></td>
						<td><?php
						if ( 'publish' === $r[1] ) {
							echo '<span style="color:#2e7d32;font-weight:600">' . esc_html__( 'Published (left untouched)', 'nura-experience' ) . '</span>';
						} elseif ( 'draft' === $r[1] ) {
							echo '<span style="color:#b8860b;font-weight:600">' . esc_html__( 'Draft', 'nura-experience' ) . '</span>';
						} else {
							echo '<span style="color:#a00">' . esc_html__( 'Not created', 'nura-experience' ) . '</span>';
						}
						?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<p style="display:flex;gap:1rem;flex-wrap:wrap">
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="nurax_make_pages">
					<?php wp_nonce_field( 'nurax_make_pages' ); ?>
					<?php submit_button( __( 'Create missing pages', 'nura-experience' ), 'secondary', 'submit', false ); ?>
				</form>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="nurax_update_pages">
					<?php wp_nonce_field( 'nurax_update_pages' ); ?>
					<?php submit_button( __( 'Update draft pages to latest content', 'nura-experience' ), 'primary', 'submit', false ); ?>
				</form>
			</p>
			<p class="description" style="max-width:760px"><?php esc_html_e( 'Tip: after updating, open each page under Pages, adjust anything specific to you (Till number, exact fees), and click Publish.', 'nura-experience' ); ?></p>
		</div>
		<?php
	}

	public function handle_create() {
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

		wp_safe_redirect( admin_url( 'admin.php?page=nurax-pages&created=' . (int) $created ) );
		exit;
	}

	public function handle_update() {
		if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'nurax_update_pages' ) ) {
			wp_die( esc_html__( 'Not allowed', 'nura-experience' ) );
		}

		$updated = 0;
		foreach ( self::defs() as $slug => $d ) {
			$existing = get_page_by_path( $slug );
			if ( ! $existing || 'draft' !== $existing->post_status ) {
				continue; // Never touch published pages.
			}
			$res = wp_update_post( array(
				'ID'           => (int) $existing->ID,
				'post_title'   => $d[0],
				'post_content' => $d[1],
			), true );
			if ( $res && ! is_wp_error( $res ) ) {
				$updated++;
			}
		}

		wp_safe_redirect( admin_url( 'admin.php?page=nurax-pages&updated=' . (int) $updated ) );
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
