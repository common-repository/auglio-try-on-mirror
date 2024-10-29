<?php
/**
 * Auglio xml feed generation
 *
 * @package Auglio_Try_On_Mirror
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Auglio_Try_On_Mirror_Feed class
 *
 * This class handles the product feed generation
 *
 * @package Auglio_Try_On_Mirror
 */
class Auglio_Try_On_Mirror_Feed {

	/**
	 * Generate feed
	 *
	 * This function generates the feed and outputs it to the browser
	 *
	 * @return void
	 */
	public function generate_feed() {

		$options = get_option( 'auglio_product_feed' );
		if ( ! (int) $options['status'] ) {
			return;
		}
		if ( class_exists( 'SitePress' ) ) {
			do_action( 'wpml_switch_language', 'all' );
		}
		header( 'Content-Type: application/xml; charset=utf-8', true );
		$args = array(
			'post_type'        => array( 'product', 'product_variation' ),
			'posts_per_page'   => -1,
			'post_status'      => $options['post_statuses'],
			'suppress_filters' => true,
		);
		$loop = new WP_Query( $args );
		echo '<?xml version="1.0" encoding="UTF-8"?>
        <SHOP>';
		while ( $loop->have_posts() ) :
			$loop->the_post();
			global $product;

			$terms = get_the_terms( $loop->post->ID, 'product_cat' );
			if ( get_post_type( $loop->post->ID ) === 'product_variation' ) {
				$terms = get_the_terms( $loop->post->post_parent, 'product_cat' );
			}
			$cat    = null;
			$gender = null;
			$export = 1;
			foreach ( $terms as $term ) {
				$term_id = $term->term_id;
				if ( isset( $options['categories'][ $term_id ] )
					&& $options['categories'][ $term_id ] && ! $cat
				) {
					$cat = $options['categories'][ $term_id ];
				}

				if ( isset( $options['genders'][ $term_id ] )
					&& $options['genders'][ $term_id ] && ! $gender
				) {
					$gender = $options['genders'][ $term_id ];
				}
				if ( isset( $options['export'][ $term_id ] )
					&& '' !== $options['export'][ $term_id ]
				) {
					$export &= $options['export'][ $term_id ];
				}
			}
			if ( ! $export ) {
				continue;
			}

			$cat      = $cat ? $cat : $options['default_category'];
			$gender   = $gender ? $gender : $options['default_gender'];
			$group_id = $loop->post->post_parent ? $loop->post->post_parent : $loop->post->ID;

			echo '<SHOPITEM>
                <ITEM_ID>' . esc_html( $loop->post->ID ) . '</ITEM_ID>
                <GROUP_ID>' . esc_html( $group_id ) . '</GROUP_ID>
                <PRODUCTNAME><![CDATA[' . esc_html( get_the_title() ) . ']]></PRODUCTNAME>
                <DESCRIPTION><![CDATA[' . wp_kses_post( strip_shortcodes( get_the_excerpt() ) ) . ']]></DESCRIPTION>
                <URL><![CDATA[' . esc_url( get_the_permalink() ) . ']]></URL>
                <CATEGORY>' . esc_html( $cat ) . '</CATEGORY>
                <IMGURL><![CDATA[' . esc_url( wp_get_attachment_url( $product->get_image_id() ) ) . ']]></IMGURL>
                <PRICE>' . esc_html( $product->get_price() ) . '</PRICE>
                <SEX>' . esc_html( $gender ) . '</SEX>
                <EAN><![CDATA[' . esc_html( $product->get_sku() ) . ']]></EAN>
            </SHOPITEM>';
		endwhile;
		echo '</SHOP>';
	}
}
