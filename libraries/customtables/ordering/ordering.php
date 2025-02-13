<?php
/**
 * CustomTables Joomla! 3.x/4.x/5.x Component and WordPress 6.x Plugin
 * @package Custom Tables
 * @author Ivan Komlev <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright (C) 2018-2025. Ivan Komlev
 * @license GNU/GPL Version 2 or later - https://www.gnu.org/licenses/gpl-2.0.html
 **/

namespace CustomTables;

// no direct access
if (!defined('ABSPATH')) exit;

use Exception;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

class Ordering
{
	var ?Table $Table = null;
	var ?Params $Params = null;
	var ?string $selects = null;
	var ?string $orderby = null;
	var ?string $ordering_processed_string = null;
	private int $index;
	private ?array $fieldList;

	function __construct($Table, $Params)
	{
		$this->Table = $Table;
		$this->Params = $Params;
		$this->index = -1;
		$this->fieldList = null;
	}

	/**
	 * @throws Exception
	 *
	 * @since 3.0.0
	 */
	public static function addTableTagID(string $result, int $tableid): string
	{
		$params = array();
		$params['id'] = 'ctTable_' . $tableid;
		return self::addEditHTMLTagParams($result, 'table', $params);
	}

	/**
	 * @throws Exception
	 *
	 * @since 3.0.0
	 */
	public static function addEditHTMLTagParams(string $result, string $tag, array $paramsToAddEdit): string
	{
		$options = array();
		$fList = CTMiscHelper::getListToReplace($tag, $options, $result, "<>", ' ');
		$i = 0;
		foreach ($fList as $fItem) {

			$params = CTMiscHelper::getHTMLTagParameters(strtolower($options[$i]));

			foreach ($paramsToAddEdit as $key => $value) {
				$params[$key] = $value;
			}

			$params_str = [];
			foreach ($params as $key => $value)
				$params_str[] = $key . '="' . htmlspecialchars($value ?? '') . '"';

			$val = '<' . $tag . ' ' . implode(' ', $params_str) . '>';
			$result = str_replace($fItem, $val, $result);
			$i++;
		}
		return $result;
	}

	/**
	 * @throws Exception
	 *
	 * @since 3.0.0
	 */
	public static function addTableBodyTagParams(string $result, int $tableid): string
	{
		$params = array();
		$params['class'] = 'js-draggable';
		$params['data-url'] = common::UriRoot(true, true) . 'index.php?option=com_customtables&view=catalog&task=ordering&tableid=' . $tableid . '&tmpl=component&clean=1';
		$params['data-direction'] = 'asc';
		$params['data-nested'] = 'true';
		return self::addEditHTMLTagParams($result, 'tbody', $params);
	}

	/**
	 * @throws Exception
	 * @since 3.2.2
	 */
	function parseOrderByString(): bool
	{
		if ($this->ordering_processed_string === null or $this->ordering_processed_string == '')
			return false;

		$orderingStringPair = explode(' ', $this->ordering_processed_string);
		$direction = '';

		if (isset($orderingStringPair[1])) {
			$direction = (strtolower($orderingStringPair[1]) == 'desc' ? ' DESC' : '');
		}

		$this->fieldList = explode('.', $orderingStringPair[0]);
		$this->index = 0;
		$orderbyQuery = self::parseOrderByFieldName($this->fieldList[$this->index], $this->Table);

		if ($orderbyQuery === null)
			return false;

		$this->orderby = $orderbyQuery . $direction;
		return true;
	}

