<?php
/**
 * Bootstrap class.
 *
 * @package MPTDV
 */

namespace MPTDV;

use MPTDV\Traits\Singleton;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load core functionality inside this class.
 */
class Bootstrap {

	use Singleton;

	/**
	 * Constructor of Bootstrap class.
	 */
	private function __construct() {
		
		if ( is_admin() ) {
			// this hook to add TradingView session id.	
			$this->load_hook_setting();
		}

		// Include trading view classes.
		$this->load_trading_view_classes();
	}

	/**
	 * Load hook setting.
	 */
	private function load_hook_setting() {
		require_once __DIR__ . '/class-setting.php';

		\MPTDV\Setting::instance();
	}

	/**
	 * Load TradingView api.
	 */
	private function load_trading_view_classes() {
		require_once __DIR__ . '/class-trading-view.php';

		\MPTDV\TradingView::instance();
	}

}
