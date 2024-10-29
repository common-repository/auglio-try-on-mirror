<?php
/**
 * Auglio Try-on Mirror Admin
 *
 * @package Auglio_Try_On_Mirror
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'interface-auglio-try-on-mirror.php';
require_once 'class-auglio-try-on-mirror.php';
require_once 'class-auglio-try-on-mirror-api.php';
/**
 * Auglio_Try_On_Mirror_Admin class for setting auglio settings.
 */
class Auglio_Try_On_Mirror_Admin extends Auglio_Try_On_Mirror implements Auglio_Try_On_Mirror_Interface {

	/**
	 * Set up base actions.
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

		add_action( 'admin_post_auglio_api_login_response', array( $this, 'api_login_response' ) );
		add_action( 'admin_post_auglio_api_logout_response', array( $this, 'api_logout_response' ) );
		add_action( 'admin_post_auglio_settings_response', array( $this, 'settings_response' ) );
		add_action( 'admin_post_auglio_product_feed_response', array( $this, 'product_feed_response' ) );
	}

	/**
	 * Add plugin to woocommerce admin menu.
	 */
	public function admin_menu() {
		add_submenu_page( 'woocommerce', 'Auglio', 'Auglio', 'manage_options', 'auglio', array( $this, 'admin_pages' ) );
	}

