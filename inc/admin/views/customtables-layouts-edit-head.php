<?php
/**
 * New User Administration Screen.
 *
 * @package WordPress
 * @subpackage Administration
 */

function enqueue_codemirror() {
//wp_enqueue_script( 'code-mirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/codemirror.min.js', array(), null, true );
//wp_enqueue_style( 'code-mirror-css', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/codemirror.min.css' );

$version = '1.0.0';

$theme = 'eclipse';
wp_enqueue_style('customtables-js-modal', plugin_dir_url(__FILE__) . '../../../libraries/customtables/media/css/modal.css', false,$version);
wp_enqueue_style('customtables-js-layouteditor', plugin_dir_url(__FILE__) . '../../../libraries/customtables/media/css/layouteditor.css', false, $version);
wp_enqueue_style('customtables-js-codemirror', plugin_dir_url(__FILE__) . '../../../libraries/codemirror/lib/codemirror.css', false, $version);
wp_enqueue_style('customtables-js-show-hint', plugin_dir_url(__FILE__) . '../../../libraries/codemirror/addon/hint/show-hint.css', false, $version);
wp_enqueue_style('customtables-js-theme', plugin_dir_url(__FILE__) . '../../../libraries/codemirror/theme/' . $theme . '.css', false, $version);



wp_enqueue_script('customtables-js-layoutwizard', home_url() . '/wp-content/plugins/customtables/libraries/customtables/media/js/layoutwizard.js', array(), $version, false);
wp_enqueue_script('customtables-js-layouteditor', home_url() . '/wp-content/plugins/customtables/libraries/customtables/media/js/layouteditor.js', array(), $version, false);

wp_enqueue_script('customtables-js-codemirror', home_url() . '/wp-content/plugins/customtables/libraries/codemirror/lib/codemirror.js', array(), $version, false);
wp_enqueue_script('customtables-js-overlay', home_url() . '/wp-content/plugins/customtables/libraries/codemirror/addon/mode/overlay.js', array(), $version, false);
wp_enqueue_script('customtables-js-show-hint', home_url() . '/wp-content/plugins/customtables/libraries/codemirror/addon/hint/show-hint.js', array(), $version, false);
wp_enqueue_script('customtables-js-xml-hint', home_url() . '/wp-content/plugins/customtables/libraries/codemirror/addon/hint/xml-hint.js', array(), $version, false);
wp_enqueue_script('customtables-js-html-hint', home_url() . '/wp-content/plugins/customtables/libraries/codemirror/addon/hint/html-hint.js', array(), $version, false);

wp_enqueue_script('customtables-js-xml', home_url() . '/wp-content/plugins/customtables/libraries/codemirror/mode/xml/xml.js', array(), $version, false);
wp_enqueue_script('customtables-js-javascript', home_url() . '/wp-content/plugins/customtables/libraries/codemirror/mode/javascript/javascript.js', array(), $version, false);
wp_enqueue_script('customtables-js-css', home_url() . '/wp-content/plugins/customtables/libraries/codemirror/mode/css/css.js', array(), '1.0', false);
wp_enqueue_script('customtables-js-htmlmixed', home_url() . '/wp-content/plugins/customtables/libraries/codemirror/mode/htmlmixed/htmlmixed.js', array(), $version, false);
}