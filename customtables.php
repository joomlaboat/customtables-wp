<?php
/**
 * @link              https://ct4.us/
 * @since             1.0.0
 * @package           CustomTables
 *
 * @wordpress-plugin
 * Plugin Name:       CustomTables
 * Plugin URI:        https://ct4.us/
 * Description:       CRUD solution for WordPress.
 * Version:           1.0.0
 * Author:            Ivan Komlev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       customtables
 * Domain Path:       /languages
*/

namespace CustomTablesWP;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Define Constants
 */

define( __NAMESPACE__ . '\CTWP', __NAMESPACE__ . '\\' );

define( CTWP . 'PLUGIN_NAME', 'customtables' );

define( CTWP . 'PLUGIN_VERSION', '1.0.0' );

define( CTWP . 'PLUGIN_NAME_DIR', plugin_dir_path( __FILE__ ) );

define( CTWP . 'PLUGIN_NAME_URL', plugin_dir_url( __FILE__ ) );

define( CTWP . 'PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

define( CTWP . 'PLUGIN_TEXT_DOMAIN', 'customtables' );


/**
 * Autoload Classes
 */

$path = PLUGIN_NAME_DIR . 'libraries' . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR;
require_once($path . 'loader.php');
CTLoader(false, true, PLUGIN_NAME_DIR);

require_once( PLUGIN_NAME_DIR . 'inc/libraries/autoloader.php' );

/**
 * Register Activation and Deactivation Hooks
 * This action is documented in inc/core/class-activator.php
 */

register_activation_hook( __FILE__, array( CTWP . 'Inc\Core\Activator', 'activate' ) );

/**
 * The code that runs during plugin deactivation.
 * This action is documented inc/core/class-deactivator.php
 */

register_deactivation_hook( __FILE__, array( CTWP . 'Inc\Core\Deactivator', 'deactivate' ) );


/**
 * Plugin Singleton Container
 *
 * Maintains a single copy of the plugin app object
 *
 * @since    1.0.0
 */
class customtables {

    static $init;
    /**
     * Loads the plugin
     *
     * @access    public
     */
    public static function init() {

        if ( null == self::$init ) {
            self::$init = new Inc\Core\Init();
            self::$init->run();
        }
        return self::$init;
    }

}

/*
 * Begins execution of the plugin
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * Also returns copy of the app object so 3rd party developers
 * can interact with the plugin's hooks contained within.
 *
 */
function customtables_init() {
    return customtables::init();
}

$min_php = '5.6.0';

// Check the minimum required PHP version and run the plugin.
if ( version_compare( PHP_VERSION, $min_php, '>=' ) ) {
    customtables_init();
}



$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
if ($page == 'customtables-layouts-edit')
    add_action( 'admin_enqueue_scripts', 'enqueue_codemirror' );