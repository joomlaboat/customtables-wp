<?php

namespace CustomTablesWP\Inc\Admin;

use CustomTables\common;
use CustomTables\CT;
use CustomTables\ListOfTables;

class Admin_Table_Edit
{
    /**
     * The text domain of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_text_domain The text domain of this plugin.
     */
    public $plugin_text_domain;
    public CT $ct;
    public $helperListOfTables;
    public ?int $tableId;

    /**
	 *
	 *
	 * @param string $plugin_text_domain	Text domain of the plugin.
	 *
	 * @since 1.0.0
	 */
    public function __construct($plugin_text_domain)
    {
        require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-listoftables.php');
        $this->ct = new CT;
        $this->helperListOfTables = new \CustomTables\ListOfTables($this->ct);
        $this->plugin_text_domain = $plugin_text_domain;

        $this->tableId = get_query_var('table');

        if($this->tableId === 0)
            $this->tableId = null;

        if($this->tableId !== null)
            $this->ct->getTable($this->tableId);
    }

    function handle_table_actions()
    {
        if(isset($_REQUEST['action']) && ('createtable' === $_REQUEST['action'] || 'savetable' === $_REQUEST['action'])) {
            $errors=$this->helperListOfTables->save($this->tableId);
            if(count($errors)>0)
            {
                print_r($errors);
                die;
            }
            $url = 'admin.php?page=customtables-tables';

            ob_start(); // Start output buffering
            ob_end_clean(); // Discard the output buffer
            wp_redirect(admin_url($url));
            exit;
        }
    }
}