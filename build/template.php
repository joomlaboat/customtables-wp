<?php
/**
 * Plugin Name:       CustomTables
 * Plugin URI:        https://ct4.us/
 * GitHub:            https://github.com/joomlaboat/customtables-wp
 * Author:            Ivan Komlev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * All the parameters passed to the function where this file is being required are accessible in this scope:
 *
 * @param array $attributes The array of attributes for this block.
 * @param string $content Rendered block output. ie. <InnerBlocks.Content />.
 * @param WP_Block $block_instance The instance of the WP_Block class that represents the block being rendered.
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;
use CustomTables\CT;
use CustomTables\Layouts;
use const CustomTablesWP\CTWP;

if (!function_exists('enqueue_frontend_scripts')) {
    function enqueue_frontend_scripts()
    {
        global $CUSTOM_TABLES_ENQUEUE;

        wp_enqueue_script('ct-catalog-script', CUSTOMTABLES_MEDIA_WEBPATH . 'js/catalog.js', array(), \CustomTablesWP\PLUGIN_VERSION, true);
        wp_enqueue_script('ct-edit-form-script', CUSTOMTABLES_MEDIA_WEBPATH . 'js/edit.js', array(), \CustomTablesWP\PLUGIN_VERSION, true);
        wp_enqueue_script('ct-uploader-script', CUSTOMTABLES_MEDIA_WEBPATH . 'js/uploader.js', array(), \CustomTablesWP\PLUGIN_VERSION, true);

        wp_enqueue_style('ct-catalog-style', CUSTOMTABLES_MEDIA_WEBPATH . 'css/style.css', array(), \CustomTablesWP\PLUGIN_VERSION, false);

        // Add inline script after enqueuing the main script
        wp_add_inline_script('ct-edit-form-script', 'let ctWebsiteRoot = "' . esc_url(home_url()) . '";');

        // Add inline script after enqueuing the main script
        if (isset($CUSTOM_TABLES_ENQUEUE['style']) and $CUSTOM_TABLES_ENQUEUE['style'] !== null)
            wp_add_inline_style('ct-catalog-style', $CUSTOM_TABLES_ENQUEUE['style']);

        // Add inline script after enqueuing the main script
        if (isset($CUSTOM_TABLES_ENQUEUE['script']) and $CUSTOM_TABLES_ENQUEUE['script'] !== null)
            wp_add_inline_script('ct-edit-form-script', $CUSTOM_TABLES_ENQUEUE['script']);

        if (isset($CUSTOM_TABLES_ENQUEUE['recaptcha']) and $CUSTOM_TABLES_ENQUEUE['recaptcha'] !== null)
            wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js');

        if (isset($CUSTOM_TABLES_ENQUEUE['fieldtype:date']) and $CUSTOM_TABLES_ENQUEUE['fieldtype:date']) {

            // Enqueue jQuery UI
            wp_enqueue_script('jquery-ui-core');

            wp_enqueue_script('ct-edit-form-script-jquery-ui-min', CustomTablesWP\PLUGIN_NAME_URL . 'assets/jquery-ui.min.js');
            wp_enqueue_style('ct-edit-form-style-jquery-timepicker', CustomTablesWP\PLUGIN_NAME_URL . 'assets/jquery.datetimepicker.min.css', array(), \CustomTablesWP\PLUGIN_VERSION);

            //Include jQuery UI Timepicker addon from CDN
            wp_enqueue_script('ct-edit-form-script-jquery-timepicker', CustomTablesWP\PLUGIN_NAME_URL . 'assets/jquery.datetimepicker.full.min.js');
        }

        if (isset($CUSTOM_TABLES_ENQUEUE['fieldtype:color']) and $CUSTOM_TABLES_ENQUEUE['fieldtype:color']) {
            wp_enqueue_script('ct-spectrum-script', CUSTOMTABLES_MEDIA_WEBPATH . 'js/spectrum.js', array(), \CustomTablesWP\PLUGIN_VERSION, true);
            wp_enqueue_style('ct-spectrum-style', CUSTOMTABLES_MEDIA_WEBPATH . 'css/spectrum.css', array(), \CustomTablesWP\PLUGIN_VERSION, false);
        }

        if (isset($CUSTOM_TABLES_ENQUEUE['fieldtype:googlemapcoordinates']) and $CUSTOM_TABLES_ENQUEUE['fieldtype:googlemapcoordinates']) {

            $googleMapAPIKey = get_option('customtables-googlemapapikey');

            if ($googleMapAPIKey !== null and $googleMapAPIKey != '')
                wp_enqueue_script('ct-google-map-script', 'https://maps.google.com/maps/api/js?key=' . $googleMapAPIKey . '&sensor=false', array(), \CustomTablesWP\PLUGIN_VERSION, true);
        }
    }

    /*
     * It will be used in the future

	function customtables_wp_title( $title, $sep ) {
		global $post;
		if ( is_single() || is_page() ) {
			$new_title = 'Your 123 Custom Page Title'; // Replace with your desired page title
			return $new_title;
		}
		return $title;
	}
    */
}

