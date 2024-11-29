<?php
/**
 * CustomTables Joomla! 3.x/4.x/5.x Component and WordPress 6.x Plugin
 * @package Custom Tables
 * @author Ivan Komlev <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright (C) 2018-2024. Ivan Komlev
 * @license GNU/GPL Version 2 or later - https://www.gnu.org/licenses/gpl-2.0.html
 **/

namespace CustomTables;

// no direct access
if ( ! defined( 'ABSPATH' ) ) exit;

use CustomTablesImageMethods;
use Exception;
use Joomla\CMS\Factory;
use CustomTablesKeywordSearch;
use CustomTables\CustomPHP;

class CT
{
    var Languages $Languages;
    var Environment $Env;
    var ?Params $Params;
    var ?Table $Table;
    var ?array $Records;
    var ?string $GroupBy; // real field name
    var ?Ordering $Ordering;
    var ?Filtering $Filter;
    var ?string $alias_fieldname;
    var int $Limit;
    var int $LimitStart;
    var bool $isEditForm;
    var array $editFields;
    var array $editFieldTypes;
    var array $LayoutVariables;
    var array $errors;
    var array $messages;

    //Joomla Specific
    var $app;
    var $document;

    /**
     * @throws Exception
     * @since 3.0.0
     */
    function __construct(?array $menuParams = null, $blockExternalVars = true, ?string $ModuleId = null, bool $enablePlugin = true)
    {
        $this->errors = [];
        $this->messages = [];

        if (defined('_JEXEC')) {

            $this->app = Factory::getApplication();

            if (!($this->app instanceof \Joomla\CMS\Application\ConsoleApplication)) {
                try {
                    $this->document = $this->app->getDocument();
                } catch (Exception $e) {
                    // Handle error if needed
                }
            }
        }

        $this->Languages = new Languages;

        $this->Env = new Environment($enablePlugin);
        $this->Params = new Params($menuParams, $blockExternalVars, $ModuleId);

        $this->GroupBy = null;
        $this->isEditForm = false;
        $this->LayoutVariables = [];
        $this->editFields = [];
        $this->editFieldTypes = [];

        $this->Limit = 0;
        $this->LimitStart = 0;

        $this->Table = null;
        $this->Records = null;
        $this->Ordering = null;
        $this->Filter = null;
    }

    function isRecordNull(?array $row): bool
    {
        if (is_null($row))
            return true;

        if (!is_array($row))
            return true;

        if (count($row) == 0)
            return true;

        if (!isset($row[$this->Table->realidfieldname]))
            return true;

        $id = $row[$this->Table->realidfieldname];

        if (is_null($id))
            return true;

        if ($id == '')
            return true;

        if (is_numeric($id) and intval($id) == 0)
            return true;

        return false;
    }

    /**
     * @throws Exception
     *
     * @since 3.0.0
     */
    function setParams(array $menuParams = null, $blockExternalVars = true, ?string $ModuleId = null): void
    {
        $this->Params->setParams($menuParams, $blockExternalVars, $ModuleId);
    }

    /**
     * @throws Exception
     * @since 3.2.3
     */
    function getTable($tableNameOrID, $userIdFieldName = null, bool $loadAllField = false): void
    {
        $this->Table = new Table($this->Languages, $this->Env, $tableNameOrID, $userIdFieldName, $loadAllField);

        if ($this->Table->tablename !== null) {
            $this->Ordering = new Ordering($this->Table, $this->Params);
            $this->prepareSEFLinkBase();
        } else {
            $this->Table = null;
            $this->Ordering = null;
        }
    }

    protected function prepareSEFLinkBase(): void
    {
        if (is_null($this->Table))
            return;

        if (is_null($this->Table->fields))
            return;

        $option = common::inputGetCmd('option');

        if ($option == 'com_customtables') {
            foreach ($this->Table->fields as $fld) {

                if ($fld['type'] == 'alias') {
                    $this->alias_fieldname = $fld['fieldname'];
                    return;
                }
            }
        }
        $this->alias_fieldname = null;
    }