	// region META BOX.
	/**
	 * Adds the meta box container.
	 *
	 * @param string $post_type post type.
	 */
	public function add_meta_box( $post_type ) {

		if ( ! $this->api ) {
			return;
		}
		// Limit meta box to certain post types.
		$post_types = array( 'product' );

		if ( in_array( $post_type, $post_types, true ) ) {
			add_meta_box(
				'auglio_product_meta_box_name',
				'Auglio',
				array( $this, 'render_meta_box_content' ),
				$post_type,
				'side',
				'high'
			);
		}
	}

	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post ) {

		if ( ! $this->api ) {
			return;
		}
		global $woocommerce;
		$product_id = get_the_ID();
		$product    = wc_get_product( $product_id );
		$query_data = array(
			'id'   => $product_id,
			'name' => $product->get_name(),
			'url'  => get_permalink( $product_id ),
		);

		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'single-post-thumbnail' );
		if ( $image ) {
			$query_data['img'] = $image[0];
		}
		$auglio_api    = new Auglio_Try_On_Mirror_Api();
		$response      = $auglio_api->get_product( $product_id );
		$in_auglio_db  = false;
		$published     = null;
		$api_connected = ! empty( $response );

		if ( 200 === (int) $response['http_code'] ) {
			$in_auglio_db  = true;
			$published     = $response['body']['product']['published'];
		}
		
		$this->render(
			'admin/product-meta-box.php',
			array(
				'in_auglio_db' => $in_auglio_db,
				'published'    => $published,
				'query_data'   => http_build_query( $query_data ),
				'api_connected'=> $api_connected,
			)
		);
	}
	/**
	 * Admin pages
	 *
	 * This method shows the configuration pages of the plugin in the admin
	 */
	public function admin_pages() {
		$tabs = array( 'settings', 'product_feed', 'api' );
		
		if ( isset( $_GET['tab'] ) && in_array( $_GET['tab'], $tabs, true ) ) {
			$tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) );
		} else {
			$tab = 'settings';
		}
		$response  = isset( $_GET['response'] ) ? sanitize_text_field( wp_unslash( $_GET['response'] ) ) : null;
		$title     = esc_html__( 'Auglio Settings', 'auglio-try-on-mirror' );
		$site_url  = home_url();
		$admin_url = admin_url();
		$this->render(
			'admin/settings-content.php',
			array(
				'api_logged_in'          => (bool) $this->api['access_token'],
				'tab'                    => $tab,
				'response'               => $response,
				'title'                  => $title,
				'site_url'               => $site_url,
				'admin_url'              => $admin_url,
				'auglio_categories'      => $this->get_auglio_categories(),
				'auglio_genders'         => $this->get_auglio_genders(),
				'store_categories'       => $this->get_store_categories(),
				'product_page_positions' => $this->get_product_page_positions(),
				'catalog_page_positions' => $this->get_catalog_page_positions(),
				'data'                   => get_option( 'auglio_' . $tab ),
			)
		);
	}
	/**
	 * API login response
	 *
	 * This method processes the submitted API login form
	 */
	public function api_login_response() {
		$this->verify_nonce( 'api_login' );

		$error_message   = null;
		$public_api_key  = isset( $_POST['auglio']['public_api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['auglio']['public_api_key'] ) ) : null;
		$private_api_key = isset( $_POST['auglio']['private_api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['auglio']['private_api_key'] ) ) : null;
		$auglio_api      = new Auglio_Try_On_Mirror_Api();
		$response        = $auglio_api->login( $public_api_key, $private_api_key );

		if ( 200 === (int) $response['http_code'] ) {

			update_option(
				'auglio_api',
				array(
					'public_api_key' => $public_api_key,
					'access_token'   => $response['body']['access_token'],
					'refresh_token'  => $response['body']['refresh_token'],
					'user_id'        => $response['body']['user_id'],
				)
			);
			$response = $auglio_api->get_settings();
			if ( ! $response['success'] ) {
					$error_message = $response['message'];
			}
		} elseif ( isset( $response['body']['message'] ) ) {
			$error_message = $response['body']['message'];
		} elseif ( isset( $response['body']['private_api_key'] ) ) {
			$error_message = $response['body']['private_api_key'];
		}
		$this->custom_redirect( 'api', $error_message );
	}

	/**
	 * API logout response
	 *
	 * This method logs out the user after they click the disconnect button
	 */
	public function api_logout_response() {
		$this->verify_nonce( 'api_logout' );
		$error_message = null;
		$auglio_api    = new Auglio_Try_On_Mirror_Api();
		$response      = $auglio_api->logout();

		delete_option( 'auglio_api' );
		if ( 200 === (int) $response['http_code'] ) {
			delete_option( 'auglio_api' );
		} elseif ( isset( $response['body']['message'] ) ) {
			$error_message = $response['body']['message'];
		}
		$this->custom_redirect( 'api', $error_message );
	}

	/**
	 * Settings response
	 *
	 * This method processes the submitted settings form
	 */
	public function settings_response() {
		$this->verify_nonce( 'settings' );

		$auglio_settings = get_option( 'auglio_settings' );

		$post_keys = array(
			'tryon_text',
			'only_wc_pages',
			'tryon_position_catalog_page',
			'tryon_position_product_page',
			'tryon_show_catalog_page',
			'tryon_show_product_page',
		);
		foreach ( $post_keys as $key ) {
			$auglio_settings[ $key ]
				= isset( $_POST['auglio_settings'][ $key ] )
				? sanitize_text_field( wp_unslash( $_POST['auglio_settings'][ $key ] ) )
				: null;
		}

		update_option( 'auglio_settings', $auglio_settings );

		$auglio_api = new Auglio_Try_On_Mirror_Api();

		$data = array(
			'tryon_text' => $auglio_settings['tryon_text'],
		);

		$response = $auglio_api->post_settings( $data );

		$error_message = null;

		if ( isset( $response['body']['message'] ) && 200 !== (int) $response['http_code'] ) {
			$error_message = $response['body']['message'];
		}
		$this->custom_redirect( 'settings', $error_message );
	}

	/**
	 * Product feed response
	 *
	 * This method processes the submitted product feed form
	 */
	public function product_feed_response() {
		$this->verify_nonce( 'product_feed' );
		if( isset( $_POST['auglio_product_feed'] ) ) {
			update_option( 'auglio_product_feed', $this->sanitize_text_or_array_field ( $_POST['auglio_product_feed'] ) );
		}
		$this->custom_redirect( 'product_feed' );
	}

	/**
	 * Custom redirect
	 *
	 * This method redirects the user to the settings page with a message
	 *
	 * @param string $tab     The tab to redirect to.
	 * @param string $message The message to display.
	 */
	private function custom_redirect( $tab, $message = null ) {
		wp_safe_redirect(
			esc_url_raw(
				add_query_arg(
					array(
						'response' => $message ? $message : 'success',
					),
					admin_url( 'admin.php?page=auglio&tab=' . $tab )
				)
			)
		);
	}

	/**
	 * Verify nonce
	 *
	 * This method verifies the nonce of the submitted form
	 *
	 * @param string $name The name of the form.
	 */
	private function verify_nonce( $name ) {
		if ( ! isset( $_POST[ 'auglio_' . $name . '_nonce' ] )
			|| ! wp_verify_nonce(
				sanitize_text_field(
					wp_unslash( $_POST[ 'auglio_' . $name . '_nonce' ] )
				),
				'auglio_' . $name . '_form_nonce'
			)
		) {
			die( 'Nonce not verified' );
		}
	}

	/**
	 * Get store categories
	 *
	 * This method gets the store categories
	 *
	 * @return array
	 */
	private function get_store_categories() {

		$product_categories = get_terms( 'product_cat' );

		$sorted_categories = array();
		foreach ( $product_categories as $product_category ) {
			$ancestors = get_term_parents_list(
				$product_category->term_id,
				'product_cat',
				array(
					'separator' => ' > ',
					'link'      => false,
					'inclusive' => false,
				)
			);
			$sorted_categories[ $ancestors . $product_category->name ] = $product_category;
		}
		ksort( $sorted_categories );

		return $sorted_categories;
	}

	/**
	 * Get Auglio Genders
	 *
	 * This method gets the Auglio system's genders
	 *
	 * @return array
	 */
	private function get_auglio_genders() {
		return array(
			'W' => 'Women',
			'M' => 'Men',
			'U' => 'Unisex',
			'B' => 'Boys',
			'G' => 'Girls',
			'K' => 'Kids Unisex',
		);
	}

	/**
	 * Get Auglio Categories
	 *
	 * This method gets the Auglio system's categories
	 *
	 * @return array
	 */
	private function get_auglio_categories() {
		return array(
			'eyewear'     => array(
				10 => 'Contact lenses',
				11 => 'Glasses',
				16 => 'Sunglasses',
			),
			'cosmetics'   => array(
				6  => 'Blush',
				12 => 'Concealer',
				7  => 'Eye liner',
				8  => 'Eye shadow',
				14 => 'Facelift',
				4  => 'Foundation',
				9  => 'Hair color',
				1  => 'Lashes',
				5  => 'Lip Gloss',
				3  => 'Lipliner',
				2  => 'Lipstick',
				13 => 'Self tanning solution',
				15 => 'Teethwhitening',
			),
			'jewellery'   => array(
				19 => 'Earrings',
				20 => 'Necklaces',
				21 => 'Piercing',
			),
			'accessories' => array(
				17 => 'Hats',
				18 => 'Scarves',
			),
		);
	}

	/**
	 * Get catalog page positions
	 *
	 * This method gets the available hooks on the catalog page
	 *
	 * @return array
	 */
	private function get_catalog_page_positions() {
		return array(
			'before_shop_loop_item'       => 'Before Shop Loop Item',
			'before_shop_loop_item_title' => 'Before Shop Loop Item Title',
			'shop_loop_item_title'        => 'Shop Loop Item Title',
			'after_shop_loop_item_title'  => 'After Shop Loop Item Title',
			'after_shop_loop_item'        => 'After Shop Loop Item',
		);
	}

	/**
	 * Get product page positions
	 *
	 * This method gets available hooks on the product page
	 *
	 * @return array
	 */
	private function get_product_page_positions() {
		return array(
			'before_add_to_cart_form'   => 'Before Add To Cart Form',
			'before_variations_form'    => 'Before Variations Form',
			'before_add_to_cart_button' => 'Before Add To Cart Button',
			'before_single_variation'   => 'Before Single Variation',
			'after_single_variation'    => 'After Single Variation',
			'after_add_to_cart_button'  => 'After Add To Cart Button',
			'after_variations_form'     => 'After Variations Form',
			'after_add_to_cart_form'    => 'After Add To Cart Form',
			'product_meta_start'        => 'Product Meta Start',
			'product_meta_end'          => 'Product Meta End',
			'product_thumbnails'        => 'Product Thumbnails',
		);
	}
}