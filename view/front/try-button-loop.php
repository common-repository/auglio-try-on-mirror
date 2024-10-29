<?php
/**
 * This view adds the try on button to the catalog page loop
 *
 * @package Auglio_Try_On_Mirror
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo '
<div class="auglio-tryon-btn-wrapper">
	<button 
		class="button auglio-tryon-btn auglio-tryon-btn-catalog auglio-try-on-mirror" 
		style="display: none;" 
		data-item_id="' . esc_attr( $product_id ) . '">
		' . esc_html( $tryon_text ) . '
	</button>
</div>';