	/**
	 * @throws Exception
	 * @since 3.2.2
	 */
	function parseOrderByFieldName(string $fieldName, Table $Table): ?string
	{
		if ($fieldName == '_id')
			return $Table->realidfieldname;
		elseif ($fieldName == '_published' and $Table->published_field_found)
			return 'listing_published';

		$fieldRow = $Table->getFieldByName($fieldName);
		if ($fieldRow === null)
			return null;

		$temp_ct = new CT([], true);
		$temp_ct->Table = $Table;
		$field = new Field($temp_ct, $fieldRow);

		if ($field->realfieldname == '')
			return null;

		switch ($field->type) {
			case 'user':
				return '(SELECT #__users.name FROM #__users WHERE #__users.id=' . $Table->realtablename . '.' . $field->realfieldname . ')';

			case 'sqljoin':

				$join_table = $field->params[0];
				$sqljoin_temp_ct = new CT([], true);
				$sqljoin_temp_ct->getTable($join_table);

				if ($this->index == count($this->fieldList) - 1) {

					$join_field = '';
					if (isset($field->params[1]))
						$join_field = $field->params[1];

					$select = self::parseOrderByFieldName($join_field, $sqljoin_temp_ct->Table);
					return '(SELECT ' . $select . ' FROM ' . $sqljoin_temp_ct->Table->realtablename
						. ' WHERE ' . $sqljoin_temp_ct->Table->realtablename . '.' . $sqljoin_temp_ct->Table->realidfieldname . '=' . $Table->realtablename . '.' . $field->realfieldname . ')';
				} else {
					$join_field = $this->fieldList[$this->index + 1];
					$this->index += 1;
				}

				$select = self::parseOrderByFieldName($join_field, $sqljoin_temp_ct->Table);
				return '(SELECT ' . $select . ' FROM ' . $sqljoin_temp_ct->Table->realtablename
					. ' WHERE ' . $sqljoin_temp_ct->Table->realtablename . '.' . $sqljoin_temp_ct->Table->realidfieldname . '=' . $Table->realtablename . '.' . $field->realfieldname . ')';

			default:
				return $field->realfieldname;
		}
	}

	/**
	 * @throws Exception
	 *
	 * @since 3.0.0
	 */
	function parseOrderByParam(): void
	{
		if (defined('_JEXEC')) {
			//get sort field (and direction) example "price desc"
			$app = Factory::getApplication();
			$ordering_param_string = '';

			if ($this->Params->blockExternalVars) {
				//module or plugin
				if ($this->Params->forceSortBy != '')
					$ordering_param_string = $this->Params->forceSortBy;
				elseif ($this->Params->sortBy != '')
					$ordering_param_string = $this->Params->sortBy;
			} else {
				if ($this->Params->forceSortBy != '') {
					$ordering_param_string = $this->Params->forceSortBy;
				} elseif (common::inputGetCmd('esordering', '', 'create-edit-record')) {
					$ordering_param_string = common::inputGetString('esordering', '', 'create-edit-record');
					$ordering_param_string = trim(preg_replace("/[^a-zA-Z-+%.: ,_]/", "", $ordering_param_string));
				} else {
					$Itemid = common::inputGetInt('Itemid', 0, 'create-edit-record');
					$ordering_param_string = $app->getUserState('com_customtables.orderby_' . $Itemid, '');

					if ($ordering_param_string == '') {
						if ($this->Params->sortBy !== null and $this->Params->sortBy != '')
							$ordering_param_string = $this->Params->sortBy;
					}
				}
			}
			$this->ordering_processed_string = $ordering_param_string;

			//set state
			if (!$this->Params->blockExternalVars)
				$app->setUserState('com_customtables.esorderby', $this->ordering_processed_string);
		} else {
			if ($this->Params->sortBy != '')
				$this->ordering_processed_string = $this->Params->sortBy;
		}
	}

