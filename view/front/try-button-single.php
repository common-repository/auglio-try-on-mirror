<?php
/**
 * This view adds the try on button to the product page
 *
 * @package Auglio_Try_On_Mirror
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo '<button type="button" 
	class="button auglio-tryon-btn auglio-tryon-btn-product-page" 
	style="display: none;" 
	data-item_id="' . esc_attr( $product_id ) . '">' . esc_html( $tryon_text ) . '</button>';
