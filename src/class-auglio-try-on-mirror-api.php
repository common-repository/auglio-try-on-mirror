<?php
/**
 * Class Auglio_Try_On_Mirror_Api
 *
 * @package Auglio_Try_On_Mirror
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Auglio_Try_On_Mirror_Api
 */
class Auglio_Try_On_Mirror_Api {

	/**
	 * API base URL
	 *
	 * @var string
	 */
	private $api_base_url = 'https://api2.auglio.com/';

	/**
	 * Login to the API
	 *
	 * @param string $public_api_key Public API key.
	 * @param string $private_api_key Private API key.
	 *
	 * @return array
	 */
	public function login( $public_api_key, $private_api_key ) {
		$args = array(
			'headers' => array(
				'Content-type: application/x-www-form-urlencoded',
			),
			'body'    => array(
				'public_api_key'  => $public_api_key,
				'private_api_key' => $private_api_key,
			),
		);
		return $this->http_post( 'auth/login', $args );
	}

	/**
	 * Logout from the API
	 *
	 * @return array
	 */
	public function logout() {
		$args = $this->get_headers();
		return $this->http_get( 'auth/logout', $args );
	}

	/**
	 * Get settings from the API
	 *
	 * @return array
	 */
	public function get_settings() {
		$args     = $this->get_headers();
		$response = $this->http_get( 'settings', $args );

		if ( 200 === (int) $response['http_code'] ) {
			$settings                      = $response['body']['settings'];
			$auglio_settings               = get_option( 'auglio_settings' );
			$auglio_settings['tryon_text'] = $settings['tryon_text'];
			$auglio_settings['automirror'] = $settings['automirror'];
			update_option( 'auglio_settings', $auglio_settings );
		} else {

			if ( isset( $response['body']['message'] ) ) {
				$error_message = $response['body']['message'];
			} else {
				$error_message = 'Auglio API - Unknown Error';
			}
			return array(
				'success' => false,
				'message' => $error_message,
			);
		}
	}

	/**
	 * Post settings to the API
	 *
	 * @param array $data Settings data.
	 *
	 * @return array
	 */
	public function post_settings( $data ) {
		$args         = $this->get_headers();
		$args['body'] = $data;
		return $this->http_post( 'settings', $args );
	}

	/**
	 * Get product from the API
	 *
	 * @param int $id Product ID.
	 *
	 * @return array
	 */
	public function get_product( $id ) {
		$args = $this->get_headers();
		return $this->http_get( 'products/' . $id, $args );
	}

	/**
	 * Post product to the API
	 *
	 * @param int   $id Product ID.
	 * @param array $data Product data.
	 *
	 * @return array
	 */
	public function post_product( $id, $data ) {
		$args         = $this->get_headers();
		$args['body'] = $data;
		return $this->http_post( 'products/' . $id, $args );
	}

	/**
	 * Post user data to the API
	 *
	 * @param array $data User data.
	 * @return array
	 */
	public function post_user( $data ) {
		$args         = $this->get_headers();
		$args['body'] = $data;
		return $this->http_post( 'user', $args );
	}

	/**
	 * Get user from the API
	 *
	 * @return array
	 */
	private function get_access_token() {
		$auglio_api   = get_option( 'auglio_api' );
		$access_token = $auglio_api['access_token'];

		if( empty( $access_token ) ) {
			return false;
		}

		list( $header, $payload, $signature ) = explode( '.', $access_token );
		// payload from jwt token.
		$payload = json_decode( base64_decode( $payload ) );

		if ( $payload->exp <= time() ) {
			return $this->refresh_token( $auglio_api );
		} else {
			return $access_token;
		}
	}

	/**
	 * Refresh the access token
	 *
	 * @param array $auglio_api Auglio API data.
	 *
	 * @return string|bool
	 */
	public function refresh_token( $auglio_api ) {
		$refresh_token = $auglio_api['refresh_token'];

		list( $refresh_header, $refresh_payload, $refresh_signature ) = explode( '.', $refresh_token );
		// payload from jwt token.
		$refresh_payload = json_decode( base64_decode( $refresh_payload ) );
		if ( $refresh_payload->exp <= time() ) {
			return false;
		}
		$args     = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $refresh_token,
			),
		);
		$response = $this->http_get( 'auth/refresh_token', $args );

		if ( 200 !== (int) $response['http_code'] ) {
			return false;
		}
		$auglio_api['refresh_token'] = $response['body']['refresh_token'];
		$auglio_api['access_token']  = $response['body']['access_token'];

		update_option( 'auglio_api', $auglio_api );
		return $auglio_api['access_token'];
	}

	/**
	 * Get headers
	 *
	 * @return array
	 */
	private function get_headers() {
		$access_token = $this->get_access_token();
		return array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
			),
		);
	}

	/**
	 * HTTP GET request
	 *
	 * @param string $path Path.
	 * @param array  $args Arguments.
	 *
	 * @return array
	 */
	private function http_get( $path, $args ) {
		$response = wp_remote_get( $this->api_base_url . $path, $args );
		return $this->process_response( $response );
	}

	/**
	 * HTTP POST request
	 *
	 * @param string $path Path.
	 * @param array  $args Arguments.
	 *
	 * @return array
	 */
	private function http_post( $path, $args ) {
		$response = wp_remote_post( $this->api_base_url . $path, $args );
		return $this->process_response( $response );
	}

	/**
	 * Process response
	 *
	 * @param mixed $response Response.
	 *
	 * @return array
	 */
	private function process_response( $response ) {
		if ( is_wp_error( $response ) ) {
			return array(
				'http_code' => 0,
				'body'      => $response->get_error_message(),
			);
		} else {
			return array(
				'http_code' => wp_remote_retrieve_response_code( $response ),
				'body'      => json_decode( wp_remote_retrieve_body( $response ), true ),
			);
		}
	}
}