	function getSortByFields(): array
	{
		//default sort by fields
		$fieldsToSort = [];

		$fieldsToSort[] = ['value' => '_id', 'label' => 'ID ' . esc_html__("A-Z", "customtables")];
		$fieldsToSort[] = ['value' => '_id desc', 'label' => 'ID ' . esc_html__("Z-A", "customtables")];

		$label = esc_html__("Published", "customtables") . ' ';
		$fieldsToSort[] = ['value' => '_published', 'label' => $label . esc_html__("A-Z", "customtables")];
		$fieldsToSort[] = ['value' => '_published desc', 'label' => $label . esc_html__("Z-A", "customtables")];

		foreach ($this->Table->fields as $row) {
			$fieldType = $row['type'];
			$fieldname = $row['fieldname'];

			if ($row['fieldtitle' . $this->Table->Languages->Postfix] != '')
				$fieldtitle = $row['fieldtitle' . $this->Table->Languages->Postfix];
			else
				$fieldtitle = $row['fieldtitle'];

			$typeParams = $row['typeparams'];

			if ($fieldType == 'string' or $fieldType == 'email' or $fieldType == 'url') {
				$fieldsToSort[] = ['value' => $fieldname, 'label' => $fieldtitle . ' ' . esc_html__("A-Z", "customtables")];
				$fieldsToSort[] = ['value' => $fieldname . ' desc', 'label' => $fieldtitle . ' ' . esc_html__("Z-A", "customtables")];
			} elseif ($fieldType == 'sqljoin') {
				$fieldsToSort[] = ['value' => $fieldname . '.sqljoin.' . $typeParams, 'label' => $fieldtitle . ' ' . esc_html__("A-Z", "customtables")];
				$fieldsToSort[] = ['value' => $fieldname . '.sqljoin.' . $typeParams . ' desc', 'label' => $fieldtitle . ' ' . esc_html__("Z-A", "customtables")];
			} elseif ($fieldType == 'phponadd' or $fieldType == 'phponchange') {
				$fieldsToSort[] = ['value' => $fieldname, 'label' => $fieldtitle . ' ' . esc_html__("A-Z", "customtables")];
				$fieldsToSort[] = ['value' => $fieldname . ' desc', 'label' => $fieldtitle . ' ' . esc_html__("Z-A", "customtables")];
			} elseif ($fieldType == 'int' or $fieldType == 'float' or $fieldType == 'ordering') {
				$fieldsToSort[] = ['value' => $fieldname, 'label' => $fieldtitle . ' ' . esc_html__("Min-Max", "customtables")];
				$fieldsToSort[] = ['value' => $fieldname . " desc", 'label' => $fieldtitle . ' ' . esc_html__("Max-Min", "customtables")];
			} elseif ($fieldType == 'changetime' or $fieldType == 'creationtime' or $fieldType == 'date') {
				$fieldsToSort[] = ['value' => $fieldname . " desc", 'label' => $fieldtitle . ' ' . esc_html__("New - Old", "customtables")];
				$fieldsToSort[] = ['value' => $fieldname, 'label' => $fieldtitle . ' ' . esc_html__("Old - New", "customtables")];
			} elseif ($fieldType == 'multilangstring') {
				$fieldsToSort[] = ['value' => $fieldname . $this->Table->Languages->Postfix, 'label' => $fieldtitle . ' ' . esc_html__("A-Z", "customtables")];
				$fieldsToSort[] = ['value' => $fieldname . $this->Table->Languages->Postfix . " desc", 'label' => $fieldtitle . ' ' . esc_html__("Z-A", "customtables")];
			} elseif ($fieldType == 'userid' or $fieldType == 'user') {
				$fieldsToSort[] = ['value' => $fieldname . '.user', 'label' => $fieldtitle . ' ' . esc_html__("A-Z", "customtables")];
				$fieldsToSort[] = ['value' => $fieldname . '.user desc', 'label' => $fieldtitle . ' ' . esc_html__("Z-A", "customtables")];
			}
		}
		return $fieldsToSort;
	}

	/**
	 * @throws Exception
	 * @since 3.2.2
	 */
	public function saveorder(): bool
	{
		// Get the input
		$pks = common::inputPostArray('cid', [], 'create-edit-record');
		$order = common::inputPostArray('order', [], 'create-edit-record');

		// Sanitize the input
		$pks = ArrayHelper::toInteger($pks);
		$order = ArrayHelper::toInteger($order);
		$realFieldName = '';

		foreach ($this->Table->fields as $field) {
			if ($field['type'] == 'ordering') {
				$realFieldName = $field['realfieldname'];
				break;
			}
		}

		if ($realFieldName == '')
			return false;

		for ($i = 0; $i < count($pks); $i++) {

			$data = [
				$realFieldName => $order[$i]
			];
			$whereClauseUpdate = new MySQLWhereClause();
			$whereClauseUpdate->addCondition($this->Table->realidfieldname, (int)$pks[$i]);
			database::update($this->Table->realtablename, $data, $whereClauseUpdate);
		}
		return true;
	}
}
