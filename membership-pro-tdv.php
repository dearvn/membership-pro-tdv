<?php
/**
 * Plugin Name: Membership Pro Tdv
 * Description: Hook some actions with Membership Pro Pro plugin and add/remove username of TradingView.
 * Version:     1.0.0
 * Author:      donald
 * Author URI:  https://github.com/dearvn/membership-pro-tdv
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: membership-pro-tdv
 *
 * @package MPTDV
 */

/*
Membership Pro Tdv is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Membership Pro Tdv is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Membership Pro Tdv. If not, see http://www.gnu.org/licenses/gpl-2.0.txt.
*/

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define plugin __FILE__
 */
if ( ! defined( 'MPTDV_PLUGIN_FILE' ) ) {
	define( 'MPTDV_PLUGIN_FILE', __FILE__ );
}

/**
 * Include necessary files to initial load of the plugin.
 */
if ( ! class_exists( 'MPTDV\Bootstrap' ) ) {
	require_once __DIR__ . '/includes/traits/trait-singleton.php';
	require_once __DIR__ . '/includes/class-bootstrap.php';
}

/**
 * Initialize the plugin functionality.
 *
 * @since  1.0.0
 * @return MPTDV\Bootstrap
 */
function membership_pro_tdv_plugin() {
	return MPTDV\Bootstrap::instance();
}

// Call initialization function.
membership_pro_tdv_plugin();
