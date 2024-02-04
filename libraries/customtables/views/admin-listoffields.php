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
if (!defined('_JEXEC') and !defined('WPINC')) {
	die('Restricted access');
}

use Exception;
use Joomla\CMS\HTML\HTMLHelper;
use JoomlaBasicMisc;

class ListOfFields
{
	var CT $ct;
	var ?array $items;
	var ?string $editLink;

	var ?bool $canState;
	var ?bool $canDelete;
	var ?bool $canEdit;
	var ?bool $saveOrder;
	var string $dbPrefix;
	var int $tableid;

	function __construct(CT $ct, ?array $items = null, ?bool $canState = null, ?bool $canDelete = null, ?bool $canEdit = null, ?bool $saveOrder = null)
	{
		$this->ct = $ct;
		$this->items = $items;
		if (isset($this->ct->Table))
			$this->editLink = "index.php?option=com_customtables&view=listoffields&task=fields.edit&tableid=" . $this->ct->Table->tableid;
		else
			$this->editLink = null;

		$this->canState = $canState ?? false;
		$this->canDelete = $canDelete ?? false;
		$this->canEdit = $canEdit ?? false;
		$this->saveOrder = $saveOrder ?? false;
		$this->dbPrefix = database::getDBPrefix();
	}

	/**
	 * @throws Exception
	 * @since 3.2.2
	 */
	function getListQuery(int $tableId, $published = null, $search = null, $type = null, $orderCol = null, $orderDirection = null, $limit = 0, $start = 0, bool $returnQueryString = false)
	{
		$this->tableid = common::inputGetInt('tableid', 0);
		$serverType = database::getServerType();

		$selects = [
			'a.*',
			'(SELECT tabletitle FROM #__customtables_tables AS tables WHERE tables.id=a.tableid) AS tabletitle'
		];

		if ($serverType == 'postgresql')
			$selects[] = 'CASE WHEN customfieldname!=\'\' THEN customfieldname ELSE CONCAT(\'es_\',fieldname) END AS realfieldname';
		else
			$selects[] = 'IF(customfieldname!=\'\', customfieldname, CONCAT(\'es_\',fieldname)) AS realfieldname';

		$whereClause = new MySQLWhereClause();
		$whereClause->addCondition('tableid', $tableId);

		$whereClausePublished = new MySQLWhereClause();
		// Filter by published state
		if (is_numeric($published))
			$whereClausePublished->addCondition('a.published', (int)$published);
		elseif ($published === null or $published === '') {
			$whereClausePublished->addOrCondition('a.published', 0);
			$whereClausePublished->addOrCondition('a.published', 1);
		}

		if ($whereClausePublished->hasConditions())
			$whereClause->addNestedCondition($whereClausePublished);

		// Filter by search.
		if (!empty($search)) {
			$whereClauseSearch = new MySQLWhereClause();
			if (stripos($search, 'id:') === 0) {
				$whereClauseSearch->addCondition('a.id', (int)substr($search, 3));
			} else {
				$whereClauseSearch->addOrCondition('a.fieldname', '%' . $search . '%', 'LIKE');
				$whereClauseSearch->addOrCondition('a.fieldtitle', '%' . $search . '%', 'LIKE');
			}
			if ($whereClauseSearch->hasConditions())
				$whereClause->addNestedCondition($whereClauseSearch);
		}

		// Filter by Type
		if ($type !== null)
			$whereClause->addCondition('a.type', (int)$type);

		// Filter by Type
		if ($this->tableid != 0)
			$whereClause->addCondition('a.tableid', (int)$this->tableid);

		return database::loadAssocList('#__customtables_fields AS a', $selects, $whereClause, $orderCol, $orderDirection, $limit, $start, null, $returnQueryString);
	}

	public function renderBody(): string
	{
		$result = '';

		foreach ($this->items as $i => $item) {

			$canCheckin = $this->ct->Env->user->authorise('core.manage', 'com_checkin') || $item->checked_out == $this->ct->Env->user->id || $item->checked_out == 0;
			$userChkOut = new CTUser($item->checked_out);
			$result .= $this->renderBodyLine($item, $i, $canCheckin, $userChkOut);
		}
		return $result;
	}

	protected function renderBodyLine(object $item, int $i, $canCheckin, $userChkOut): string
	{
		$hashRealTableName = database::realTableName($this->ct->Table->realtablename);
		$hashRealTableName = str_replace($this->dbPrefix, '#__', $hashRealTableName);

		$result = '<tr class="row' . ($i % 2) . '" data-draggable-group="' . $this->ct->Table->tableid . '">';

		if ($this->canState or $this->canDelete) {
			$result .= '<td class="text-center">';

			if ($item->checked_out) {
				if ($canCheckin)
					$result .= HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->fieldname);
				else
					$result .= '&#9633;';
			} else
				$result .= HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->fieldname);

