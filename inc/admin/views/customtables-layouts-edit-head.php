<?php
/**
 * Plugin Name:       CustomTables
 * Plugin URI:        https://ct4.us/
 * GitHub:            https://github.com/joomlaboat/customtables-wp
 * Author:            Ivan Komlev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function enqueue_codemirror() {

$version = '1.1.0';
wp_enqueue_style('customtables-js-modal', plugin_dir_url(__FILE__) . '../../../libraries/customtables/media/css/modal.css', false,$version);
wp_enqueue_style('customtables-js-layouteditor', plugin_dir_url(__FILE__) . '../../../libraries/customtables/media/css/layouteditor.css', false, $version);

wp_enqueue_script('customtables-js-layoutwizard', home_url() . '/wp-content/plugins/customtables/libraries/customtables/media/js/layoutwizard.js', array(), $version, false);
wp_enqueue_script('customtables-js-layouteditor', home_url() . '/wp-content/plugins/customtables/libraries/customtables/media/js/layouteditor.js', array(), $version, false);
}