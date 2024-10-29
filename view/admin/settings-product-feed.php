<?php
/**
 * Product Feed settings page contents
 *
 * @package Auglio_Try_On_Mirror
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2><?php echo esc_html__( 'Product Feed Settings', 'auglio-try-on-mirror' ); ?></h2>
<p>
	<?php echo esc_html__( 'XML Feed URL' ); ?>:
	<a href="<?php echo esc_url( site_url( '/?auglio_xml_product_feed' ) ); ?>" target="_blank" rel="noopener">
		<?php echo esc_url( site_url( '/?auglio_xml_product_feed' ) ); ?>
	</a>
</p>
<form action="<?php echo esc_url( $form_action ); ?>" method="post" id="auglio_product_feed_form" >
	<input type="hidden" name="action" value="auglio_product_feed_response">
	<input type="hidden" name="auglio_product_feed_nonce" value="<?php echo esc_attr( wp_create_nonce( 'auglio_product_feed_form_nonce' ) ); ?>"/>
	<table class="form-table" aria-label="<?php echo esc_attr__( 'Product Feed Settings', 'auglio-try-on-mirror' ); ?>">
		<tbody>
			<tr>
				<th scope="row">
					<label for="auglio-status"><?php echo esc_html__( 'Status', 'auglio-try-on-mirror' ); ?></label>
				</th>
				<td>
					<select name="auglio_product_feed[status]" id="auglio-status">
						<option value="1" <?php selected( 1 === (int) $data['status'] ); ?>>
							<?php echo esc_html__( 'Enabled', 'auglio-try-on-mirror' ); ?>
						</option>
						<option value="0" <?php selected( 0 === (int) $data['status'] ); ?>>
							<?php echo esc_html__( 'Disable' ); ?>
						</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo esc_html__( 'Post Status', 'auglio-try-on-mirror' ); ?>
				</th>
				<td>
					<fieldset>
						<label for="auglio-post_status_publish">
							<input type="checkbox" <?php checked( in_array( 'publish', $data['post_statuses'], true ) ); ?>
							name="auglio_product_feed[post_statuses][]"
							value="publish" id="auglio-post_status_publish">
							<?php echo esc_html__( 'Publish', 'auglio-try-on-mirror' ); ?>
						</label>
						<br>
						<label for="auglio-post_status_pending">
							<input type="checkbox" <?php checked( in_array( 'pending', $data['post_statuses'], true ) ); ?>
							name="auglio_product_feed[post_statuses][]" 
							value="pending" id="auglio-post_status_pending">
							<?php echo esc_html__( 'Pending', 'auglio-try-on-mirror' ); ?>
						</label>
						<br>
						<label for="auglio-post_status_draft">
							<input type="checkbox" <?php checked( in_array( 'draft', $data['post_statuses'], true ) ); ?>
							name="auglio_product_feed[post_statuses][]" 
							value="draft" id="auglio-post_status_draft">
							<?php echo esc_html__( 'Draft', 'auglio-try-on-mirror' ); ?>
						</label>
						<br>
						<label for="auglio-post_status_future">
							<input type="checkbox" <?php checked( in_array( 'future', $data['post_statuses'], true ) ); ?>
							name="auglio_product_feed[post_statuses][]"
							value="future" id="auglio-post_status_future">
							<?php echo esc_html__( 'Future', 'auglio-try-on-mirror' ); ?>
						</label>
						<br>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="auglio-default_category">
						<?php echo esc_html__( 'Default category', 'auglio-try-on-mirror' ); ?>
					</label>
				</th>
				<td>
					<select name="auglio_product_feed[default_category]" id="auglio-default_category">
						<option value=""></option>
						<?php foreach ( $auglio_categories as $group_name => $group ) : ?>
						<optgroup label="<?php echo esc_attr( ucfirst( $group_name ) ); ?>">
							<?php foreach ( $group as $category_id => $category_name ) : ?>
							<option value="<?php echo esc_attr( $category_id ); ?>" <?php selected( (int) $data['default_category'] === $category_id ); ?>>
								<?php echo esc_html( $category_name ); ?>
							</option>
							<?php endforeach; ?>
						</optgroup>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="auglio-default_gender">
						<?php echo esc_html__( 'Default Gender', 'auglio-try-on-mirror' ); ?>
					</label>
				</th>
				<td>
					<select name="auglio_product_feed[default_gender]" id="auglio-default_gender">
						<option value=""></option>
						<?php foreach ( $auglio_genders as $gender_id => $gender_name ) : ?>
						<option value="<?php echo esc_attr( $gender_id ); ?>" <?php selected( $data['default_gender'] === $gender_id ); ?>>
							<?php echo esc_html( $gender_name ); ?>
						</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">Category Pairing</th>
				<td>
					<table aria-label="Product Feed Settings - Category Pairing">
						<tr>
							<th scope="col" style="width: auto; padding: 20px 10px;">Store Product Category</th>
							<th scope="col" style="width: auto; padding: 20px 10px;">Auglio Product Category</th>
							<th scope="col" style="width: auto; padding: 20px 10px;">Auglio Gender</th>
							<th scope="col" style="width: auto; padding: 20px 10px;">Export</th>
						</tr>
					<?php foreach ( $store_categories as $ancestors => $product_category ) : ?>
						<tr>
							<td>
								<label for="auglio-categories_<?php echo esc_attr( $product_category->term_id ); ?>">
									<?php echo esc_html( $ancestors ); ?>
								</label>
							</td>
							<td>
								<select name="auglio_product_feed[categories][<?php echo esc_attr( $product_category->term_id ); ?>]" id="auglio-categories_<?php echo esc_attr( $product_category->term_id ); ?>">
									<option value=""></option>
									<?php foreach ( $auglio_categories as $group_name => $group ) : ?>
									<optgroup label="<?php echo esc_attr( ucfirst( $group_name ) ); ?>">
										<?php foreach ( $group as $category_id => $category_name ) : ?>
										<option value="<?php echo esc_attr( $category_id ); ?>" 
											<?php selected( isset( $data['categories'][ $product_category->term_id ] ) && (int) $data['categories'][ $product_category->term_id ] === $category_id ); ?>>
											<?php echo esc_html( $category_name ); ?>
										</option>
										<?php endforeach; ?>
									</optgroup>
									<?php endforeach; ?>
								</select>
							</td>
							<td>
								<select name="auglio_product_feed[genders][<?php echo esc_attr( $product_category->term_id ); ?>]" id="auglio-genders_<?php echo esc_attr( $product_category->term_id ); ?>">
									<option value=""></option>
									<?php foreach ( $auglio_genders as $gender_id => $gender_name ) : ?>
									<option value="<?php echo esc_attr( $gender_id ); ?>" <?php selected( isset( $data['genders'][ $product_category->term_id ] ) && $data['genders'][ $product_category->term_id ] === $gender_id ); ?>>
										<?php echo esc_html( $gender_name ); ?>
									</option>
									<?php endforeach; ?>
								</select>
							</td>
							<td>
								<select name="auglio_product_feed[export][<?php echo esc_attr( $product_category->term_id ); ?>]" id="auglio-export_<?php echo esc_attr( $product_category->term_id ); ?>">
									<option value="1" <?php selected( ! isset( $data['export'][ $product_category->term_id ] ) || 1 === (int) $data['export'][ $product_category->term_id ] ); ?>>Yes</option>
									<option value="0" <?php selected( isset( $data['export'][ $product_category->term_id ] ) && 0 === (int) $data['export'][ $product_category->term_id ] ); ?>>No</option>
								</select>
							</td>
						</tr>
					<?php endforeach; ?>
					</table>
				</td>
			</tr>
		</tbody>
	</table>
	<?php submit_button(); ?>
</form>