			$result .= '</td>';
		}

		if ($this->canEdit) {
			$result .= '<td class="text-center d-none d-md-table-cell">';

			$iconClass = '';
			if (!$this->saveOrder)
				$iconClass = ' inactive" title="' . common::translate('JORDERINGDISABLED');

			$result .= '<span class="sortable-handler' . $iconClass . '"><span class="icon-ellipsis-v" aria-hidden="true"></span></span>';

			if ($this->saveOrder)
				$result .= '<input type="text" name="order[]" size="5" value="' . $item->ordering . '" class="width-20 text-area-order hidden">';

			$result .= '</td>';
		}

		$result .= '<td><div class="name">';

		if ($this->canEdit) {
			$result .= '<a href="' . $this->editLink . '&id=' . $item->id . '">' . common::escape($item->fieldname) . '</a>';
			if ($item->checked_out)
				$result .= HtmlHelper::_('jgrid.checkedout', $i, $userChkOut->name, $item->checked_out_time, 'listoffields.', $canCheckin);
		} else
			$result .= common::escape($item->fieldname);

		if ($this->ct->Env->advancedTagProcessor and $this->ct->Table->realtablename != '')
			$result .= '<br/><span style="color:grey;">' . $hashRealTableName . '.' . $item->realfieldname . '</span>';

		$result .= '</div></td>';

		$result .= '<td><div class="name"><ul style="list-style: none !important;margin-left:0;padding-left:0;">';

		$item_array = (array)$item;
		$moreThanOneLang = false;

		foreach ($this->ct->Languages->LanguageList as $lang) {
			$fieldTitle = 'fieldtitle';
			$fieldDescription = 'description';
			if ($moreThanOneLang) {
				$fieldTitle .= '_' . $lang->sef;
				$fieldDescription .= '_' . $lang->sef;

				if (!array_key_exists($fieldTitle, $item_array)) {
					Fields::addLanguageField('#__customtables_fields', 'fieldtitle', $fieldTitle);
					$item_array[$fieldTitle] = '';
				}

				if (!array_key_exists($fieldTitle, $item_array)) {
					Fields::addLanguageField('#__customtables_fields', 'description', $fieldDescription);
					$item_array[$fieldDescription] = '';
				}
			}

			$result .= '<li>' . (count($this->ct->Languages->LanguageList) > 1 ? $lang->title . ': ' : '') . '<b>' . common::escape($item_array[$fieldTitle]) . '</b></li>';
			$moreThanOneLang = true; //More than one language installed
		}

		$result .= '
                        </ul>
                    </div>
                </td>';

		$result .= '<td>' . common::translate($item->typeLabel) . '</td>';
		$result .= '<td>' . common::escape($item->typeparams) . $this->checkTypeParams($item->type, $item->typeparams ?? '') . '</td>';
		$result .= '<td>' . common::translate($item->isrequired) . '</td>';
		$result .= '<td>' . common::escape($this->ct->Table->tabletitle) . '</td>';
		$result .= '<td class="text-center btns d-none d-md-table-cell">';
		if ($this->canState) {
			if ($item->checked_out) {
				if ($canCheckin)
					$result .= HtmlHelper::_('jgrid.published', $item->published, $i, 'listoffields.', true, 'cb');
				else
					$result .= HtmlHelper::_('jgrid.published', $item->published, $i, 'listoffields.', false, 'cb');

			} else {

				$result .= HtmlHelper::_('jgrid.published', $item->published, $i, 'listoffields.', true, 'cb');
			}
		} else {
			$result .= HtmlHelper::_('jgrid.published', $item->published, $i, 'listoffields.', false, 'cb');
		}
		$result .= '</td>';

		$result .= '<td class="d-none d-md-table-cell">' . $item->id . '</td>';
		$result .= '</tr>';

		return $result;
	}


	protected function checkTypeParams(string $type, string $typeParams): string
	{
		if ($type == 'sqljoin' or $type == 'records') {
			$params = JoomlaBasicMisc::csv_explode(',', $typeParams, '"', false);

			$error = [];

			if ($params[0] == '')
				$error[] = 'Join Table not selected';

			if (!isset($params[1]) or $params[1] == '')
				$error[] = 'Join Field not selected';

			return '<br/><p class="alert-error">' . implode(', ', $error) . '</p>';
		}
		return '';
	}

	function save(?int $tableId, ?int $fieldId): bool
	{
		$fieldId = Fields::saveField($tableId, $fieldId);

		if ($fieldId == null)
			return false;

		return true;
	}

	function getFieldTypesFromXML(bool $onlyWordpress = false): ?array
	{
		$xml = JoomlaBasicMisc::getXMLData('fieldtypes.xml');
		if (count($xml) == 0 or !isset($xml->type))
			return null;

		$options = [];

		foreach ($xml->type as $type) {
			$type_att = $type->attributes();
			$is4Pro = (bool)(int)$type_att->proversion;
			$isDeprecated = (bool)(int)$type_att->deprecated;

			$active = true;

			if ($onlyWordpress) {
				if (!((string)$type_att->wordpress == 'true'))
					$active = false;
			}

			if ($active and !$isDeprecated) {
				$option = [];

				$option['name'] = (string)$type_att->ct_name;
				$option['label'] = (string)$type_att->label;
				$option['description'] = (string)$type_att->description;
				$option['proversion'] = $is4Pro;
				$options[] = $option;
			}
		}
		return $options;
	}
}