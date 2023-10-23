<?php

namespace CustomTablesWP\Inc\Admin;

use CustomTableList;
use CustomTables\common;
use CustomTables\CT;
use const CustomTablesWP\CTWP;
use CustomTables\listOfTables;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       https://www.nuancedesignstudio.in
 * @since      1.0.0
 *
 * @author    Karan NA Gupta
 */
class Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * The text domain of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_text_domain The text domain of this plugin.
     */
    private $plugin_text_domain;

    /**
     * WP_List_Table object
     *
     * @since    1.0.0
     * @access   private
     * @var      admin_table_list $admin_list_table
     */
    private $admin_table_list;
    private $admin_table_edit;
    private $admin_field_list;
    private $admin_field_edit;
    private $admin_record_list;
    private $admin_record_edit;
    private $admin_layout_list;
    private $admin_layout_edit;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     * @param string $plugin_text_domain The text domain of this plugin
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version, $plugin_text_domain)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->plugin_text_domain = $plugin_text_domain;
        add_action('init', array($this, 'my_load_plugin_textdomain'));
    }

    function my_load_plugin_textdomain()
    {
        $domain = 'customtables';
        $mo_file = ABSPATH . 'wp-content/plugins/customtables/Languages/' . $domain . '-' . get_locale() . '.mo';

        load_textdomain($domain, $mo_file);
        //load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/customtables-admin.css', array(), $this->version, 'all');
        wp_enqueue_style('fieldtypes', plugin_dir_url(__FILE__) . '../../libraries/customtables/media/css/fieldtypes.css', false, '2.7.8');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        //$params = array ( 'ajaxurl' => admin_url( 'admin-ajax.php' ) );
        wp_enqueue_script('nds_ajax_handle', plugin_dir_url(__FILE__) . 'js/customtables-admin.js', array('jquery'), $this->version, false);
        //wp_localize_script( 'nds_ajax_handle', 'params', $params );


        $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
        if($page == 'customtables-schema') {
            wp_enqueue_script('customtables-js-raphael', home_url() . '/wp-content/plugins/customtables/libraries/customtables/media/js/raphael.min.js', array('jquery'), $this->version, false);
            wp_enqueue_script('customtables-js-diagram', home_url() . '/wp-content/plugins/customtables/libraries/customtables/media/js/diagram.js', array('jquery'), $this->version, false);
        }
    }

    /**
     * Callback for the user sub-menu in define_admin_hooks() for class Init.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu()
    {
        // Get the custom tables icon
        $icon = $this->getCustomTablesIcon();

        // Dashboard
        add_menu_page(
            'Dashboard - Custom Tables ', // Page Title
            'Custom Tables',             // Menu Title
            'manage_options',            // Capability
            'customtables',               // Menu Slug
            array($this, 'load_customtablesAdminDashboard'), // Callback Function
            $icon                         // Icon URL
        );

        // Dashboard Submenu
        add_submenu_page(
            'customtables',          // Parent Menu Slug
            'Dashboard - Custom Tables', // Page Title
            'Dashboard',              // Menu Title
            'manage_options',         // Capability
            'customtables',           // Menu Slug
            array($this, 'load_customtablesAdminDashboard'), // Callback Function
            1                          // Position
        );

        // Tables
        $page_hook = add_submenu_page(
            'customtables',                    // Parent Menu Slug
            __('Tables - Custom Tables', $this->plugin_text_domain), // Page Title
            __('Tables', $this->plugin_text_domain),                // Menu Title
            'manage_options',                                      // Capability
            'customtables-tables',                                 // Menu Slug
            array($this, 'load_admin_table_list'),                  // Callback Function
            2                                                      // Position
        );
        add_action('load-' . $page_hook, array($this, 'preload_admin_table_list'));

        // Layouts
        $page_hook = add_submenu_page(
            'customtables',                     // Parent Menu Slug
            'Custom Tables - Layouts',          // Page Title
            'Layouts',                          // Menu Title
            'manage_options',                   // Capability
            'customtables-layouts',             // Menu Slug
            array($this, 'load_admin_table_list'), // Callback Function
            3                                   // Position
        );
        $page_hook = add_action('load-' . $page_hook, array($this, 'preload_admin_layout_list'));

        // Database Schema
        add_submenu_page(
            'customtables',                      // Parent Menu Slug
            'Custom Tables - Database Schema',   // Page Title
            'Database Schema',                   // Menu Title
            'manage_options',                    // Capability
            'customtables-schema',        // Menu Slug
            array($this, 'load_customtablesAdminSchema'), // Callback Function
            4                                    // Position
        );

        // Documentation
        add_submenu_page(
            'customtables',                       // Parent Menu Slug
            'Custom Tables - Documentation',       // Page Title
            'Documentation',                       // Menu Title
            'manage_options',                      // Capability
            'customtables-documentation',          // Menu Slug
            array($this, 'load_customtablesAdminDocumentation'), // Callback Function
            5                                       // Position
        );

        // Edit Table Sub Sub Menu
        $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';

        switch ($page) {
            case 'customtables-tables-edit':
                $page_hook = add_submenu_page(
                    'customtables',                     // Parent Menu Slug
                    __('Edit Table - Custom Tables', $this->plugin_text_domain), // Page Title
                    __(' - Edit', $this->plugin_text_domain),                     // Menu Title
                    'manage_options',                                         // Capability
                    'customtables-tables-edit',                               // Menu Slug
                    array($this, 'load_customtablesAdminTablesEdit'),        // Callback Function
                    2                                                        // Position
                );
                add_action('load-' . $page_hook, array($this, 'load_customtablesAdminTablesEdit'));
                break;

            case 'customtables-fields':

                $page_hook = add_submenu_page(
                    'customtables',                     // Parent Menu Slug
                    __('Fields - Custom Tables', $this->plugin_text_domain), // Page Title
                    __(' - Fields', $this->plugin_text_domain),                     // Menu Title
                    'manage_options',                                         // Capability
                    'customtables-fields',                               // Menu Slug 'customtables-fields'
                    array($this, 'load_admin_field_list'),        // Callback Function
                    2                                                        // Position
                );
                //$url = add_query_arg(array('table' => $tableId), admin_url('admin.php?page=customtables-fields'));
                //echo "<script>jQuery(document).ready(function() { jQuery('#toplevel_page_customtables-fields').attr('href', '$url'); });</script>";
                add_action('load-' . $page_hook, array($this, 'preload_admin_fields_list'));
                break;

            case 'customtables-fields-edit':

                $tableId = common::inputGetInt('table');
                $page_hook = add_submenu_page(
                    'customtables',                     // Parent Menu Slug
                    __('Fields - Custom Tables', $this->plugin_text_domain), // Page Title
                    __(' - Fields', $this->plugin_text_domain),                     // Menu Title
                    'manage_options',                                         // Capability
                    'customtables-fields&table='.$tableId,                               // Menu Slug
                    array($this, 'load_admin_field_list'),        // Callback Function
                    2                                                        // Position
                );
                //add_action('load-' . $page_hook, array($this, 'preload_admin_fields_list'));

                $page_hook = add_submenu_page(
                    'customtables',                     // Parent Menu Slug
                    __('Edit Field - Custom Tables', $this->plugin_text_domain), // Page Title
                    __(' -- Edit', $this->plugin_text_domain),                     // Menu Title
                    'manage_options',                                         // Capability
                    'customtables-fields-edit',                               // Menu Slug
                    array($this, 'load_customtablesAdminFieldsEdit'),        // Callback Function
                    3                                                        // Position
                );
                add_action('load-' . $page_hook, array($this, 'load_customtablesAdminFieldsEdit'));
                break;
        }

    }

    protected function getCustomTablesIcon()
    {
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

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public function preload_admin_table_list()
    {

        /*
        $arguments	=	array(
            'label'		=>	__( 'Users Per Page', $this->plugin_text_domain ),
            'default'	=>	5,
            'option'	=>	'users_per_page'
        );

        add_screen_option( 'per_page', $arguments );
        */

        // instantiate the Admin Table List
        $this->admin_table_list = new Admin_Table_List($this->plugin_text_domain);
        $this->admin_table_list->handle_table_actions();
    }

    public function preload_admin_fields_list()
    {

        /*
        $arguments	=	array(
            'label'		=>	__( 'Users Per Page', $this->plugin_text_domain ),
            'default'	=>	5,
            'option'	=>	'users_per_page'
        );

        add_screen_option( 'per_page', $arguments );
        */

        // instantiate the Admin Field List
        $this->admin_field_list = new Admin_Field_List($this->plugin_text_domain);
        $this->admin_field_list->handle_field_actions();

        $page = common::inputGetCmd('page');
        if($page ==  'customtables-fields') {
            $tableId = common::inputGetInt('table');
            if($tableId === null) {
                // Redirect the user to the external URL
                $url = 'admin.php?page=customtables-tables';
                wp_redirect($url);
                exit();
            }
        }
    }

    public function preload_admin_record_list()
    {

        /*
        $arguments	=	array(
            'label'		=>	__( 'Users Per Page', $this->plugin_text_domain ),
            'default'	=>	5,
            'option'	=>	'users_per_page'
        );

        add_screen_option( 'per_page', $arguments );
        */

        // instantiate the Admin Record List
        $this->admin_record_list = new Admin_Record_List($this->plugin_text_domain);
        $this->admin_record_list->handle_record_actions();
    }

    public function preload_admin_layout_list()
    {

        /*
        $arguments	=	array(
            'label'		=>	__( 'Users Per Page', $this->plugin_text_domain ),
            'default'	=>	5,
            'option'	=>	'users_per_page'
        );

        add_screen_option( 'per_page', $arguments );
        */

        // instantiate the Admin Layout List
        $this->admin_layout_list = new Admin_Layout_List($this->plugin_text_domain);
        $this->admin_layout_list->handle_layout_actions();
    }

    public function load_customtablesAdminDashboard()
    {
        include_once('views' . DIRECTORY_SEPARATOR . 'customtables-dashboard.php');
    }

    /*
	 * Display the Table List
	 *
	 * Callback for
	 *
	 * @since	1.0.0
	 */
    public function load_admin_table_list()
    {
        {
            // instantiate the Admin Table List
            $this->admin_table_list = new Admin_Table_List($this->plugin_text_domain);

            // query, filter, and sort the data
            $this->admin_table_list->prepare_items();

            // render the List of Tables
            include_once('views/customtables-tables.php');
        }
    }

    public function load_admin_field_list()
    {
        {
            // instantiate the Admin Field List
            $this->admin_field_list = new Admin_Field_List($this->plugin_text_domain);

            // query, filter, and sort the data
            $this->admin_field_list->prepare_items();

            // render the List of Fields
            include_once('views/customtables-fields.php');
        }
    }

    public function load_admin_record_list()
    {
        {
            // instantiate the Admin Record List
            $this->admin_record_list = new Admin_Record_List($this->plugin_text_domain);

            // query, filter, and sort the data
            $this->admin_record_list->prepare_items();

            // render the List of Records
            include_once('views/customtables-records.php');
        }
    }

    public function load_admin_layout_list()
    {
        {
            // instantiate the Admin Layout List
            $this->admin_layout_list = new Admin_Layout_List($this->plugin_text_domain);

            // query, filter, and sort the data
            $this->admin_layout_list->prepare_items();

            // render the List of Layout
            include_once('views/customtables-layouts.php');
        }
    }

    public function load_customtablesAdminTablesEdit()
    {
        $this->admin_table_edit = new Admin_Table_Edit($this->plugin_text_domain);
        $this->admin_table_edit->handle_table_actions();
        include_once('views' . DIRECTORY_SEPARATOR . 'customtables-tables-edit.php');
    }

    public function load_customtablesAdminFieldsEdit()
    {
        $this->admin_field_edit = new Admin_Field_Edit($this->plugin_text_domain);
        $this->admin_field_edit->handle_field_actions();
        include_once('views' . DIRECTORY_SEPARATOR . 'customtables-fields-edit.php');
    }

    public function load_customtablesAdminRecordsEdit()
    {
        $this->admin_record_edit = new Admin_Record_Edit($this->plugin_text_domain);
        $this->admin_record_edit->handle_record_actions();
        include_once('views' . DIRECTORY_SEPARATOR . 'customtables-records-edit.php');
    }

    public function load_customtablesAdminLayoutsEdit()
    {
        $this->admin_layout_edit = new Admin_Layout_Edit($this->plugin_text_domain);
        $this->admin_layout_edit->handle_layout_actions();
        include_once('views' . DIRECTORY_SEPARATOR . 'customtables-layouts-edit.php');
    }
/*
    public function load_customtablesAdminLayouts()
    {
        include_once('views' . DIRECTORY_SEPARATOR . 'customtables-layouts.php');
    }*/

    public function load_customtablesAdminSchema()
    {
        include_once('views' . DIRECTORY_SEPARATOR . 'customtables-schema.php');
    }

    public function load_customtablesAdminDocumentation()
    {
        // render the List Table
        include_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-documentation.php');
        $documentation = new \CustomTables\Documentation();

        include_once('views' . DIRECTORY_SEPARATOR . 'customtables-documentation.php');
    }
}
