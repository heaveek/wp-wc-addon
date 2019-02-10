<?php
/* ====================================
 * Plugin Name: Woocommerce Books Addon
 * Description: Plugin add fields and display information.
 * Version: 1.0
 * ==================================== */

	// Create and add fields, save the received information
	add_action('edit_form_after_title', 'fields_for_books_info', 1);

	function fields_for_books_info($post){
		global $wpdb;
		$arr_author = $wpdb->get_results("
			SELECT DISTINCT meta_value FROM `wp_postmeta` WHERE meta_key = 'author_book' ORDER BY `wp_postmeta`.`meta_value` DESC
		",
		ARRAY_N
		);
	?>
	<div style="width: 30%; padding: 20px; background-color: white; border: 1px solid #ddd; box-shadow: inset 0 1px 2px rgba(0,0,0,.07); margin-top: 20px;">
		<label>Author:</label>
		<input list="author_book" name="extra[author_book]" value="<?php echo get_post_meta($post->ID, 'author_book', 1); ?>">
		<datalist name="extra[author_book]" id="author_book"> 
		<?php
			foreach($arr_author as $key=>$elem) {	
				foreach($elem as $el_author) {
					if($el_author) { ?>
						<option value="<?php echo $el_author; ?>">
							<?php 
								echo $el_author;
							?>
						</option>
				<?php }
				}
			}
		?>
		</datalist>
		
		<p><label>EAN:<span style="color: red;">* </span><input type="text" name="extra[ean_book]" value="<?php echo get_post_meta($post->ID, 'ean_book', 1); ?>" required></label></p>
		
		<p><label>ISBN:<span style="color: red;">* </span><input type="text" name="extra[isbn_book]" value="<?php echo get_post_meta($post->ID, 'isbn_book', 1); ?>" required></label></p>
	
		<input type="hidden" name="books_fields_nonce" value="<?php echo wp_create_nonce(__FILE__); ?>">
	</div>
	<?php
	}

	add_action( 'save_post', 'fields_for_books_update', 0 );

	function fields_for_books_update($post_id){
		if (empty($_POST['extra']) 
			|| ! wp_verify_nonce($_POST['books_fields_nonce'], __FILE__ ) 
			|| wp_is_post_autosave($post_id) 
			|| wp_is_post_revision($post_id))
			return false;

		$_POST['extra'] = array_map('sanitize_text_field', $_POST['extra']); // чистим все данные от пробелов по краям
		foreach($_POST['extra'] as $key => $value){
			if(empty($value)){
				delete_post_meta( $post_id, $key);
				continue;
			}
			update_post_meta($post_id, $key, $value);
		}
		return $post_id;
	}

	// Print the data to the template
	add_action('woocommerce_single_product_summary', 'woocommerce_books_addon');

	function woocommerce_books_addon() {
		global $post, $product;
		$author_select_field = get_post_meta($post->ID, 'author_book', 1);
		$ean_text_field = get_post_meta($post->ID, 'ean_book', 1);
		$isbn_text_field = get_post_meta($post->ID, 'isbn_book', 1);

		if ($author_select_field) {
			?>
			<div class="author-select-field">
				<p>Издательство: <?php echo $author_select_field; ?></p>
			</div>
		<?php }

		if ($ean_text_field) { ?>
			<div class="ean-text-field">
				<p>EAN: <?php echo $ean_text_field; ?></p>
			</div>
		<?php }

		if ($isbn_text_field) { ?>
			<div class="isbn-text-field">
				<p>ISBN: <?php echo $isbn_text_field; ?></p>			
			</div>
			<?php
		}
	}

	// We display data after the header, then the price
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);

	add_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
	add_action('woocommerce_single_product_summary', 'woocommerce_books_addon', 10);
	add_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 15);