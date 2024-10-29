<?php
/**
 * Plugin Name: Auglio Try-on Mirror
 * Plugin URI: http://wordpress.org/plugins/auglio-try-on-mirror/
 * Description: This plugin allows to quickly install Auglio Try-on Mirror on any WooCommerce website.
 * Version: 1.0.1
 * WC requires at least: 3.0.0
 * WC tested up to: 7.8.0
 * Author: Auglio
 * Author URI: https://auglio.com
 * Text Domain: auglio-try-on-mirror
 * Copyright: © 2023 Auglio.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Auglio_Try_On_Mirror
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'AUGLIO_TRY_ON_MIRROR_VERSION' ) ) {
	define( 'AUGLIO_TRY_ON_MIRROR_VERSION', '1.0.1' );
}
if ( ! defined( 'AUGLIO_BASE_PATH' ) ) {
	define( 'AUGLIO_BASE_PATH', plugin_dir_path( __FILE__ ) );
}

/**
 * Activation hook
 *
 * This function is called when the plugin is activated
 *
 * @return void
 */
function auglio_try_on_mirror_activation() {

	update_option( 'auglio_try_on_mirror_version', AUGLIO_TRY_ON_MIRROR_VERSION );

	$installation_id = get_option( 'auglio_installation_id' );
	if ( ! $installation_id ) {
		$installation_id = wp_generate_uuid4();
		update_option( 'auglio_installation_id', $installation_id );
	}

	$default_auglio_settings = array(
		'automirror'                  => 0,
		'only_wc_pages'               => 0,
		'tryon_text'                  => '',
		'tryon_show_catalog_page'     => 1,
		'tryon_position_catalog_page' => 'after_shop_loop_item',
		'tryon_show_product_page'     => 1,
		'tryon_position_product_page' => 'after_add_to_cart_button',
	);

	$auglio_settings = get_option( 'auglio_settings', array() );

	update_option( 'auglio_settings', array_merge( $default_auglio_settings, $auglio_settings ) );

	$default_auglio_api = array(
		'public_api_key' => '',
		'access_token'   => '',
		'refresh_token'  => '',
		'user_id'        => '',
	);

	$auglio_api = get_option( 'auglio_api', array() );

	update_option( 'auglio_api', array_merge( $default_auglio_api, $auglio_api ) );

	$default_auglio_product_feed = array(
		'status'           => 0,
		'post_statuses'    => array( 'publish' ),
		'default_category' => 0,
		'default_gender'   => 'U',
		'categories'       => array(),
		'genders'          => array(),
		'export'           => array(),
	);

	$auglio_product_feed = get_option( 'auglio_product_feed', array() );
	update_option( 'auglio_product_feed', array_merge( $default_auglio_product_feed, $auglio_product_feed ) );
	global $wp_version;
	$auglio_api = get_option( 'auglio_api', array() );
	require_once __DIR__ . '/src/class-auglio-try-on-mirror-api.php';
	$api = new Auglio_Try_On_Mirror_Api();
	$api->post_user(
		array(
			'plugin_version'    => AUGLIO_TRY_ON_MIRROR_VERSION,
			'domain'            => get_site_url(),
			'wordpress_version' => $wp_version,
			'updated_at'        => gmdate( 'Y-m-d H:i:s' ),
			'partner_id'        => $auglio_api['user_id'],
			'plugin'            => 'wordpress',
			'id'                => $installation_id,
		)
	);
}

register_activation_hook( __FILE__, 'auglio_try_on_mirror_activation' );

/**
 * Check version
 *
 * This function checks if the plugin version has changed
 *
 * @return void
 */
function auglio_check_version() {
	if ( AUGLIO_TRY_ON_MIRROR_VERSION !== get_option( 'auglio_try_on_mirror_version' ) ) {
		auglio_try_on_mirror_activation();
	}
}

add_action( 'plugins_loaded', 'auglio_check_version' );

// Makes sure the plugin is defined before trying to use it.
$need = false;

if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
	require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

// multisite && this plugin is locally activated - Woo can be network or locally activated.
if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
	// this plugin is network activated - Woo must be network activated.
	$need = is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ? false : true;
	// this plugin runs on a single site || is locally activated.
} else {
	$need = is_plugin_active( 'woocommerce/woocommerce.php' ) ? false : true;
}

if ( true === $need ) {
	wp_die(
		esc_html__( 'Sorry, but this plugin requires the WooCommerce Plugin to be installed and active.' ) .
		'<br><a href="' . esc_url( admin_url( 'plugins.php' ) ) .
		'">&laquo; ' . esc_html__( 'Return to Plugins' ) . '</a>'
	);
	return;
} else {
	// Check if there is admin user.
	if ( is_admin() ) {
		require_once __DIR__ . '/src/class-auglio-try-on-mirror-admin.php';
		$auglio_try_on_mirror = new Auglio_Try_On_Mirror_Admin();
	} else {
		require_once __DIR__ . '/src/class-auglio-try-on-mirror.php';
		$auglio_try_on_mirror = new Auglio_Try_On_Mirror();
	}

	$auglio_try_on_mirror->init();
}


add_action( 'admin_init', 'auglio_has_woocommerce' );

/**
 * Check if WooCommerce is active
 *
 * This function checks if WooCommerce is active and if not, deactivates the plugin
 *
 * @return void
 */
function auglio_has_woocommerce() {
	if ( is_admin() && current_user_can( 'activate_plugins' )
		&& ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		add_action( 'admin_notices', 'auglio_notice' );
		deactivate_plugins( plugin_basename( __FILE__ ) );

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
}

add_action( 'init', 'auglio_update_settings' );

/**
 * Update settings
 *
 * This function updates the settings
 *
 * @return void
 */
function auglio_update_settings() {
	if ( isset( $_GET['auglio_settings_update'] ) ) {
		require_once __DIR__ . '/src/class-auglio-try-on-mirror-api.php';
		$auglio_api = new Auglio_Try_On_Mirror_Api();
		$auglio_api->get_settings();
	}
}

add_action( 'init', 'auglio_xml_product_feed' );

/**
 * Generate feed
 *
 * This function generates the feed
 *
 * @return void
 */
function auglio_xml_product_feed() {
	if ( isset( $_GET['auglio_xml_product_feed'] ) ) {
		require_once __DIR__ . '/src/class-auglio-try-on-mirror-feed.php';
		$feed = new Auglio_Try_On_Mirror_Feed();
		$feed->generate_feed();
		die();
	}
}

/**
 * WooCommerce not active notice
 *
 * This function displays a notice if WooCommerce is not active
 *
 * @return void
 */
function auglio_notice() {
	echo '<div class="error"><p>' . esc_html__(
		'Sorry, but Auglio Try-on Mirror requires WooCommerce version 3.0.0 or above to be installed and active.',
		'auglio-try-on-mirror'
	) . '</p></div>';
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'auglio_add_action_links' );

/**
 * Add action links
 *
 * This function adds action links to the plugin
 *
 * @param array $links The current links.
 *
 * @return array
 */
function auglio_add_action_links( $links ) {
	$mylinks = array(
		'<a href="' . admin_url( 'admin.php?page=auglio' ) . '">Settings</a>',
	);

	return array_merge( $mylinks, $links );
}

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );