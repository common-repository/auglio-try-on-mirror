<?php
/**
 * Auglio Try-on Mirror
 *
 * @package Auglio_Try_On_Mirror
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'interface-auglio-try-on-mirror.php';

/**
 * Auglio_Try_On_Mirror class
 */
class Auglio_Try_On_Mirror implements Auglio_Try_On_Mirror_Interface {

	/**
	 * Plugin version
	 *
	 * @var string plugin_version
	 */
	private $plugin_version = AUGLIO_TRY_ON_MIRROR_VERSION;

	/**
	 * API settings
	 *
	 * @var array
	 */
	protected $api;

	/**
	 * Plugin settings
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * Auglio_Try_On_Mirror constructor.
	 */
	public function __construct() {

		$this->api      = get_option( 'auglio_api' );
		$this->settings = get_option( 'auglio_settings' );
		if ( ! isset( $this->settings['automirror'] ) ) {
			$this->settings['automirror'] = 0;
		}
	}

	/**
	 * Init function
	 *
	 * This function sets up the base actions
	 *
	 * @return void
	 */
	public function init() {
		if ( ! $this->settings['automirror'] ) {
			// Add the try button to the product page.
			if ( 1 === $this->settings['tryon_show_product_page'] ) {
				$action = 'woocommerce_' . $this->settings['tryon_position_product_page'];
				add_action( $action, array( $this, 'show_try_button_single' ), 20 );
			}
			// Add the try button to the catalog page.
			if ( 1 === $this->settings['tryon_show_catalog_page'] ) {
				$action = 'woocommerce_' . $this->settings['tryon_position_catalog_page'];
				add_action( $action, array( $this, 'show_try_button_loop' ), 20 );
			}
		}
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts_styles' ) );
	}

	/**
	 * Load scripts and styles
	 *
	 * This function loads the scripts and styles
	 *
	 * @return void
	 */
	public function load_scripts_styles() {
		// Load the script only on WooCommerce pages or on the front page if the option is enabled.
		if ( ( is_woocommerce() || ( ! $this->settings['only_wc_pages'] && is_front_page() ) ) && $this->api ) {
			wp_enqueue_script(
				'auglio-automirror',
				'//m.auglio.com/' . $this->api['public_api_key'],
				array(),
				$this->plugin_version,
				true
			);
		}
	}

	/**
	 * Show try button on single product page
	 *
	 * @return void
	 */
	public function show_try_button_single() {
		$this->show_try_button( 'try-button-single' );
	}

	/**
	 * Show try button on catalog page
	 *
	 * @return void
	 */
	public function show_try_button_loop() {
		$this->show_try_button( 'try-button-loop' );
	}

	/**
	 * Show try button
	 *
	 * @param string $view View name.
	 *
	 * @return void
	 */
	private function show_try_button( $view ) {
		global $product;
		$this->render(
			'front/' . $view . '.php',
			array(
				'product_id' => $product->get_id(),
				'tryon_text' => $this->settings['tryon_text'] ? $this->settings['tryon_text'] : 'TRY ON',
			)
		);
	}

	/**
	 * Recursive sanitation for text or array
	 * 
	 * @param $array_or_string (array|string)
	 * @since  0.1
	 * @return mixed
	 */
	protected function sanitize_text_or_array_field( $array_or_string ) {
		if ( is_string($array_or_string) ) {
			$array_or_string = sanitize_text_field( $array_or_string );
		} elseif ( is_array( $array_or_string ) ) {
			foreach ( $array_or_string as $key => &$value ) {
				if ( is_array( $value ) ) {
					$value = $this->sanitize_text_or_array_field( $value );
				} else {
					$value = sanitize_text_field( wp_unslash( $value ) );
				}
			}
		}

		return $array_or_string;
	}

	/**
	 * Render template
	 *
	 * @param string $template_name Template name.
	 * @param array  $parameters    Parameters.
	 * @param bool   $render_output Render output.
	 *
	 * @return void|string
	 */
	public function render( $template_name, array $parameters = array(), $render_output = true ) {
		foreach ( $parameters as $name => $value ) {
			${$name} = $value;
		}
		ob_start();
		include AUGLIO_BASE_PATH . '/view/' . $template_name;
		$output = ob_get_contents();
		ob_end_clean();

		if ( $render_output ) {
			echo $output;
		} else {
			return $output;
		}
	}
}
