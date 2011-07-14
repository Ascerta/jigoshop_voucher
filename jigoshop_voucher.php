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
 * Installs and upgrades
 **/
register_activation_hook( __FILE__, 'install_jigoshop_voucher' );

function install_jigoshop_voucher() {
	
	if (!$type_id = get_term_by( 'slug', 'voucher', 'product_type')) {
			wp_insert_term('voucher', 'product_type');
		}

}
?>
