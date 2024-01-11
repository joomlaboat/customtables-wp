<?php

namespace CustomTablesWP\Inc\Core;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.nuancedesignstudio.in
 * @since      1.0.0
 *
 * @author     Karan NA Gupta
 */
class Internationalization_i18n {

	/**
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'customtables',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