/*
 * <p <?php echo get_block_wrapper_attributes(); ?>>BLOCK1</p>
 * */
?>
<div>

    <?php

    if (isset($attributes['table'])) {

        if ($attributes['table'] !== 0) {

            $mixedLayout_array = [];
            $mixedLayout_safe = '';
            $ct = new CT(null, false);

            try {
                $ct->getTable($attributes['table']);
                if ($ct->Table->tablename !== null) {
                    $layoutType = null;
                    $view = common::inputGetCmd('view' . $ct->Table->tableid);
                    if ($view == 'edititem') {
                        $layoutType = 2;
                        $layoutId = (int)$attributes['editlayout'];
                    } elseif ($view == 'details') {
                        $layoutType = 4;
                        $layoutId = (int)$attributes['detailslayout'];
                    } else {
                        $layoutType = $attributes['type'];

                        if ((int)$layoutType == 1)
                            $layoutId = (int)$attributes['cataloglayout'];
                        elseif ((int)$layoutType == 2)
                            $layoutId = (int)$attributes['editlayout'];
                        elseif ((int)$layoutType == 4)
                            $layoutId = (int)$attributes['detailslayout'];
                        else
                            $layoutId = 0;
                    }

                    $layouts = new Layouts($ct);
                    $mixedLayout_array = $layouts->renderMixedLayout($layoutId, $layoutType);
                    $mixedLayout_safe = $mixedLayout_array['html'];
                } else {
                    $mixedLayout_safe = 'Table "' . $attributes['table'] . '" not found.';
                }
            } catch (Exception $e) {
                $mixedLayout_safe = 'Error: ' . $e->getMessage();
            }

            $message = get_transient('plugin_error_message');
            if ($message) {
                echo '<blockquote style="background-color: #f8d7da; border-left: 5px solid #dc3545; padding: 10px;"><p>' . esc_html($message) . '</p></blockquote>';
                // Once displayed, clear the transient
                delete_transient('plugin_error_message');
            }

            $success_message = get_transient('plugin_success_message');
            if (!empty($success_message)) {
                echo '<blockquote style="background-color: #d4edda;border-left: 5px solid #28a745;padding: 10px;"><p>' . esc_html($success_message) . '</p></blockquote>';
                // Optionally, you can delete the transient after displaying it
                delete_transient('plugin_success_message');
            }

            if (!is_admin()) {
                global $CUSTOM_TABLES_ENQUEUE;

                if (isset($mixedLayout_array['style']) and $mixedLayout_array['style'] !== null)
                    $CUSTOM_TABLES_ENQUEUE['style'] = $mixedLayout_array['style'];

                if (isset($mixedLayout_array['script']) and $mixedLayout_array['script'] !== null)
                    $CUSTOM_TABLES_ENQUEUE['script'] = $mixedLayout_array['script'];

                if (isset($mixedLayout_array['captcha']) and $mixedLayout_array['captcha'] !== null)
                    $CUSTOM_TABLES_ENQUEUE['recaptcha'] = $mixedLayout_array['captcha'];

                if (isset($mixedLayout_array['fieldtypes']) and $mixedLayout_array['fieldtypes'] !== null) {

                    if (in_array('date', $mixedLayout_array['fieldtypes']))
                        $CUSTOM_TABLES_ENQUEUE['fieldtype:date'] = true;

                    if (in_array('datetime', $mixedLayout_array['fieldtypes']))
                        $CUSTOM_TABLES_ENQUEUE['fieldtype:datetime'] = true;

                    if (in_array('color', $mixedLayout_array['fieldtypes']))
                        $CUSTOM_TABLES_ENQUEUE['fieldtype:color'] = true;

                    if (in_array('googlemapcoordinates', $mixedLayout_array['fieldtypes']))
                        $CUSTOM_TABLES_ENQUEUE['fieldtype:googlemapcoordinates'] = true;
                }

                add_action('wp_enqueue_scripts', 'enqueue_frontend_scripts');
                //add_filter( 'wp_title', 'customtables_wp_title', 10, 2 );
            }
            echo $mixedLayout_safe;

        } else {
            echo 'Custom Tables: Table Not Selected.';
        }
    }
    ?>
</div>