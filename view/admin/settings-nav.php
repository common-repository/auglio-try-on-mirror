<?php
/**
 * Navigation tabs for settings page
 *
 * @package Auglio_Try_On_Mirror
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$auglio_plugin_active_tab = 'nav-tab-active';

$auglio_plugin_tabs = array(
	'settings'     => __( 'Settings', 'auglio-try-on-mirror' ),
	'product_feed' => __( 'XML Product Feed', 'auglio-try-on-mirror' ),
	'api'          => __( 'Auglio API Connection', 'auglio-try-on-mirror' ),
);
?>
<nav class="nav-tab-wrapper wp-clearfix" aria-label="Secondary menu">
<?php foreach ( $auglio_plugin_tabs as $auglio_plugin_tab => $auglio_plugin_tab_text ) { ?>
	<a href="<?php echo esc_url( site_url( '/wp-admin/admin.php?page=auglio&tab=' . $auglio_plugin_tab ) ); ?>" 
		class="nav-tab <?php echo $auglio_plugin_tab === $tab ? esc_html( $auglio_plugin_active_tab ) : ''; ?>">
		<?php echo esc_html( $auglio_plugin_tab_text ); ?>
	</a>
<?php } ?>
</nav>