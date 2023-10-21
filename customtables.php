<?php
/**
 * @link              https://ct4.us/
 * @since             1.0.0
 * @package           customtables
 *
 * @wordpress-plugin
 * Plugin Name:       Custom Tables
 * Plugin URI:        https://ct4.us/
 * Description:       CRUD solution for WordPress.
 * Version:           1.0.0
 * Author:            Ivan Komlev
 * Author URI:        https://ct4.us/
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

/*

add_action('admin_menu', 'customtables');

function customtables(){

    $svg = '<?xml version="1.0" encoding="UTF-8" standalone="no"?><svg xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd"   xmlns="http://www.w3.org/2000/svg"
   xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"
   
   inkscape:version="1.0 (4035a4fb49, 2020-05-01)"
   sodipodi:docname="ct-gray.svg"
   viewBox="0 0 114 115"
   height="115"
   width="114"
   id="svg858">
  <defs
     id="defs862" />

  <g
     id="g866"
     inkscape:label="Image"
     inkscape:groupmode="layer">
    <path
       id="path870"
       d="M 35.103826,96.56662 C 23.278826,89.9971 13.093146,83.832692 12.468982,82.867939 10.795824,80.281786 9.8404542,33.632036 11.417889,31.544115 13.001683,29.447778 53.915377,5.6138435 55.930197,5.6138435 c 2.43754,0 44.123893,24.0385655 45.211953,26.0716115 1.14004,2.130191 1.28774,48.867091 0.16003,50.641674 -1.19538,1.88108 -41.302403,26.293271 -43.102383,26.235391 -0.87778,-0.0282 -11.27097,-5.42638 -23.095971,-11.9959 z M 51.406787,75.963401 c 3.54226,-0.585352 3.78219,-0.8413 3.5,-3.733727 -0.30086,-3.083731 -0.3435,-3.105334 -6.13946,-3.110564 -5.15179,-0.0046 -6.119637,-0.334426 -8.249999,-2.811031 -1.921122,-2.233358 -2.413502,-3.932138 -2.413502,-8.326923 0,-4.806975 0.389869,-5.911028 3.01397,-8.535129 2.704151,-2.704154 3.544471,-2.978928 8.174891,-2.673077 4.92357,0.325215 5.19554,0.212012 5.91354,-2.46142 0.64567,-2.404117 0.39843,-2.936974 -1.73999,-3.75 -6.40245,-2.434204 -16.319434,0.374476 -20.107948,5.694953 -5.6066,7.873744 -4.870656,19.869788 1.576962,25.704801 2.097016,1.897774 8.514176,4.539463 11.168576,4.597661 0.825,0.01809 3.21133,-0.249907 5.30296,-0.595544 z m 22.76236,-14.599558 -0.16146,-13.75 h 4.54807 c 4.531126,0 4.548066,-0.01304 4.548066,-3.5 v -3.5 h -12.499996 -12.5 v 3.5 c 0,3.481481 0.0238,3.5 4.5,3.5 h 4.5 v 14.060365 14.060366 l 3.61339,-0.310366 3.61339,-0.310365 z"
       style="fill-opacity:1" />
  </g>
</svg>';

    $icon = 'data:image/svg+xml;base64,' . base64_encode($svg);

    //add_menu_page( string $page_title, string $menu_title, string $capability, string $menu_slug, callable $callback = '', string $icon_url = '', int|float $position = null )
    add_menu_page( 'Custom Tables - Dashboard', 'Custom Tables', 'manage_options', 'customtables', 'customtablesAdminDashboard', $icon );
    add_submenu_page( 'customtables', 'Custom Tables - D', 'Dashboard', 'manage_options', 'customtables', 'customtablesAdminDashboard', 1 );
    add_submenu_page( 'customtables', 'Custom Tables - Tables', 'Tables', 'manage_options', 'customtables-tables', 'customtablesAdminTables', 1 );
    add_submenu_page( 'customtables', 'Custom Tables - Tables', 'Layouts', 'manage_options', 'customtables-tables', 'customtablesAdminTables', 1 );
    add_submenu_page( 'customtables', 'Custom Tables - Tables', 'Database schema', 'manage_options', 'customtables-tables', 'customtablesAdminTables', 1 );
    add_submenu_page( 'customtables', 'Custom Tables - Tables', 'Documentation', 'manage_options', 'customtables-tables', 'customtablesAdminTables', 1 );
}

function customtablesAdminDashboard(){
    echo "<h1>Hello D!</h1>";
}

function customtablesAdminTables(){
    echo "<h1>Hello T!</h1>";


    echo '<table class="widefat fixed" cellspacing="0">
    <thead>
    <tr>

            <th id="cb" class="manage-column column-cb check-column" scope="col"></th> // this column contains checkboxes
            <th id="columnname" class="manage-column column-columnname" scope="col"></th>
            <th id="columnname" class="manage-column column-columnname num" scope="col"></th> // "num" added because the column contains numbers

    </tr>
    </thead>

    <tfoot>
    <tr>

            <th class="manage-column column-cb check-column" scope="col"></th>
            <th class="manage-column column-columnname" scope="col"></th>
            <th class="manage-column column-columnname num" scope="col"></th>

    </tr>
    </tfoot>

    <tbody>
        <tr class="alternate">
            <th class="check-column" scope="row"></th>
            <td class="column-columnname"></td>
            <td class="column-columnname"></td>
        </tr>
        <tr>
            <th class="check-column" scope="row"></th>
            <td class="column-columnname"></td>
            <td class="column-columnname"></td>
        </tr>
        <tr class="alternate" valign="top"> // this row contains actions
            <th class="check-column" scope="row"></th>
            <td class="column-columnname">
                <div class="row-actions">
                    <span><a href="#">Action</a> |</span>
                    <span><a href="#">Action</a></span>
                </div>
            </td>
            <td class="column-columnname"></td>
        </tr>
        <tr valign="top"> // this row contains actions
            <th class="check-column" scope="row"></th>
            <td class="column-columnname">
                <div class="row-actions">
                    <span><a href="#">Action</a> |</span>
                    <span><a href="#">Action</a></span>
                </div>
            </td>
            <td class="column-columnname"></td>
        </tr>
    </tbody>
</table>
';
}

*/


/*
add_action( 'admin_head', function () {

    echo '<style>



    </style>';
    $this->document->addStyleSheet(JURI::root(true) . "/components/com_customtables/libraries/customtables/media/css/fieldtypes.css");
    */
//}
/*
    
    echo '<script>
    
    jQuery( document ).ready(function() {
    jQuery(\'[data-toggle="tab"]\').click(function () {

            var tabs = jQuery(this).attr(\'data-tabs\');
            var tab = jQuery(this).attr("data-tab");
            jQuery(tabs).find(".gtab").removeClass("active");
            jQuery(tabs).find(tab).addClass("active");
        });
});
    
    
    </script>';
    */
//} );
