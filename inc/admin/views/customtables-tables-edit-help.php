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

$title = __('Add New Custom Table', 'customtables');
$parent_file = 'customtables-tables.php';

$help = '<p>' . __('To add a new Table to your site, fill in the form on this screen and click the Add New Table button at the bottom.', 'customtables') . '</p>';

$help .= '<p>' . __('Remember to click the Add New Table button at the bottom of this screen when you are finished.', 'customtables') . '</p>';
