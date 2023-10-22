<?php

use CustomTables\common;
use CustomTables\CT;
use CustomTables\database;
use CustomTables\Fields;
use CustomTables\listOfTables;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

require_once ABSPATH . 'wp-includes/pluggable.php';

class CustomTableList extends WP_List_Table
{
    function __construct()
    {
        $action = $this->current_action();
        if (!str_contains($action, 'customtables-') and !str_contains($action, 'createtable') and !str_contains($action, 'savetable'))
            parent::__construct();
    }

    function tableSave()
    {
        $ct = new CT;
        require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-listoftables.php');
        $helperListOfLayout = new listOfTables($ct);
        $tableId = common::inputGetInt('table');
        $messages = $helperListOfLayout->save($tableId);
        $url = 'admin.php?page=customtables-tables';
        wp_redirect(admin_url($url));
        exit;
    }


    // Define your table's data source


    function get_bulk_actions()
    {

    }

    // To show bulk action dropdown


    // Define the columns in your table
    function display_table()
    {
        $this->views();
        $this->prepare_items();
        $this->display();
    }

    // Define what each column displays


    // Handle bulk actions (if any)


    // Handle a bulk action (if implemented)


    // Adding action links to column


    // Display the table







}