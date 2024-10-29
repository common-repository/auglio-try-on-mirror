<?php
/**
 * API Connection settings page contents
 *
 * @package Auglio_Try_On_Mirror
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2><?php esc_html_e( 'Auglio API Connection', 'auglio-try-on-mirror' ); ?></h2>
<?php if ( ! empty( $data['refresh_token'] ) ) : ?>
<p>
	<?php esc_html_e( 'You are connected to Auglio API with API key', 'auglio-try-on-mirror' ); ?>: 
	<strong><?php echo esc_html( $data['public_api_key'] ); ?></strong>
</p>
<form action="<?php echo esc_url( $form_action ); ?>" method="post" id="auglio_api_logout_form">
	<input type="hidden" name="action" value="auglio_api_logout_response">
	<input type="hidden" name="auglio_api_logout_nonce" value="<?php echo esc_attr( wp_create_nonce( 'auglio_api_logout_form_nonce' ) ); ?>"/>
	<p class="submit">
		<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr__( 'Disconnect', 'auglio-try-on-mirror' ); ?>">
	</p>
</form>
<?php else : ?>
<form action="<?php echo esc_url( $form_action ); ?>" method="post" id="auglio_api_login_form">
	<input type="hidden" name="action" value="auglio_api_login_response">
	<input type="hidden" name="auglio_api_login_nonce" value="<?php echo esc_attr( wp_create_nonce( 'auglio_api_login_form_nonce' ) ); ?>"/>
	<table class="form-table" aria-label="API Connection Settings">
		<tbody>
			<tr>
				<th scope="row">
					<label for="auglio-public_api_key"><?php esc_html_e( 'API Key', 'auglio-try-on-mirror' ); ?></label>
				</th>
				<td>
					<input required name="auglio[public_api_key]" id="auglio-public_api_key" class="regular-text" type="text">
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="auglio-private_api_key"><?php esc_html_e( 'Private API Key', 'auglio-try-on-mirror' ); ?></label>
				</th>
				<td>
					<input required name="auglio[private_api_key]" id="auglio-private_api_key" class="regular-text" type="password">
				</td>
			</tr>
		</tbody>
	</table>
	<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr__( 'Connect', 'auglio-try-on-mirror' ); ?>"></p>
</form>
<?php endif; ?>