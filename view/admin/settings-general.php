<?php
/**
 * General settings tab content
 *
 * @package Auglio_Try_On_Mirror
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2><?php echo esc_html__( 'General Settings', 'auglio-try-on-mirror' ); ?></h2>
<form action="<?php echo esc_url( $form_action ); ?>" method="post" id="auglio_settings_form" >
	<input type="hidden" name="action" value="auglio_settings_response">
	<input type="hidden" name="auglio_settings_nonce" value="<?php echo esc_attr( wp_create_nonce( 'auglio_settings_form_nonce' ) ); ?>"/>
	<table class="form-table" aria-label="<?php echo esc_attr__( 'General Settings', 'auglio-try-on-mirror' ); ?>">
		<tbody>
			<tr>
				<th scope="row">
					<?php echo esc_html__( 'Virtual Mirror', 'auglio-try-on-mirror' ); ?>
				</th>
				<td>
					<fieldset>
						<label for="auglio-only_wc_pages">
							<input type="checkbox" <?php checked( 1 === (int) $data['only_wc_pages'] ); ?> name="auglio_settings[only_wc_pages]" id="auglio-only_wc_pages" value="1">
							<?php echo esc_html__( 'Show Virtual Mirror only on WooCommerce pages', 'auglio-try-on-mirror' ); ?>
						</label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row">
				<?php echo esc_html__( '"Try On" button visibility', 'auglio-try-on-mirror' ); ?>
				</th>
				<td>
					<fieldset>
						<label for="auglio-tryon_show_catalog_page">
							<input type="checkbox" <?php checked( 1 === (int) $data['tryon_show_catalog_page'] ); ?> name="auglio_settings[tryon_show_catalog_page]" id="auglio-tryon_show_catalog_page" value="1">
							<?php echo esc_html__( 'Show "Try On" buttons on catalog page', 'auglio-try-on-mirror' ); ?>
						</label>
					</fieldset>
					<fieldset>
						<label for="auglio-tryon_show_product_page">
							<input type="checkbox" <?php checked( 1 === (int) $data['tryon_show_product_page'] ); ?> name="auglio_settings[tryon_show_product_page]" id="auglio-tryon_show_product_page" value="1">
							<?php echo esc_html__( 'Show "Try On" button on product page', 'auglio-try-on-mirror' ); ?>
						</label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="auglio-tryon_position_catalog_page">
						<?php echo esc_html__( '"Try On" button position on catalog page', 'auglio-try-on-mirror' ); ?>	
					</label>
				</th>
				<td>
					<select name="auglio_settings[tryon_position_catalog_page]" id="auglio-tryon_position_catalog_page">
						<?php foreach ( $catalog_page_positions as $position_id => $position_name ) : ?>
						<option value="<?php echo esc_attr( $position_id ); ?>" <?php selected( $position_id === $data['tryon_position_catalog_page'] ); ?>>
							<?php echo esc_html( $position_name ); ?>
						</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="auglio-tryon_position_product_page">
					<?php echo esc_html__( '"Try On" button position on product page', 'auglio-try-on-mirror' ); ?>
						</label>
					</fieldset>
					</label>
				</th>
				<td>
					<select name="auglio_settings[tryon_position_product_page]" id="auglio-tryon_position_product_page">
						<?php foreach ( $product_page_positions as $position_id => $position_name ) : ?>
						<option value="<?php echo esc_attr( $position_id ); ?>" <?php selected( $position_id === $data['tryon_position_product_page'] ); ?>>
							<?php echo esc_html( $position_name ); ?>
						</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="auglio-tryon_text">
					<?php echo esc_html__( '"Try On" button text', 'auglio-try-on-mirror' ); ?>
					</label>
				</th>
				<td>
					<input type="text" name="auglio_settings[tryon_text]" id="auglio-tryon_text" class="regular-text" value="<?php echo esc_attr( $data['tryon_text'] ); ?>">
				</td>
			</tr>
		</tbody>
	</table>
	<?php submit_button(); ?>
</form>