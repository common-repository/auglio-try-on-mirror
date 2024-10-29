<?php
/**
 * Settings page contents
 *
 * @package Auglio_Try_On_Mirror
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( $title ); ?></h1>
<?php
if ( ! $api_logged_in ) {
	echo '
    <div class="notice is-dismissible notice-warning">
        <p>' .
	sprintf(
		// translators: %1$s - auglio.com pricing url, %2$s = auglio.com, %3$s = auglio client dashboard profile url.
		wp_kses_post( __(
			'Signup for a free Auglio account at 
			<a href="%1$s" target="_blank">%2$s</a>, and 
			copy and paste Public and Private API keys from the
			<a href="%3$s" target="_blank">profile page</a> 
			into the API Connection form.',
			'auglio-try-on-mirror'
		) ),
		esc_url( 'https://auglio.com/en/pricing' ),
		'auglio.com',
		esc_url( 'https://dashboard.auglio.com/en/auth/profile' )
	) . '
        </p>
    </div>';
}
if ( isset( $_GET['response'] ) ) {
	if ( 'success' === $_GET['response'] ) {
		?>
	<div class="notice is-dismissible notice-success">
		<p><?php echo esc_html__( 'Saved successfully!', 'auglio-try-on-mirror' ); ?></p>
	</div>
		<?php
	} else {
		?>
	<div class="notice is-dismissible notice-error">
		<p><?php echo wp_kses_post( wp_unslash( $_GET['response'] ) ); ?></p>
	</div>
		<?php
	}
}

require __DIR__ . '/settings-nav.php';

$form_action = admin_url( 'admin-post.php' );

if ( 'settings' === $tab ) {
	require __DIR__ . '/settings-general.php';
} elseif ( 'product_feed' === $tab ) {
	require __DIR__ . '/settings-product-feed.php';
} elseif ( 'api' === $tab ) {
	require __DIR__ . '/settings-api.php';
}
?>
</div>
