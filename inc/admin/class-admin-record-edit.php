<?php

namespace CustomTablesWP\Inc\Admin;

use CustomTables\common;
use CustomTables\CT;
use CustomTables\Layouts;
use CustomTables\record;

class Admin_Record_Edit
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
    public ?int $tableId;
    public ?int $listing_id;
    public ?array $recordRow;
    public string $formLink;
    public string $pageLayout;
    /*
	 *
	 *
	 * @param string $plugin_text_domain	Text domain of the plugin.
	 *
	 * @since 1.0.0
	 */
    public function __construct($plugin_text_domain)
    {
        require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-listoffields.php');
        $this->ct = new CT;
        $this->plugin_text_domain = $plugin_text_domain;
        $this->tableId = common::inputGetInt('table');
        $this->recordRow = null;
        $this->listing_id = null;

        if ($this->tableId) {
            $this->ct->getTable($this->tableId);
            if ($this->ct->Table->tablename !== null) {
                $this->listing_id = common::inputGetInt('id');

                if($this->listing_id === 0)
                    $this->listing_id = null;

                if ($this->listing_id !== null) {
                    $this->recordRow = $this->ct->Table->loadRecord($this->listing_id);
                }
            }
        }

        $Layouts = new Layouts($this->ct);
        $this->ct->LayoutVariables['layout_type'] = 2;
        $this->pageLayout = $Layouts->createDefaultLayout_Edit_WP($this->ct->Table->fields, false);

        $this->formLink = 'admin.php?page=customtables-records-edit&table=' . $this->tableId
            .($this->listing_id !== null ? '&id=' . $this->listing_id : '');
    }

    function handle_record_actions(): void
    {
        if (isset($_REQUEST['action']) && ('createrecord' === $_REQUEST['action'] || 'saverecord' === $_REQUEST['action'])) {

            $recordClassFilePath = CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'records' . DIRECTORY_SEPARATOR . 'record.php';
            require_once($recordClassFilePath);
            $record = new record($this->ct);

            $Layouts = new Layouts($this->ct);
            $record->editForm->layoutContent = $Layouts->createDefaultLayout_Edit($this->ct->Table->fields, false);

            $listing_id = common::inputGetCmd('id');
            $record->save($listing_id, false);

            //$this->helperListOfFields->save($this->tableId, $this->fieldId);
            $url = 'admin.php?page=customtables-records&table=' . $this->tableId;

            ob_start(); // Start output buffering
            ob_end_clean(); // Discard the output buffer
            wp_redirect(admin_url($url));
            exit;
        }
    }
}