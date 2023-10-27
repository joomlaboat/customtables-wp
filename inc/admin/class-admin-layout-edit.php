<?php

namespace CustomTablesWP\Inc\Admin;

use CustomTables\common;
use CustomTables\CT;
use CustomTables\Layouts;
use CustomTables\ListOfLayouts;

class Admin_Layout_Edit
{
    /**
     * The text domain of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_text_domain The text domain of this plugin.
     */
    public string $plugin_text_domain;
    public CT $ct;
    public ListOfLayouts $helperListOfLayouts;
    public ?int $layoutId;
    public ?array $layoutRow;

    /*
	 *
	 *
	 * @param string $plugin_text_domain	Text domain of the plugin.
	 *
	 * @since 1.0.0
	 */
    public function __construct($plugin_text_domain)
    {
        require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-listoflayouts.php');
        $this->ct = new CT;
        $this->helperListOfLayouts = new ListOfLayouts($this->ct);
        $this->plugin_text_domain = $plugin_text_domain;

        $this->layoutId = common::inputGetInt('layout');

        if($this->layoutId === 0)
            $this->layoutId = null;

        if($this->layoutId !== null) {
            $layout = new Layouts($this->ct);
            $this->layoutRow = $layout->getLayoutRowById($this->layoutId);
        }
        else
        {
            $this->layoutRow = null;
        }
    }

    function handle_layout_actions(): void
    {
        if(isset($_REQUEST['action']) && ('createlayout' === $_REQUEST['action'] || 'savelayout' === $_REQUEST['action'])) {
            $this->helperListOfLayouts->save($this->layoutId);
            $url = 'admin.php?page=customtables-layouts';
            ob_start(); // Start output buffering
            ob_end_clean(); // Discard the output buffer
            wp_redirect(admin_url($url));
            exit;
        }
    }
}