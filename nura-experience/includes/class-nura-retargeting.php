<?php
/**
 * Retargeting pixels for NURA.
 *
 * The honest answer to *anonymous* browse and cart abandonment: you cannot
 * email a signed-out visitor, but you can retarget them with ads. This module
 * fires the standard e-commerce events to Meta Pixel, Google Analytics 4 and
 * (optionally) a Google Ads conversion, so remarketing audiences build
 * automatically:
 *
 *   PageView / page_view       - every page
 *   ViewContent / view_item    - single product
 *   AddToCart / add_to_cart     - on the WooCommerce add-to-cart event
 *   InitiateCheckout / begin_checkout - checkout page
 *   Purchase / purchase        - order received (with value + contents)
 *
 * All tags are consent-gated (a lightweight cookie banner, overridable by any
 * consent plugin via the `nurax_retargeting_consent` filter) and the whole
 * feature is dormant until it is enabled and at least one tag ID is entered,
 * under WooCommerce > NURA Retargeting.
 *
 * @package NURA_Experience
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class NURAX_Retargeting {

	const OPTION = 'nurax_retargeting';
	const COOKIE = 'nurax_px_consent';

	private static $instance = null;

	/** Order id captured on the thank-you page so Purchase can fire in the footer. */
	private $purchase_order_id = 0;

	public static function instance(): NURAX_Retargeting {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function boot(): void {
		add_action( 'admin_menu', array( $this, 'menu' ), 47 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		if ( ! $this->active() ) {
			return;
		}
		add_action( 'wp_head', array( $this, 'print_base' ), 5 );
		add_action( 'woocommerce_thankyou', array( $this, 'capture_purchase' ), 5, 1 );
		add_action( 'wp_footer', array( $this, 'print_events' ), 20 );
	}

	/* --------------------------------------------------------------- settings */

	public function defaults(): array {
		return array(
			'enabled'          => 0,
			'meta_pixel_id'    => '',
			'ga4_id'           => '',
			'google_ads_id'    => '',
			'ads_purchase_lbl' => '',
			'require_consent'  => 1,
			'consent_text'     => __( 'We use cookies to personalise content and ads. You can accept or decline advertising cookies.', 'nura-experience' ),
		);
	}

	public function settings(): array {
		$saved = get_option( self::OPTION, array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		return array_merge( $this->defaults(), $saved );
	}

	public function register_settings(): void {
		register_setting( 'nurax_retargeting_group', self::OPTION, array( $this, 'sanitize' ) );
	}

	public function sanitize( $input ): array {
		$d   = $this->defaults();
		$out = array();
		$out['enabled']          = empty( $input['enabled'] ) ? 0 : 1;
		$out['meta_pixel_id']    = preg_replace( '/[^0-9]/', '', (string) ( $input['meta_pixel_id'] ?? '' ) );
		$out['ga4_id']           = preg_replace( '/[^A-Za-z0-9\-]/', '', (string) ( $input['ga4_id'] ?? '' ) );
		$out['google_ads_id']    = preg_replace( '/[^A-Za-z0-9\-]/', '', (string) ( $input['google_ads_id'] ?? '' ) );
		$out['ads_purchase_lbl'] = preg_replace( '/[^A-Za-z0-9\-_]/', '', (string) ( $input['ads_purchase_lbl'] ?? '' ) );
		$out['require_consent']  = empty( $input['require_consent'] ) ? 0 : 1;
		$out['consent_text']     = isset( $input['consent_text'] ) && '' !== trim( (string) $input['consent_text'] )
			? sanitize_text_field( (string) $input['consent_text'] )
			: $d['consent_text'];
		return $out;
	}

	/** Enabled with at least one tag configured. */
	public function active(): bool {
		$s = $this->settings();
		if ( empty( $s['enabled'] ) ) {
			return false;
		}
		return ( '' !== $s['meta_pixel_id'] || '' !== $s['ga4_id'] || '' !== $s['google_ads_id'] );
	}

	/** Whether advertising tags may fire for this visitor. */
	public function consent_ok(): bool {
		$s = $this->settings();
		if ( empty( $s['require_consent'] ) ) {
			return (bool) apply_filters( 'nurax_retargeting_consent', true );
		}
		$c  = isset( $_COOKIE[ self::COOKIE ] ) ? (string) $_COOKIE[ self::COOKIE ] : '';
		$ok = ( '1' === $c );
		return (bool) apply_filters( 'nurax_retargeting_consent', $ok );
	}

	/** Currency for event payloads. */
	private function currency(): string {
		return function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'KES';
	}

	/* ------------------------------------------------------------------ tags */

	public function print_base(): void {
		if ( ! $this->consent_ok() ) {
			return;
		}
		$s     = $this->settings();
		$pixel = $s['meta_pixel_id'];
		$ga4   = $s['ga4_id'];
		$ads   = $s['google_ads_id'];

		if ( '' !== $pixel ) {
			$pid = esc_js( $pixel );
			echo "\n<!-- NURA Meta Pixel -->\n<script>\n";
			echo "!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');\n";
			echo "fbq('init','" . $pid . "');fbq('track','PageView');\n";
			echo "</script>\n";
		}

		if ( '' !== $ga4 || '' !== $ads ) {
			$primary = '' !== $ga4 ? $ga4 : $ads;
			echo "\n<!-- NURA Google tag -->\n";
			echo '<script async src="https://www.googletagmanager.com/gtag/js?id=' . esc_attr( $primary ) . '"></script>' . "\n";
			echo "<script>\nwindow.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());\n";
			if ( '' !== $ga4 ) {
				echo "gtag('config','" . esc_js( $ga4 ) . "');\n";
			}
			if ( '' !== $ads ) {
				echo "gtag('config','" . esc_js( $ads ) . "');\n";
			}
			echo "</script>\n";
		}
	}

	public function capture_purchase( $order_id ): void {
		$this->purchase_order_id = (int) $order_id;
	}

	public function print_events(): void {
		if ( ! $this->consent_ok() ) {
			$this->print_consent_bar();
			return;
		}
		$s        = $this->settings();
		$has_meta = ( '' !== $s['meta_pixel_id'] );
		$has_ga   = ( '' !== $s['ga4_id'] );
		$has_ads  = ( '' !== $s['google_ads_id'] );
		$currency = $this->currency();

		$js = '';

		// Single product -> ViewContent / view_item.
		if ( function_exists( 'is_product' ) && is_product() ) {
			$pid  = (int) get_the_ID();
			$prod = function_exists( 'wc_get_product' ) ? wc_get_product( $pid ) : null;
			if ( $prod ) {
				$name  = wp_json_encode( $prod->get_name() );
				$price = (float) wc_get_price_to_display( $prod );
				if ( $has_meta ) {
					$js .= "fbq('track','ViewContent',{content_ids:['" . esc_js( (string) $pid ) . "'],content_type:'product',value:" . $price . ",currency:'" . esc_js( $currency ) . "'});\n";
				}
				if ( $has_ga ) {
					$js .= "gtag('event','view_item',{currency:'" . esc_js( $currency ) . "',value:" . $price . ",items:[{item_id:'" . esc_js( (string) $pid ) . "',item_name:" . $name . ",price:" . $price . "}]});\n";
				}
			}
		}

		// Checkout (not the thank-you page) -> InitiateCheckout / begin_checkout.
		$is_checkout = function_exists( 'is_checkout' ) && is_checkout();
		$is_received = function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' );
		if ( $is_checkout && ! $is_received && function_exists( 'WC' ) && WC()->cart ) {
			$total = (float) WC()->cart->get_total( 'edit' );
			if ( $has_meta ) {
				$js .= "fbq('track','InitiateCheckout',{value:" . $total . ",currency:'" . esc_js( $currency ) . "'});\n";
			}
			if ( $has_ga ) {
				$js .= "gtag('event','begin_checkout',{currency:'" . esc_js( $currency ) . "',value:" . $total . "});\n";
			}
		}

		// Purchase (thank-you page) -> Purchase / purchase / Ads conversion.
		if ( $this->purchase_order_id > 0 && function_exists( 'wc_get_order' ) ) {
			$order = wc_get_order( $this->purchase_order_id );
			if ( $order ) {
				$oid      = (string) $order->get_id();
				$value    = (float) $order->get_total();
				$cur      = (string) $order->get_currency();
				$ids      = array();
				$ga_items = array();
				foreach ( $order->get_items() as $item ) {
					if ( ! is_object( $item ) || ! method_exists( $item, 'get_product_id' ) ) {
						continue;
					}
					$ipid  = (int) $item->get_product_id();
					$ids[] = (string) $ipid;
					$ga_items[] = array(
						'item_id'   => (string) $ipid,
						'item_name' => method_exists( $item, 'get_name' ) ? $item->get_name() : '',
						'quantity'  => method_exists( $item, 'get_quantity' ) ? (int) $item->get_quantity() : 1,
					);
				}
				if ( $has_meta ) {
					$js .= "fbq('track','Purchase',{value:" . $value . ",currency:'" . esc_js( $cur ) . "',content_type:'product',content_ids:" . wp_json_encode( $ids ) . "},{eventID:'nura-" . esc_js( $oid ) . "'});\n";
				}
				if ( $has_ga ) {
					$js .= "gtag('event','purchase',{transaction_id:'" . esc_js( $oid ) . "',value:" . $value . ",currency:'" . esc_js( $cur ) . "',items:" . wp_json_encode( $ga_items ) . "});\n";
				}
				if ( $has_ads && '' !== $s['ads_purchase_lbl'] ) {
					$send_to = $s['google_ads_id'] . '/' . $s['ads_purchase_lbl'];
					$js .= "gtag('event','conversion',{send_to:'" . esc_js( $send_to ) . "',value:" . $value . ",currency:'" . esc_js( $cur ) . "',transaction_id:'" . esc_js( $oid ) . "'});\n";
				}
			}
		}

		// AddToCart listener (fires on WooCommerce's added_to_cart JS event).
		$atc = '';
		if ( $has_meta ) {
			$atc .= "if(window.fbq){fbq('track','AddToCart');}";
		}
		if ( $has_ga ) {
			$atc .= "if(window.gtag){gtag('event','add_to_cart');}";
		}
		$listener = '';
		if ( '' !== $atc ) {
			$listener = "(function(){function h(){" . $atc . "}"
				. "if(window.jQuery){window.jQuery(document.body).on('added_to_cart',h);}"
				. "document.body.addEventListener('click',function(e){var t=e.target;if(t&&t.closest&&t.closest('.add_to_cart_button,.single_add_to_cart_button')){setTimeout(h,300);}},true);})();\n";
		}

		if ( '' === $js && '' === $listener ) {
			return;
		}
		echo "\n<!-- NURA Retargeting events -->\n<script>\n" . $js . $listener . "</script>\n";
	}

	private function print_consent_bar(): void {
		$s = $this->settings();
		// Only show the bar when consent is required and not yet decided.
		if ( empty( $s['require_consent'] ) || isset( $_COOKIE[ self::COOKIE ] ) ) {
			return;
		}
		$text = esc_html( (string) $s['consent_text'] );
		?>
		<div id="nurax-consent-bar" style="position:fixed;left:0;right:0;bottom:0;z-index:99999;background:#2b2b2b;color:#fff;padding:14px 18px;font-family:Arial,Helvetica,sans-serif;font-size:14px;display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:center;">
			<span style="max-width:640px;line-height:1.4;"><?php echo $text; ?></span>
			<span style="display:flex;gap:8px;">
				<button type="button" id="nurax-consent-accept" style="background:#b76e79;color:#fff;border:0;border-radius:8px;padding:9px 18px;font-weight:700;cursor:pointer;"><?php esc_html_e( 'Accept', 'nura-experience' ); ?></button>
				<button type="button" id="nurax-consent-decline" style="background:transparent;color:#fff;border:1px solid #666;border-radius:8px;padding:9px 18px;cursor:pointer;"><?php esc_html_e( 'Decline', 'nura-experience' ); ?></button>
			</span>
		</div>
		<script>
		(function(){
			function setC(v){var d=new Date();d.setTime(d.getTime()+180*24*60*60*1000);document.cookie='<?php echo esc_js( self::COOKIE ); ?>='+v+';expires='+d.toUTCString()+';path=/;SameSite=Lax';}
			var bar=document.getElementById('nurax-consent-bar');
			var a=document.getElementById('nurax-consent-accept');
			var x=document.getElementById('nurax-consent-decline');
			if(a){a.addEventListener('click',function(){setC('1');if(bar){bar.style.display='none';}location.reload();});}
			if(x){x.addEventListener('click',function(){setC('0');if(bar){bar.style.display='none';}});}
		})();
		</script>
		<?php
	}

	/* ------------------------------------------------------------- admin page */

	public function menu(): void {
		add_submenu_page(
			'woocommerce',
			__( 'NURA Retargeting', 'nura-experience' ),
			__( 'NURA Retargeting', 'nura-experience' ),
			'manage_woocommerce',
			'nurax-retargeting',
			array( $this, 'render' )
		);
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$s   = $this->settings();
		$opt = self::OPTION;
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'NURA Retargeting', 'nura-experience' ); ?></h1>
			<p class="description"><?php esc_html_e( 'Build remarketing audiences from anonymous visitors - the shoppers you cannot email. Enter a Meta Pixel and/or a Google tag, and NURA fires the standard product-view, add-to-cart, checkout and purchase events so you can run "came back" ads. Tags only load after the visitor accepts advertising cookies.', 'nura-experience' ); ?></p>

			<form method="post" action="options.php">
				<?php settings_fields( 'nurax_retargeting_group' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable retargeting', 'nura-experience' ); ?></th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( $opt ); ?>[enabled]" value="1" <?php checked( ! empty( $s['enabled'] ) ); ?> /> <?php esc_html_e( 'Fire retargeting tags on the storefront', 'nura-experience' ); ?></label></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Meta Pixel ID', 'nura-experience' ); ?></th>
						<td><input type="text" name="<?php echo esc_attr( $opt ); ?>[meta_pixel_id]" value="<?php echo esc_attr( (string) $s['meta_pixel_id'] ); ?>" class="regular-text" placeholder="e.g. 123456789012345" /> <span class="description"><?php esc_html_e( 'From Meta Events Manager.', 'nura-experience' ); ?></span></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Google Analytics 4 ID', 'nura-experience' ); ?></th>
						<td><input type="text" name="<?php echo esc_attr( $opt ); ?>[ga4_id]" value="<?php echo esc_attr( (string) $s['ga4_id'] ); ?>" class="regular-text" placeholder="G-XXXXXXXXXX" /></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Google Ads conversion ID', 'nura-experience' ); ?></th>
						<td><input type="text" name="<?php echo esc_attr( $opt ); ?>[google_ads_id]" value="<?php echo esc_attr( (string) $s['google_ads_id'] ); ?>" class="regular-text" placeholder="AW-XXXXXXXXXX" /> <span class="description"><?php esc_html_e( 'Optional. Needed only for Google Ads remarketing / conversion tracking.', 'nura-experience' ); ?></span></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Google Ads purchase label', 'nura-experience' ); ?></th>
						<td><input type="text" name="<?php echo esc_attr( $opt ); ?>[ads_purchase_lbl]" value="<?php echo esc_attr( (string) $s['ads_purchase_lbl'] ); ?>" class="regular-text" /> <span class="description"><?php esc_html_e( 'The conversion label for purchases (paired with the Ads ID above).', 'nura-experience' ); ?></span></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Cookie consent', 'nura-experience' ); ?></th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( $opt ); ?>[require_consent]" value="1" <?php checked( ! empty( $s['require_consent'] ) ); ?> /> <?php esc_html_e( 'Require advertising-cookie consent before loading tags (recommended)', 'nura-experience' ); ?></label>
						<p class="description"><?php esc_html_e( 'Shows a simple accept/decline bar. If you already run a consent-management plugin, leave this off and hook the nurax_retargeting_consent filter to your plugin instead.', 'nura-experience' ); ?></p></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Consent bar text', 'nura-experience' ); ?></th>
						<td><input type="text" name="<?php echo esc_attr( $opt ); ?>[consent_text]" value="<?php echo esc_attr( (string) $s['consent_text'] ); ?>" class="large-text" /></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
