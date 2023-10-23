<?php

namespace CustomTablesWP\Inc\Admin;

use CustomTables\common;
use CustomTables\CT;
use CustomTables\Field;
use CustomTables\Fields;
use CustomTables\ListOfFields;

class Admin_Field_Edit
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
    public $helperListOfFields;
    public ?int $fieldId;
    public array $fieldRow;
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
        $this->helperListOfFields = new \CustomTables\ListOfFields($this->ct);
        $this->plugin_text_domain = $plugin_text_domain;
        $this->tableId = common::inputGetInt('table');
        $this->fieldId = null;
        $this->fieldRow=['tableid' => null,'fieldname' => null , 'fieldtitle' => null, 'type' => null, 'typeparams' => null, 'isrequired' => null,
            'defaultvalue' => null, 'allowordering' => null, 'valuerule' =>null, 'valuerulecaption' => null];

        if ($this->tableId)
        {
            $this->ct->getTable($this->tableId);
            if($this->ct->Table->tablename !== null) {
                $this->fieldId = common::inputGetInt('field');
                if ($this->fieldId !== null)
                    $this->fieldRow = Fields::getFieldRow($this->fieldId,true);
            }
        }
    }

    function handle_field_actions()
    {
        if(isset($_REQUEST['action']) && ('createfield' === $_REQUEST['action'] || 'savefield' === $_REQUEST['action'])) {
            $this->helperListOfFields->save($this->tableId,$this->fieldId);
            $url = 'admin.php?page=customtables-fields';

            ob_start(); // Start output buffering
            ob_end_clean(); // Discard the output buffer
            wp_redirect(admin_url($url));
            exit;
        }
    }
}