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
use CustomTables\CT;
use CustomTables\Fields;
use CustomTables\ListOfFields;
use CustomTables\TableHelper;
use Exception;

class Admin_Field_Edit
{
    /**
     * @since    1.0.0
     * @access   private
     */
    public CT $ct;
    public ListOfFields $helperListOfFields;
	public ?int $tableId;
    public ?int $fieldId;
    public array $fieldRow;
    public array $fieldTypes;
    public array $allTables;

	/**
	 * @throws Exception
	 * @since 1.1.4
	 */
    public function __construct()
    {
        require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-listoffields.php');
        $this->ct = new CT([],true);
        $this->helperListOfFields = new ListOfFields($this->ct);
	    $this->tableId = common::inputGetInt('table');
        $this->fieldId = null;
        $this->fieldRow=['tableid' => null,'fieldname' => null , 'fieldtitle' => null, 'type' => null, 'typeparams' => null, 'isrequired' => null,
            'defaultvalue' => null, 'allowordering' => null, 'valuerule' =>null, 'valuerulecaption' => null];

        if ($this->tableId)
        {
            $this->ct->getTable($this->tableId);
            if($this->ct->Table->tablename !== null) {
                $this->fieldId = common::inputGetInt('field');

                if ($this->fieldId === 0)
                    $this->fieldId = null;

                if ($this->fieldId !== null)
                    $this->fieldRow = $this->ct->Table->getFieldById($this->fieldId);
            }
        }

        $this->fieldTypes = $this->helperListOfFields->getFieldTypesFromXML(true);
        $this->allTables = TableHelper::getAllTables();
    }

    function handle_field_actions(): void
    {
	    $action = common::inputPostCmd('action','','create-edit-field');
        if('createfield' === $action || 'savefield' === $action) {
            $this->helperListOfFields->save($this->tableId,$this->fieldId);
            $url = 'admin.php?page=customtables-fields&table='.$this->tableId;

            ob_start(); // Start output buffering
            ob_end_clean(); // Discard the output buffer
            wp_redirect(admin_url($url));
            exit;
        }
    }
}