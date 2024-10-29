<?php
/**
 * Auglio product page meta box view
 *
 * @package Auglio_Try_On_Mirror
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$auglio_status = __( 'Not in Auglio', 'auglio-try-on-mirror' );
if ( $in_auglio_db ) {
	if ( $published ) {
		$auglio_status = __( 'Published', 'auglio-try-on-mirror' );
	} else {
		$auglio_status = __( 'Unpublished', 'auglio-try-on-mirror' );
	}
}

if( $api_connected ) {
?>
<p>
	<span class="dashicons dashicons-post-status"></span>
	<?php echo esc_html__( 'Auglio Status' ); ?>:
	<strong><?php echo esc_html( $auglio_status ); ?></strong>
</p>
<p>
	<a href="https://dashboard.auglio.com/products/create?<?php echo esc_url( $query_data ); ?>" class="button" target="_blank">
		<?php
		if ( $in_auglio_db ) {
			echo esc_html__( 'Edit in', 'auglio-try-on-mirror' );
		} else {
			echo esc_html__( 'Add to', 'auglio-try-on-mirror' );
		}
		?>
		<?php echo esc_html__( 'Virtual Mirror', 'auglio-try-on-mirror' ); ?>
	</a>
</p>
<?php
} else {
?>
<p><?php echo esc_html__( 'Please connect to Auglio API to use this feature.', 'auglio-try-on-mirror' ) ?></p>
<a href="<?php echo esc_url( site_url( '/wp-admin/admin.php?page=auglio&tab=api' ) ); ?>" class="button" target="_blank">Connect</a>
<?php
}
?>