    /**
     * @throws Exception
     * @since 3.2.2
     */
    function getRecords(bool $all = false, int $limit = 0, ?string $orderby = null, string $groupBy = null): bool
    {
        $count = $this->getNumberOfRecords($this->Filter->whereClause);

        if ($count === null)
            return false;

        //Grouping
        if (!empty($groupBy)) {
            $tempFieldRow = $this->Table->getFieldByName($groupBy);
            if ($tempFieldRow !== null)
                $this->GroupBy = $tempFieldRow['realfieldname'];
        }

        //Ordering
        if ($orderby != null)
            $this->Ordering->ordering_processed_string = $orderby;

        if ($this->Ordering->ordering_processed_string !== null) {
            $this->Ordering->parseOrderByString();
        }

        $selects = $this->Table->selects;
        $ordering = [];

        if ($this->Ordering->orderby !== null) {
            if ($this->Ordering->selects !== null)
                $selects[] = $this->Ordering->selects;

            $ordering[] = $this->Ordering->orderby;
        }

        if ($this->Table->recordcount > 0) {

            if ($limit > 0) {
                $this->Records = database::loadAssocList($this->Table->realtablename, $selects, $this->Filter->whereClause,
                    (count($ordering) > 0 ? implode(',', $ordering) : null), null, $limit, null, $this->GroupBy);
                $this->Limit = $limit;
            } else {
                $the_limit = $this->Limit;

                if ($all) {
                    $this->Records = database::loadAssocList($this->Table->realtablename, $selects, $this->Filter->whereClause,
                        (count($ordering) > 0 ? implode(',', $ordering) : null), null, 20000, null, $this->GroupBy);
                } else {
                    if ($the_limit > 20000)
                        $the_limit = 20000;

                    if ($the_limit == 0)
                        $the_limit = 20000; //or we will run out of memory

                    if ($this->Table->recordcount < $this->LimitStart or $this->Table->recordcount < $the_limit)
                        $this->LimitStart = 0;

                    try {
                        $this->Records = database::loadAssocList($this->Table->realtablename, $selects, $this->Filter->whereClause,
                            (count($ordering) > 0 ? implode(',', $ordering) : null), null, $the_limit, $this->LimitStart, $this->GroupBy);
                    } catch (Exception $e) {
                        $this->errors[] = $e->getMessage();
                        return false;
                    }
                }
            }
        } else
            $this->Records = [];

        if ($this->Limit == 0)
            $this->Limit = 20000;

        return true;
    }

    function getNumberOfRecords(MySQLWhereClause $whereClause): ?int
    {
        if ($this->Table === null or $this->Table->tablerow === null or $this->Table->tablerow['realidfieldname'] === null)
            return null;

        try {
            $rows = database::loadObjectList($this->Table->realtablename, ['COUNT_ROWS'], $whereClause);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return null;
        }

        if (count($rows) == 0)
            $this->Table->recordcount = 0;
        else
            $this->Table->recordcount = intval($rows[0]->record_count);

        return $this->Table->recordcount;
    }

    /**
     * @throws Exception
     * @since 3.2.3
     */
    function setFilter(?string $filter_string = null, int $showpublished = 0): void
    {
        $this->Filter = new Filtering($this, $showpublished);
        if ($filter_string != '')
            $this->Filter->addWhereExpression($filter_string);
    }

