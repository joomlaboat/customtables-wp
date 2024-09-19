<?php
/**
 * Plugin Name:       CustomTables
 * Plugin URI:        https://ct4.us/
 * GitHub:            https://github.com/joomlaboat/customtables-wp
 * Author:            Ivan Komlev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace CustomTablesWP\Inc\Admin;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use CustomTables\common;

class Admin_Settings
{
    function handle_settings_actions(): void
    {
        $action = common::inputPostCmd('action','','settings');

        echo 'action:'.$action.'<br>';
        //die;

        if('save-settings'=== $action) {

            //Google Maps
            $GoogleMapAPIKey = common::inputPostString('googlemapapikey','','settings');
            if(get_option('customtables-googlemapapikey')===false)
                add_option('customtables-googlemapapikey', sanitize_text_field($GoogleMapAPIKey));
            else
                update_option('customtables-googlemapapikey', sanitize_text_field($GoogleMapAPIKey));

            //Google Drive
            $GoogleDriveAPIKey = common::inputPostString('googledriveapikey','','settings');
            if(get_option('customtables-googledriveapikey')===false)
                add_option('customtables-googledriveapikey', sanitize_text_field($GoogleDriveAPIKey));
            else
                update_option('customtables-googledriveapikey', sanitize_text_field($GoogleDriveAPIKey));

            $GoogleDriveClientId = common::inputPostString('googledriveclientid','','settings');
            if(get_option('customtables-googledriveclientid')===false)
                add_option('customtables-googledriveclientid', sanitize_text_field($GoogleDriveClientId));
            else
                update_option('customtables-googledriveclientid', sanitize_text_field($GoogleDriveClientId));



            $url = 'admin.php?page=customtables-settings';

            ob_start(); // Start output buffering
            ob_end_clean(); // Discard the output buffer
            wp_redirect(admin_url($url));
            exit;
        }
    }
}