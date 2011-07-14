<?php
/**
 * @package Jigoshop_voucher
 * @version 0.1
 */
/*
Plugin Name: Jigoshop Voucher
Plugin URI: https://github.com/Ascerta/Jigoshop
Description: Allow customers to buy vouchers and have them automatically set up and sent out.
Author: Andrew Spratley
Version: 0.1
Author URI: http://www.ascerta.co.uk/
*/

/**
 * Install
 **/
register_activation_hook( __FILE__, 'install_jigoshop_voucher' );

function install_jigoshop_voucher() {
	
	if (!$type_id = get_term_by( 'slug', 'voucher', 'product_type')) {
			wp_insert_term('voucher', 'product_type');
		}

}

/**
 * Product Options
 * 
 * Product Options for the voucher product type
 *
 * @since 		1.0
 */
function voucher_product_type_options() {
	global $post;
	?>
	<div id="voucher_product_options" class="panel jigoshop_options_panel">
	
	</div>
	<?php
}
add_action('jigoshop_product_type_options_box', 'voucher_product_type_options');

/**
 * Product Type selector
 * 
 * Adds this product type to the product type selector in the product options meta box
 *
 * @since 		1.0
 *
 * @param 		string $product_type Passed the current product type so that if it keeps its selected state
 */
function voucher_product_type_selector( $product_type ) {
	
	echo '<option value="voucher" '; if ($product_type=='voucher') echo 'selected="selected"'; echo '>'.__('Voucher','jigoshop').'</option>';

}
add_action('product_type_selector', 'voucher_product_type_selector');

/**
 * Process meta
 * 
 * Processes this product types options when a post is saved
 *
 * @since 		1.0
 *
 * @param 		array $data The $data being saved
 * @param 		int $post_id The post id of the post being saved
 */
function process_product_meta_voucher( $data, $post_id ) {
	

	return $data;

}
add_filter('process_product_meta_voucher', 'process_product_meta_voucher', 1, 2);

/**
 * Order Status completed - Generate voucher code, store it and send it to user
 **/
add_action('order_status_completed', 'jigoshop_send_voucher');

function jigoshop_send_voucher( $order_id ) {
	
	global $wpdb;
	
	$order = &new jigoshop_order( $order_id );
	
	if (sizeof($order->items)>0) foreach ($order->items as $item) {
	
		if ($item['id']>0) {
			$_product = &new jigoshop_product( $item['id'] );
			$o = $_product->product_type;
			if ( $_product->exists && $_product->is_type('voucher') ) {
				
				$user_email = $order->billing_email;
				
				if ($order->user_id>0) {
					$user_info = get_userdata($order->user_id);
					if ($user_info->user_email) {
						$user_email = $user_info->user_email;
					}
				
				}
				else {
						$order->user_id = 0;
				}
			
		
			//create coupon code
			
    $length = 15;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $seed_len = strlen($characters);
    $string = '';    
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[mt_rand(0, $seed_len)];
    }
   						
			
				$coupons = new jigoshop_coupons();
				$coupons = $coupons->get_coupons();
				$coupons[$code] = array( 
								'code' => $code,
								'amount' => $item['cost'],
								'type' => 'fixed_cart',
								'products' => array(),
								'individual_use' => 'yes'
							);
					
				update_option('jigoshop_coupons', $coupons);
			
			wp_mail( $order->billing_email, 'Your code', 'Here is your code '.$code);
				
			}
			
		}
	
	}
}
add_action( 'voucher_add_to_cart', 'jigoshop_voucher_add_to_cart' );

if (!function_exists('jigoshop_voucher_add_to_cart')) {
	function jigoshop_voucher_add_to_cart() {

		global $_product; $availability = $_product->get_availability();

		if ($availability['availability']) : ?><p class="stock <?php echo $availability['class'] ?>"><?php echo $availability['availability']; ?></p><?php endif;

		?>
		<form action="<?php echo $_product->add_to_cart_url(); ?>" class="cart" method="post">
		 	<div class="quantity"><input name="quantity" value="1" size="4" title="Qty" class="input-text qty text" maxlength="12" /></div>
		 	<button type="submit" class="button-alt"><?php _e('Add to cart', 'jigoshop'); ?></button>
		 	<?php do_action('jigoshop_add_to_cart_form'); ?>
		</form>
		<?php
	}
}
?>
