<?php

use ProductFilter\ProductFilterPlugin;

/**
 *
 * Plugin Name:       Product Filter
 * Plugin URI:        https://premmerce.com
 * Description:       The plugin provide custom filter page for woocommerce
 * Version:           1.0
 * Author:            Sentius Dev Team
 * Author URI:        https://premmerce.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       product-filter
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

call_user_func( function () {

	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

	$main = new ProductFilterPlugin( __FILE__ );

	register_activation_hook( __FILE__, [ $main, 'activate' ] );

	register_deactivation_hook( __FILE__, [ $main, 'deactivate' ] );

	register_uninstall_hook( __FILE__, [ ProductFilterPlugin::class, 'uninstall' ] );

	$main->run();
} );