    /**
     * @throws Exception
     * @since 3.2.3
     */
    function getRecordsByKeyword(): void
    {
        //Joomla Method
        $moduleId = common::inputGetInt('ModuleId', 0);
        if ($moduleId != 0) {
            $keywordSearch = common::inputGetString('eskeysearch_' . $moduleId, '');
            if ($keywordSearch != '') {
                require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'filter' . DIRECTORY_SEPARATOR . 'keywordsearch.php');

                $KeywordSearcher = new CustomTablesKeywordSearch($this);

                $KeywordSearcher->groupby = $this->GroupBy;
                $KeywordSearcher->esordering = $this->Ordering->ordering_processed_string;

                $this->Records = $KeywordSearcher->getRowsByKeywords(
                    $keywordSearch,
                    $this->Table->recordcount,
                    (int)$this->app->getState('limit'),
                    $this->LimitStart
                );

                if ($this->Table->recordcount < $this->LimitStart)
                    $this->LimitStart = 0;
            }
        }
    }

    function getRecordList(): array
    {
        if ($this->Table->recordlist !== null)
            return $this->Table->recordlist;

        $recordList = [];

        foreach ($this->Records as $row)
            $recordList[] = $row[$this->Table->realidfieldname];

        $this->Table->recordlist = $recordList;
        return $recordList;
    }

    /**
     * @throws Exception
     * @since 3.0.0
     */
    function applyLimits(int $limit = 0): void
    {
        if ($limit != 0) {
            $this->Limit = $limit;
            $this->LimitStart = 0;
            return;
        }

        if (defined('_JEXEC')) {
            $limit_var = 'com_customtables.limit_' . $this->Params->ItemId;
            $this->Limit = $this->app->getUserState($limit_var, 0);
        } else
            $this->Limit = 0;

        //Grouping
        $this->GroupBy = null;
        if (!empty($this->Params->groupBy)) {
            $tempFieldRow = $this->Table->getFieldByName($this->Params->groupBy);
            if ($tempFieldRow !== null)
                $this->GroupBy = $tempFieldRow['realfieldname'];
        }

        if ($this->Params->blockExternalVars) {
            if ((int)$this->Params->limit > 0) {
                $this->Limit = (int)$this->Params->limit;
                $this->LimitStart = common::inputGetInt('start', 0);
                $this->LimitStart = ($this->Limit != 0 ? (floor($this->LimitStart / $this->Limit) * $this->Limit) : 0);
            } else {
                $this->Limit = 0;
                $this->LimitStart = 0;
            }
        } else {
            $this->LimitStart = common::inputGetInt('start', 0);

            if (defined('_JEXEC')) {
                $limit_var = 'com_customtables.limit_' . $this->Params->ItemId;
                $this->Limit = $this->app->getUserState($limit_var, 0);
            } else
                $this->Limit = 0;

            if ($this->Limit == 0 and (int)$this->Params->limit > 0) {
                $this->Limit = (int)$this->Params->limit;
            }

            // In case limit has been changed, adjust it
            $this->LimitStart = ($this->Limit != 0 ? (floor($this->LimitStart / $this->Limit) * $this->Limit) : 0);
        }
    }

    /**
     * @throws Exception
     * @since 3.2.2
     */
    public function deleteSingleRecord($listing_id): int
    {
        $whereClause = new MySQLWhereClause();
        $whereClause->addCondition($this->Table->realidfieldname, $listing_id);

        $rows = database::loadAssocList($this->Table->realtablename, $this->Table->selects, $whereClause, null, null, 1);

        if (count($rows) == 0)
            return -1;

        $row = $rows[0];

        //delete images if exist
        $imageMethods = new CustomTablesImageMethods;

        foreach ($this->Table->fields as $fieldRow) {
            $field = new Field($this, $fieldRow, $row);

            if ($field->type == 'image') {
                $ImageFolderArray = CustomTablesImageMethods::getImageFolder($field->params);

                //delete single image
                if ($row[$field->realfieldname] !== null) {

                    $fileNameType = $field->params[3] ?? '';

                    $imageMethods->DeleteExistingSingleImage(
                        $row[$field->realfieldname],
                        $ImageFolderArray['path'],
                        $field->params[0],
                        $this->Table->realtablename,
                        $field->realfieldname,
                        $this->Table->realidfieldname,
                        $fileNameType
                    );
                }
            } elseif ($field->type == 'imagegallery') {
                $ImageFolderArray = CustomTablesImageMethods::getImageFolder($field->params);

                //delete gallery images if exist
                $galleryName = $field->fieldname;
                $photoTableName = '#__customtables_gallery_' . $this->Table->tablename . '_' . $galleryName;

                $whereClause = new MySQLWhereClause();
                $whereClause->addCondition('listingid', $listing_id);

                $photoRows = database::loadObjectList($photoTableName, ['photoid'], $whereClause);
                $imageGalleryPrefix = 'g';

                foreach ($photoRows as $photoRow) {
                    $imageMethods->DeleteExistingGalleryImage(
                        $ImageFolderArray['path'],
                        $imageGalleryPrefix,
                        $this->Table->tableid,
                        $galleryName,
                        $photoRow->photoid,
                        $field->params[0],
                        true
                    );
                }
            }
        }

        database::deleteRecord($this->Table->realtablename, $this->Table->realidfieldname, $listing_id);

        if ($this->Env->advancedTagProcessor)
            $this->Table->saveLog($listing_id, 5);

        $new_row = array();

        if ($this->Env->advancedTagProcessor and $this->Table->tablerow['customphp'] !== null) {
            $customPHP = new CustomPHP($this, 'delete');
            $customPHP->executeCustomPHPFile($this->Table->tablerow['customphp'], $new_row, $row);
        }

        return 1;
    }

    /**
     * @throws Exception
     * @since 3.2.3
     */
    public function setPublishStatusSingleRecord($listing_id, int $status): int
    {
        if (!$this->Table->published_field_found)
            return -1;

        $data = [
            'published' => $status
        ];
        $whereClauseUpdate = new MySQLWhereClause();
        $whereClauseUpdate->addCondition($this->Table->realidfieldname, $listing_id);
        database::update($this->Table->realtablename, $data, $whereClauseUpdate);

        if ($status == 1)
            $this->Table->saveLog($listing_id, 3);
        else
            $this->Table->saveLog($listing_id, 4);

        $this->RefreshSingleRecord($listing_id, 0, ($status == 1 ? 'publish' : 'unpublish'));
        return 1;
    }

    /**
     * @throws Exception
     * @since 3.2.2
     */
    public function RefreshSingleRecord($listing_id, $save_log, string $action = 'refresh'): int
    {
        $whereClause = new MySQLWhereClause();
        $whereClause->addCondition($this->Table->realidfieldname, $listing_id);

        $rows = database::loadAssocList($this->Table->realtablename, $this->Table->selects, $whereClause, null, null, 1);

        if (count($rows) == 0)
            return -1;

        $row = $rows[0];
        $saveField = new SaveFieldQuerySet($this, $row, false);

        //Apply default values
        foreach ($this->Table->fields as $fieldRow) {

            if (!$saveField->checkIfFieldAlreadyInTheList($fieldRow['realfieldname']))
                $saveField->applyDefaults($fieldRow);
        }

        if (count($saveField->row_new) > 0) {
            $whereClauseUpdate = new MySQLWhereClause();
            $whereClauseUpdate->addCondition($this->Table->realidfieldname, $listing_id);
            database::update($this->Table->realtablename, $saveField->row_new, $whereClauseUpdate);
        }

        //End of Apply default values

        common::inputSet("listing_id", $listing_id);

        if ($this->Env->advancedTagProcessor)
            CustomPHP::doPHPonChange($this, $row);

        //update MD5s
        $this->updateMD5($listing_id);

        if ($save_log == 1)
            $this->Table->saveLog($listing_id, 10);

        //TODO use $saveField->saveField
        //$this->updateDefaultValues($row);

        if ($this->Env->advancedTagProcessor) {
            $customPHP = new CustomPHP($this, $action);
            $customPHP->executeCustomPHPFile($this->Table->tablerow['customphp'], $row, $row);
        }

        //Send email note if applicable
        if ($this->Params->onRecordAddSendEmail == 3 and !empty($this->Params->onRecordSaveSendEmailTo)) {
            //check conditions

            if ($saveField->checkSendEmailConditions($listing_id, $this->Params->sendEmailCondition)) {
                //Send email conditions met
                $saveField->sendEmailIfAddressSet($listing_id, $row, $this->Params->onRecordSaveSendEmailTo);
            }
        }
        return 1;
    }

    /**
     * @throws Exception
     * @since 3.2.3
     */
    protected function updateMD5(string $listing_id): void
    {
        //TODO: Use savefield
        foreach ($this->Table->fields as $fieldRow) {
            if ($fieldRow['type'] == 'md5') {
                $fieldsToCount = explode(',', str_replace('"', '', $fieldRow['typeparams']));//only field names, nothing else

                $fields = array();
                foreach ($fieldsToCount as $f) {
                    //to make sure that field exists
                    foreach ($this->Table->fields as $fieldRow2) {
                        if ($fieldRow2['fieldname'] == $f and $fieldRow['fieldname'] != $f)
                            $fields[] = 'COALESCE(' . $fieldRow2['realfieldname'] . ')';
                    }
                }

                if (count($fields) > 1) {

                    $data = [
                        $fieldRow['realfieldname'] => ['MD5(CONCAT_WS(' . implode(',', $fields) . '))', 'sanitized']
                    ];
                    $whereClauseUpdate = new MySQLWhereClause();
                    $whereClauseUpdate->addCondition($this->Table->realidfieldname, $listing_id);
                    database::update($this->Table->realtablename, $data, $whereClauseUpdate);
                }
            }
        }
    }

    /**
     * @throws Exception
     * @since 3.2.3
     */
    public function CheckAuthorization(int $action = 1): bool
    {
        $listing_id = $this->Params->listing_id;
        $userIdField = $this->Params->userIdField;

        if ($action == 0)
            return true;

        if ($action == 5) //force edit
        {
            $action = 1;
        } else {
            if ($action == 1 and $listing_id == 0)
                $action = 4; //add new
        }

        if ($this->Params->guestCanAddNew == 1)
            return true;

        if ($this->Params->guestCanAddNew == -1 and $listing_id == 0)
            return false;

        //check is authorized or not
        if ($action == 1)
            $userGroup = $this->Params->editUserGroups;
        elseif ($action == 2)
            $userGroup = $this->Params->publishUserGroups;
        elseif ($action == 3)
            $userGroup = $this->Params->deleteUserGroups;
        elseif ($action == 4)
            $userGroup = $this->Params->addUserGroups;
        else
            $userGroup = null;

        if ($this->Env->user->id === null)
            return false;

        if ($this->Env->user->isUserAdministrator) {
            //Super Users have access to everything
            return true;
        }

        if ($listing_id === null or $listing_id === "" or $listing_id == 0 or $userIdField == '')
            return $this->Env->user->checkUserGroupAccess($userGroup);

        $theAnswerIs = $this->checkIfItemBelongsToUser($listing_id, $userIdField);

        if (!$theAnswerIs)
            return $this->Env->user->checkUserGroupAccess($userGroup);

        return true;
    }

    /**
     * @throws Exception
     * @since 3.2.2
     */
    public function checkIfItemBelongsToUser(string $listing_id, string $userIdField): bool
    {
        $whereClause = $this->UserIDField_BuildWheres($userIdField, $listing_id);
        $rows = database::loadObjectList($this->Table->realtablename, ['COUNT_ROWS'], $whereClause, null, null, 1);

        if (count($rows) !== 1)
            return false;

        if ($rows->record_count == 1)
            return true;

        return false;
    }

    /**
     * @throws Exception
     * @since 3.2.3
     */
    public function UserIDField_BuildWheres(string $userIdField, string $listing_id): MySQLWhereClause
    {
        $whereClause = new MySQLWhereClause();
        $statement_items = CTMiscHelper::ExplodeSmartParamsArray($userIdField); //"and" and "or" as separators
        $whereClauseOwner = new MySQLWhereClause();

        foreach ($statement_items as $item) {

            if (!str_contains($item['equation'], '.')) {
                //example: user
                //check if the record belong to the current user
                $user_field_row = $this->Table->getFieldByName($item['equation']);
                $whereClauseOwner->addCondition($user_field_row['realfieldname'], $this->Env->user->id);
            } else {
                //example: parents(children).user
                $statement_parts = explode('.', $item['equation']);
                if (count($statement_parts) != 2) {
                    $this->errors[] = esc_html__("Menu Item - 'UserID Field name' parameter has a syntax error. Error is about '(' character. Correct example: parent(children).user", "customtables");
                    return $whereClause;
                }

                $table_parts = explode('(', $statement_parts[0]);
                if (count($table_parts) != 2) {
                    $this->errors[] = esc_html__("Menu Item - 'UserID Field name' parameter has a syntax error. Error is about '(' character. Correct example: parent(children).user", "customtables");
                    return $whereClause;
                }

                $parent_tablename = $table_parts[0];
                $parent_join_field = str_replace(')', '', $table_parts[1]);
                $parent_user_field = $statement_parts[1];

                $parent_table_row = TableHelper::getTableRowByName($parent_tablename);

                if (!is_object($parent_table_row)) {
                    $this->errors[] = esc_html__("Menu Item - 'UserID Field name' parameter has an error: Table not found.", "customtables");
                    return $whereClause;
                }

                $tempTable = new Table($this->Languages, $this->Env, $parent_table_row->id);

                $parent_join_field_row = $tempTable->getFieldByName($parent_join_field);

                if (count($parent_join_field_row) == 0) {
                    $this->errors[] = esc_html__("Menu Item - 'UserID Field name' parameter has an error: Table not found.", "customtables");
                    return $whereClause;
                }

                if ($parent_join_field_row['type'] != 'sqljoin' and $parent_join_field_row['type'] != 'records') {
                    $this->errors[] = sprintf("Menu Item - 'UserID Field name' parameter has an error: Wrong join field type '%s'. Accepted types: 'sqljoin' and 'records'.", $parent_join_field_row['type']);
                    return $whereClause;
                }

                //User field
                $parent_user_field_row = $tempTable->getFieldByName($parent_user_field);

                if (count($parent_user_field_row) == 0) {
                    $this->errors[] = sprintf("Menu Item - 'UserID Field name' parameter has an error: User field '%s' not found.", $parent_user_field);
                    return $whereClause;
                }

                if ($parent_user_field_row['type'] != 'userid' and $parent_user_field_row['type'] != 'user') {
                    $this->errors[] = sprintf("Menu Item - 'UserID Field name' parameter has an error: Wrong user field type '%s'. Accepted types: 'userid' and 'user'.", $parent_join_field_row['type']);
                    return $whereClause;
                }

                $whereClauseParent = new MySQLWhereClause();

                $whereClauseParent->addCondition('p.' . $parent_user_field_row['realfieldname'], $this->Env->user->id);

                $fieldType = $parent_join_field_row['type'];
                if ($fieldType != 'sqljoin' and $fieldType != 'records')
                    return $whereClause;

                if ($fieldType == 'sqljoin') {
                    $whereClauseParent->addCondition('p.' . $parent_user_field_row['realfieldname'], 'c.listing_id', '=', true);
                }

                if ($fieldType == 'records')
                    $whereClauseParent->addCondition('p.' . $parent_user_field_row['realfieldname'], 'CONCAT(",",c.' . $this->Table->realidfieldname . ',",")', 'INSTR', true);

                $parent_wheres_string = (string)$whereClauseParent;
                $whereClauseOwner->addCondition('(SELECT p.' . $parent_table_row->realidfieldname . ' FROM ' . $parent_table_row->realtablename . ' AS p WHERE ' . $parent_wheres_string . ' LIMIT 1)', null, 'NOT NULL');
            }
        }

        $whereClause->addNestedCondition($whereClauseOwner);

        if ($listing_id != '' and $listing_id != 0)
            $whereClause->addCondition($this->Table->realidfieldname, $listing_id);

        return $whereClause;
    }
}
