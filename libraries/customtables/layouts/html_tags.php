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
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use JESPagination;

class Twig_Html_Tags
{
	var CT $ct;
	var bool $isTwig;
	var array $button_objects = []; //Not clear where and how this variable used.

	function __construct(CT &$ct, $isTwig = true)
	{
		$this->ct = &$ct;
		$this->isTwig = $isTwig;

		$this->ct->LayoutVariables['captcha'] = null;
		$this->button_objects = [];//Not clear where and how this variable used.
	}

	function recordcount(): string
	{
		if ($this->ct->Env->frmt != 'html' and $this->ct->Env->frmt != '')
			return '';

		if (!isset($this->ct->Table)) {
			$this->ct->errors[] = '{{ html.recordcount }} - Table not loaded.';
			return '';
		}

		if (!isset($this->ct->Records)) {
			$this->ct->errors[] = '{{ html.recordcount }} - Records not loaded.';
			return '';
		}

		return '<span class="ctCatalogRecordCount">' . __("Found", "customtables") . ': ' . $this->ct->Table->recordcount
			. ' ' . __("Result(s)", "customtables") . '</span>';
	}

	function add($Alias_or_ItemId = ''): string
	{
		if ($this->ct->Env->print == 1 or ($this->ct->Env->frmt != 'html' and $this->ct->Env->frmt != ''))
			return '';

		if ($this->ct->Env->isPlugin)
			return '';

		$usergroups = $this->ct->Env->user->groups;

		$add_userGroup = (int)$this->ct->Params->addUserGroups;

		if (!$this->ct->Env->isUserAdministrator and !in_array($add_userGroup, $usergroups))
			return ''; //Not permitted

		if ($this->ct->Env->print == 1 or ($this->ct->Env->frmt != 'html' and $this->ct->Env->frmt != ''))
			return ''; //Not permitted

		if (defined('_JEXEC')) {
			$link = CUSTOMTABLES_MEDIA_HOME_URL;

			if ($Alias_or_ItemId != '' and is_numeric($Alias_or_ItemId) and (int)$Alias_or_ItemId > 0)
				$link .= '/index.php?option=com_customtables&amp;view=edititem&amp;returnto=' . $this->ct->Env->encoded_current_url . '&amp;Itemid=' . $Alias_or_ItemId;
			elseif ($Alias_or_ItemId != '')
				$link .= '/index.php/' . $Alias_or_ItemId . '?returnto=' . $this->ct->Env->encoded_current_url;
			else
				$link .= '/index.php?option=com_customtables&amp;view=edititem&amp;returnto=' . $this->ct->Env->encoded_current_url
					. '&amp;Itemid=' . $this->ct->Params->ItemId;

			if (!is_null($this->ct->Params->ModuleId))
				$link .= '&amp;ModuleId=' . $this->ct->Params->ModuleId;

			if (common::inputGetCmd('tmpl', '') != '')
				$link .= '&amp;tmpl=' . common::inputGetCmd('tmpl', '');

			if (!is_null($this->ct->Params->ModuleId))
				$link .= '&amp;ModuleId=' . $this->ct->Params->ModuleId;
		} elseif (defined('WPINC')) {
			$link = common::curPageURL();
			$link = CTMiscHelper::deleteURLQueryOption($link, 'view' . $this->ct->Table->tableid);
			$link .= (str_contains($link, '?') ? '&amp;' : '?') . 'view' . $this->ct->Table->tableid . '=edititem';
		} else {
			return '{{ html.add }} not supported.';
		}

		$alt = __("Add New", "customtables");

		if ($this->ct->Env->toolbarIcons != '')
			$img = '<i class="ba-btn-transition ' . $this->ct->Env->toolbarIcons . ' fa-plus-circle" data-icon="' . $this->ct->Env->toolbarIcons . ' fa-plus-circle" title="' . $alt . '"></i>';
		else {
			$img = '<img src="' . CUSTOMTABLES_MEDIA_WEBPATH . 'images/icons/new.png" alt="' . $alt . '" title="' . $alt . '" />';
		}

		return '<a href="' . $link . '" id="ctToolBarAddNew' . $this->ct->Table->tableid . '" class="toolbarIcons">' . $img . '</a>';
	}

	function importcsv(): string
	{
		if (defined('WPINC')) {
			return 'The tag "{{ html.importcsv() }}" not yet supported by WordPress version of the Custom Tables.';
		}

		if ($this->ct->Env->print == 1 or ($this->ct->Env->frmt != 'html' and $this->ct->Env->frmt != ''))
			return '';

		if ($this->ct->Env->isPlugin or (!is_null($this->ct->Params->ModuleId) and $this->ct->Params->ModuleId != 0))
			return '';

		if (!is_null($this->ct->Params->ModuleId))
			return '';

		$usergroups = $this->ct->Env->user->groups;
		if (!$this->ct->Env->isUserAdministrator and !in_array($this->ct->Params->addUserGroups, $usergroups))
			return ''; //Not permitted

		$max_file_size = CTMiscHelper::file_upload_max_size();

		$fileid = common::generateRandomString();
		$fieldid = '9999999';//some unique number. TODO
		$objectname = 'importcsv';

		HTMLHelper::_('behavior.formvalidator');

		$urlstr = '/index.php?option=com_customtables&amp;view=fileuploader&amp;tmpl=component&'
			. 'tableid=' . $this->ct->Table->tableid . '&'
			. 'task=importcsv&'
			. $objectname . '_fileid=' . $fileid
			. '&Itemid=' . $this->ct->Params->ItemId
			. (is_null($this->ct->Params->ModuleId) ? '' : '&ModuleId=' . $this->ct->Params->ModuleId)
			. '&fieldname=' . $objectname;

		return '<div>
                    <div id="ct_fileuploader_' . $objectname . '"></div>
                    <div id="ct_eventsmessage_' . $objectname . '"></div>
                    <form action="" name="ctUploadCSVForm" id="ctUploadCSVForm">
                	<script>
                        //UploadFileCount=1;
                    	ct_getUploader(' . $fieldid . ',"' . $urlstr . '",' . $max_file_size . ',"csv","ctUploadCSVForm",true,"ct_fileuploader_' . $objectname . '","ct_eventsmessage_' . $objectname . '","' . $fileid . '","'
			. $this->ct->Env->field_input_prefix . $objectname . '","ct_uploadedfile_box_' . $objectname . '")
                    </script>
                    <input type="hidden" name="' . $this->ct->Env->field_input_prefix . $objectname . '" id="' . $this->ct->Env->field_input_prefix . $objectname . '" value="" />
                    <input type="hidden" name="' . $this->ct->Env->field_input_prefix . $objectname . '_filename" id="' . $this->ct->Env->field_input_prefix . $objectname . '_filename" value="" />
			' . __("Permitted Max. File Size", "customtables") . ': ' . CTMiscHelper::formatSizeUnits($max_file_size) . '
                    </form>
                </div>
';
	}

	function pagination($show_arrow_icons = false): string
	{
		if ($this->ct->Env->print == 1 or ($this->ct->Env->frmt != 'html' and $this->ct->Env->frmt != ''))
			return '';

		if ($this->ct->Env->isPlugin or (!is_null($this->ct->Params->ModuleId) and $this->ct->Params->ModuleId != 0))
			return '';

		if ($this->ct->Table->recordcount <= $this->ct->Limit)
			return '';

		if (!is_null($this->ct->Params->ModuleId))
			return '';

		$pagination = new JESPagination($this->ct->Table->recordcount, $this->ct->LimitStart, $this->ct->Limit, '', $this->ct->Env->version, $show_arrow_icons);

		if ($this->ct->Env->version < 4)
			return '<div class="pagination">' . $pagination->getPagesLinks() . '</div>';
		else
			return '<div style="display:inline-block;">' . $pagination->getPagesLinks() . '</div>';
	}

	function limit($the_step = 5): string
	{
		if ($this->ct->Env->print == 1 or ($this->ct->Env->frmt != 'html' and $this->ct->Env->frmt != ''))
			return '';

		if ($this->ct->Env->isPlugin or (!is_null($this->ct->Params->ModuleId) and $this->ct->Params->ModuleId != 0))
			return '';

		if (!is_null($this->ct->Params->ModuleId))
			return '';

		$pagination = new JESPagination($this->ct->Table->recordcount, $this->ct->LimitStart, $this->ct->Limit, '', $this->ct->Env->version);
		return __("Show", "customtables") . ': ' . $pagination->getLimitBox($the_step);
	}

	function orderby(): string
	{
		if ($this->ct->Env->print == 1 or ($this->ct->Env->frmt != 'html' and $this->ct->Env->frmt != ''))
			return '';

		if ($this->ct->Env->isPlugin or (!is_null($this->ct->Params->ModuleId) and $this->ct->Params->ModuleId != 0))
			return '';

		if (!is_null($this->ct->Params->ModuleId))
			return '';

		if ($this->ct->Params->forceSortBy !== null and $this->ct->Params->forceSortBy != '')
			$this->ct->errors[] = __("Order by field is locked because 'Forced sort by field' parameter is set. Administrator - Check menu item settings.", "customtables");

		return __("Order by", "customtables") . ': ' . OrderingHTML::getOrderBox($this->ct->Ordering);
	}

	//$returnto must be provided already decoded
	function goback($defaultLabel = "Go Back", $image_icon = '', $attribute = '', string $returnto = ''): string
	{
		if ($defaultLabel === null)
			$defaultLabel = "Go Back";

		if (defined('_JEXEC'))
			$label = common::translate($defaultLabel);
		else
			$label = $defaultLabel;

		if ($this->ct->Env->print == 1 or ($this->ct->Env->frmt != 'html' and $this->ct->Env->frmt != ''))
			return '';

		if ($this->ct->Env->isPlugin or (!is_null($this->ct->Params->ModuleId) and $this->ct->Params->ModuleId != 0))
			return '';

		if (!is_null($this->ct->Params->ModuleId))
			return '';

		if ($returnto == '')
			$returnto = common::getReturnToURL() ?? '';

		if ($returnto == '')
			$returnto = $this->ct->Params->returnTo;

		if ($returnto == '')
			return '';

		if ($attribute == '' and $image_icon == '') {
			if ($this->ct->Env->toolbarIcons != '')
				$vlu = '<a href="' . $returnto . '"><i class="ba-btn-transition ' . $this->ct->Env->toolbarIcons
					. ' fa-angle-left" data-icon="' . $this->ct->Env->toolbarIcons . ' fa-angle-left" title="'
					. $label . '" style="margin-right:10px;"></i>' . $label . '</a>';
			else
				$vlu = '<a href="' . $returnto . '" class="ct_goback"><div>' . $label . '</div></a>';
		} else {

			$img = '';
			if (($this->ct->Env->toolbarIcons != '' or $image_icon == '') and $attribute == '')
				$img = '<i class="ba-btn-transition ' . $this->ct->Env->toolbarIcons . ' fa-angle-left" data-icon="'
					. $this->ct->Env->toolbarIcons . ' fa-angle-left" title="' . $label . '" style="margin-right:10px;"></i>';
			elseif ($this->ct->Env->toolbarIcons == '')
				$img = '<img src="' . $image_icon . '" alt="' . $label . '" />';

			$vlu = '<a href="' . $returnto . '" ' . $attribute . '><div>' . $img . $label . '</div></a>';
		}

		return $vlu;
	}

	function batch(): string
	{
		if ($this->ct->Env->print == 1 or ($this->ct->Env->frmt != 'html' and $this->ct->Env->frmt != ''))
			return '';

		if ($this->ct->Env->isPlugin)
			return '';

		$buttons = func_get_args();
		if (count($buttons) == 1) {
			if (is_array($buttons[0]))
				$buttons = $buttons[0];
		}

		$available_modes = $this->getAvailableModes();
		if (count($available_modes) == 0)
			return '';

		if (is_array($buttons))
			$buttons_array = $buttons;
		else
			$buttons_array = explode(',', $buttons);

		if (count($buttons_array) == 0)
			$buttons_array = $available_modes;

		$html_buttons = [];

		foreach ($buttons_array as $mode) {
			if ($mode == 'checkbox') {
				$html_buttons[] = '<input type="checkbox" id="esCheckboxAll' . $this->ct->Table->tableid . '" onChange="esCheckboxAllClicked(' . $this->ct->Table->tableid . ')" />';
			} else {
				if (in_array($mode, $available_modes)) {
					$rid = 'esToolBar_' . $mode . '_box_' . $this->ct->Table->tableid;

					switch ($mode) {
						case 'publish':
							$alt = __("Publish Selected", "customtables");
							break;
						case 'unpublish':
							$alt = __("Unpublish Selected", "customtables");
							break;
						case 'refresh':
							$alt = __("Refresh Selected", "customtables");
							break;
						case 'delete':
							$alt = __("Delete Selected", "customtables");
							break;
						default:
							return 'unsupported batch toolbar icon.';
					}

					if ($this->ct->Env->toolbarIcons != '') {
						$icons = ['publish' => 'fa-check-circle', 'unpublish' => 'fa-ban', 'refresh' => 'fa-sync', 'delete' => 'fa-trash'];
						$img = '<i class="ba-btn-transition ' . $this->ct->Env->toolbarIcons . ' ' . $icons[$mode] . '" data-icon="' . $this->ct->Env->toolbarIcons . ' ' . $icons[$mode] . '" title="' . $alt . '"></i>';
					} else
						$img = '<img src="' . CUSTOMTABLES_MEDIA_WEBPATH . 'images/icons/' . $mode . '.png" border="0" alt="' . $alt . '" title="' . $alt . '" />';

					$link = 'javascript:ctToolBarDO("' . $mode . '", ' . $this->ct->Table->tableid . ')';
					$html_buttons[] = '<div id="' . $rid . '" class="toolbarIcons"><a href=\'' . $link . '\'>' . $img . '</a></div>';
				}
			}
		}

		if (count($html_buttons) == 0)
			return '';

		return implode('', $html_buttons);
	}

	protected function getAvailableModes(): array
	{
		$available_modes = array();
		if ($this->ct->Env->user->id != 0) {
			$publish_userGroup = (int)$this->ct->Params->publishUserGroups;

			if ($this->ct->Env->user->checkUserGroupAccess($publish_userGroup)) {
				$available_modes[] = 'publish';
				$available_modes[] = 'unpublish';
			}

			$edit_userGroup = (int)$this->ct->Params->editUserGroups;
			if ($this->ct->Env->user->checkUserGroupAccess($edit_userGroup))
				$available_modes[] = 'refresh';

			$delete_userGroup = (int)$this->ct->Params->deleteUserGroups;
			if ($this->ct->Env->user->checkUserGroupAccess($delete_userGroup))
				$available_modes[] = 'delete';
		}
		return $available_modes;
	}

	function print($linktype = '', $label = '', $class = 'ctEditFormButton btn button'): string
	{
		if ($this->ct->Env->print == 1 or ($this->ct->Env->frmt != 'html' and $this->ct->Env->frmt != ''))
			return '';

		if ($this->ct->Env->isPlugin or (!is_null($this->ct->Params->ModuleId) and $this->ct->Params->ModuleId != 0))
			return '';

		if (!is_null($this->ct->Params->ModuleId))
			return '';

		$link = $this->ct->Env->current_url . (!str_contains($this->ct->Env->current_url, '?') ? '?' : '&') . 'tmpl=component&amp;print=1';

		if (common::inputGetInt('moduleid', 0) != 0) {
			//search module

			$moduleid = common::inputGetInt('moduleid', 0);
			$link .= '&amp;moduleid=' . $moduleid;

			//keyword search
			$inputbox_name = 'eskeysearch_' . $moduleid;
			$link .= '&amp;' . $inputbox_name . '=' . common::inputGetString($inputbox_name, '');
		}

		$onClick = 'window.open("' . $link . '","win2","status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no");return false;';
		if ($this->ct->Env->print == 1) {
			$vlu = '<p><a href="#" onclick="window.print();return false;"><img src="' . CUSTOMTABLES_MEDIA_WEBPATH . 'images/icons/print.png" alt="' . __("Print", "customtables") . '"  /></a></p>';
		} else {
			if ($label == '')
				$label = __("Print", "customtables");

			if ($linktype != '')
				$vlu = '<a href="#" onclick=\'' . $onClick . '\'><i class="ba-btn-transition fas fa-print" data-icon="fas fa-print" title="' . $label . '"></i></a>';
			else
				$vlu = '<input type="button" class="' . $class . '" value="' . $label . '" onClick=\'' . $onClick . '\' />';
		}
		return $vlu;
	}

	/**
	 * @throws Exception
	 * @since 3.2.8
	 */
	function search($list_of_fields_string_or_array = null, $class = '', $reload = false, $improved = ''): string
	{
		if (is_string($reload))
			$reload = $reload == 'reload';

		if ($list_of_fields_string_or_array === null)
			return '{{ html.search() }} tag requires at least one field name.';

		if ($this->ct->Env->print == 1 or ($this->ct->Env->frmt != 'html' and $this->ct->Env->frmt != ''))
			return '';

		if ($this->ct->Env->isPlugin or (!is_null($this->ct->Params->ModuleId) and $this->ct->Params->ModuleId != 0))
			return '';

		if (!is_null($this->ct->Params->ModuleId))
			return '';

		if (is_array($list_of_fields_string_or_array))
			$list_of_fields_string_array = $list_of_fields_string_or_array;
		else
			$list_of_fields_string_array = explode(',', $list_of_fields_string_or_array);

		if (count($list_of_fields_string_array) == 0) {
			$this->ct->errors[] = 'Search box: Please specify a field name.';
			return '';
		}

		$first_fld_layout = null;

		//Clean list of fields
		$list_of_fields = [];

		$wrong_list_of_fields = [];
		foreach ($list_of_fields_string_array as $field_name_string_pair) {

			$field_name_pair = explode(':', $field_name_string_pair);
			$field_name_string = $field_name_pair[0];
			if ($first_fld_layout === null and isset($field_name_pair[1]))
				$first_fld_layout = $field_name_pair[1];

			if ($field_name_string == '_id') {
				$list_of_fields[] = '_id';
			} elseif ($field_name_string == '_published') {
				$list_of_fields[] = '_published';
			} else {
				//Check if field name is exist in selected table
				$fld = Fields::FieldRowByName($field_name_string, $this->ct->Table->fields);

				if (!is_array($fld)) {
					$this->ct->errors[] = 'Search box: Field name "' . $field_name_string . '" not found.';
					return '';
				}

				if (count($fld) > 0)
					$list_of_fields[] = $field_name_pair[0];
				else
					$wrong_list_of_fields[] = $field_name_string_pair;
			}
		}

		if (count($list_of_fields) == 0) {
			$this->ct->errors[] = 'Search box: Field' . (count($wrong_list_of_fields) > 0 ? 's' : '') . ' "' . implode(',', $wrong_list_of_fields) . '" not found.';
			return '';
		}

		$vlu = 'Search field name is wrong';

		require_once(CUSTOMTABLES_LIBRARIES_PATH
			. DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'searchinputbox.php');

		$SearchBox = new SearchInputBox($this->ct, 'esSearchBox');
		$first_fld = [];
		$first_field_type = '';

		foreach ($list_of_fields as $field_name_string) {
			if ($field_name_string == '_id') {
				$fld = array(
					'id' => 0,
					'fieldname' => '_id',
					'type' => '_id',
					'typeparams' => '',
					'fieldtitle' . $this->ct->Languages->Postfix => __("Record ID", "customtables"),
					'realfieldname' => $this->ct->Table->realtablename,
					'isrequired' => false,
					'defaultvalue' => null,
					'valuerule' => null,
					'valuerulecaption' => null
				);
			} elseif ($field_name_string == '_published') {
				$fld = array(
					'id' => 0,
					'fieldname' => '_published',
					'type' => '_published',
					'typeparams' => '',
					'fieldtitle' . $this->ct->Languages->Postfix => __("Published", "customtables"),
					'realfieldname' => 'listing_published',
					'isrequired' => false,
					'defaultvalue' => null,
					'valuerule' => null,
					'valuerulecaption' => null
				);
			}

			if ($first_field_type == '') {
				$first_field_type = $fld['type'];
				$first_fld = $fld;
			} else {
				// If field types are mixed then use string search
				if ($first_field_type != $fld['type'])
					$first_field_type = 'string';
			}
		}
		$first_fld['type'] = $first_field_type;

		if (count($list_of_fields) > 1) {
			$first_fld['fields'] = $list_of_fields;
			$first_fld['typeparams'] = '';
		}

		$first_fld['layout'] = $first_fld_layout;

		//Add control elements
		$fieldTitles = $this->getFieldTitles($list_of_fields);
		$field_title = implode(' ' . __("or", "customtables") . ' ', $fieldTitles);
		$cssClass = 'ctSearchBox';

		if ($class != '')
			$cssClass .= ' ' . $class;

		if ($improved == 'improved')
			$cssClass .= ' ct_improved_selectbox';
		elseif ($improved == 'virtualselect')
			$cssClass .= ($cssClass == '' ? '' : ' ') . ' ct_virtualselect_selectbox';

		$onchange = $reload ? 'ctSearchBoxDo();' : null;//action should be a space not empty or this.value=this.value

		$objectName = $first_fld['fieldname'];

		if (count($first_fld) == 0)
			return 'Unsupported field type or field not found.';

		$vlu = $SearchBox->renderFieldBox('es_search_box_', $objectName, $first_fld,
			$cssClass, '0',
			'', '', $onchange, $field_title);//action should be a space not empty or
		//0 because it's not an edit box, and we pass onChange value even " " is the value;

		$field2search = $this->prepareSearchElement($first_fld);
		$vlu .= '<input type=\'hidden\' ctSearchBoxField=\'' . $field2search . '\' />';

		return $vlu;
	}

	protected function getFieldTitles($list_of_fields): array
	{
		$field_titles = [];
		foreach ($list_of_fields as $fieldname_string) {

			$fieldname_pair = explode(':', $fieldname_string);
			$fieldname = $fieldname_pair[0];

			if ($fieldname == '_id')
				$field_titles[] = __("Record ID", "customtables");
			elseif ($fieldname == '_published')
				$field_titles[] = __("Published", "customtables");
			else {
				foreach ($this->ct->Table->fields as $fld) {
					if ($fld['fieldname'] == $fieldname) {
						$field_titles[] = $fld['fieldtitle' . $this->ct->Languages->Postfix];
						break;
					}
				}
			}
		}
		return $field_titles;
	}

	protected function prepareSearchElement($fld): string
	{
		if (isset($fld['fields']) and count($fld['fields']) > 0) {
			return 'es_search_box_' . $fld['fieldname'] . ':' . implode(';', $fld['fields']) . ':';
		} else {
			return 'es_search_box_' . $fld['fieldname'] . ':' . $fld['fieldname'] . ':';
		}
	}

	function searchbutton($label = '', $class_ = ''): string
	{
		if ($this->ct->Env->print == 1 or ($this->ct->Env->frmt != 'html' and $this->ct->Env->frmt != ''))
			return '';

		if ($this->ct->Env->isPlugin or (!is_null($this->ct->Params->ModuleId) and $this->ct->Params->ModuleId != 0))
			return '';

		if (!is_null($this->ct->Params->ModuleId))
			return '';

		$class = 'ctSearchBox';

		if (isset($class_) and $class_ != '')
			$class .= ' ' . $class_;
		else
			$class .= ' btn button-apply btn-primary';

		$default_Label = __("Search", "customtables");

		if ($label == common::ctStripTags($label)) {
			if ($this->ct->Env->toolbarIcons != '') {
				$img = '<i class=\'' . $this->ct->Env->toolbarIcons . ' fa-search\' data-icon=\'' . $this->ct->Env->toolbarIcons . ' fa-search\' title=\'' . $label . '\'></i>';
				$labelHtml = ($label !== '' ? '<span style=\'margin-left:10px;\'>' . $label . '</span>' : '');
			} else {
				$img = '';

				if ($label == '')
					$label = $default_Label;

				$labelHtml = ($label !== '' ? '<span>' . $label . '</span>' : '');
			}
			return '<button class=\'' . $class . '\' onClick=\'ctSearchBoxDo()\' title=\'' . $default_Label . '\'>' . $img . $labelHtml . '</button>';
		} else {
			return '<button class=\'' . $class . '\' onClick=\'ctSearchBoxDo()\' title=\'' . $default_Label . '\'>' . $label . '</button>';
		}
	}

	function searchreset($label = '', $class_ = ''): string
	{
		if ($this->ct->Env->print == 1 or ($this->ct->Env->frmt != 'html' and $this->ct->Env->frmt != ''))
			return '';

		if ($this->ct->Env->isPlugin or (!is_null($this->ct->Params->ModuleId) and $this->ct->Params->ModuleId != 0))
			return '';

		if (!is_null($this->ct->Params->ModuleId))
			return '';

		$class = 'ctSearchBox';

		if (isset($class_) and $class_ != '')
			$class .= ' ' . $class_;
		else
			$class .= ' btn button-apply btn-primary';

		$default_Label = __("Search Reset", "customtables");
		if ($label == common::ctStripTags($label)) {
			if ($this->ct->Env->toolbarIcons != '') {
				$img = '<i class=\'' . $this->ct->Env->toolbarIcons . ' fa-times\' data-icon=\'' . $this->ct->Env->toolbarIcons . ' fa-times\' title=\'' . $label . '\'></i>';
				$labelHtml = ($label !== '' ? '<span style=\'margin-left:10px;\'>' . $label . '</span>' : '');
			} else {
				$img = '';

				if ($label == '')
					$label = $default_Label;

				$labelHtml = ($label !== '' ? '<span>' . $label . '</span>' : '');
			}
			return '<button class=\'' . $class . '\' onClick=\'ctSearchReset()\' title=\'' . $default_Label . '\'>' . $img . $labelHtml . '</button>';
		} else {
			return '<button class=\'' . $class . '\' onClick=\'ctSearchReset()\' title=\'' . $default_Label . '\'>' . $label . '</button>';
		}
	}

	function message($text, $type = 'Message'): ?string
	{
		if ($this->ct->Env->print == 1 or ($this->ct->Env->frmt != 'html' and $this->ct->Env->frmt != ''))
			return '';

		if ($this->ct->Env->isPlugin or (!is_null($this->ct->Params->ModuleId) and $this->ct->Params->ModuleId != 0))
			return '';

		if (!is_null($this->ct->Params->ModuleId))
			return '';

		if ($type === 'error')
			$this->ct->errors[] = $text;
		else
			$this->ct->messages[] = $text;

		return null;
	}

	function navigation($list_type = 'list', $ul_css_class = ''): string
	{
		if ($this->ct->Env->frmt != 'html' and $this->ct->Env->frmt != '')
			return '';

		if (!is_null($this->ct->Params->ModuleId))
			return '';

		$PathValue = $this->CleanNavigationPath($this->ct->Filter->PathValue);
		if (count($PathValue) == 0)
			return '';
		elseif ($list_type == '' or $list_type == 'list') {
			$vlu = '<ul' . ($ul_css_class != '' ? ' class="' . $ul_css_class . '"' : '') . '><li>' . implode('</li><li>', $PathValue) . '</li></ul>';
			return $vlu;
		} elseif ($list_type == 'comma')
			return implode(',', $PathValue);
		else
			return 'navigation: Unknown list type';
	}

	protected function CleanNavigationPath($thePath): array
	{
		//Returns a list of unique search path criteria - eliminates duplicates
		$newPath = array();
		if (count($thePath) == 0)
			return $newPath;

		for ($i = count($thePath) - 1; $i >= 0; $i--) {
			$item = $thePath[$i];
			if (count($newPath) == 0)
				$newPath[] = $item;
			else {
				$found = false;
				foreach ($newPath as $newitem) {
					if (str_contains($newitem, $item)) {
						$found = true;
						break;
					}
				}
				if (!$found)
					$newPath[] = $item;
			}
		}
		return array_reverse($newPath);
	}

	/**
	 * @throws Exception
	 * @since 3.2.8
	 */
	function captcha(): string
	{
		if (!$this->ct->Env->advancedTagProcessor)
			return '{{ html.captcha }} - Captcha Available in PRO Version only.';

		if ($this->ct->Env->print == 1 or ($this->ct->Env->frmt != 'html' and $this->ct->Env->frmt != ''))
			return '';

		if ($this->ct->Env->isPlugin or (!is_null($this->ct->Params->ModuleId) and $this->ct->Params->ModuleId != 0))
			return '';

		if (!is_null($this->ct->Params->ModuleId))
			return '';

		$site_key = null;
		$secret_key = null;

		$functionParams = func_get_args();
		if (isset($functionParams[0]))
			$site_key = $functionParams[0];

		if (isset($functionParams[1]))
			$secret_key = $functionParams[1];

		if (defined('_JEXEC')) {
			$app = Factory::getApplication();
			$document = $app->getDocument();
			$document->addCustomTag('<script src="https://www.google.com/recaptcha/api.js"></script>');
		}

		$this->ct->LayoutVariables['captcha'] = true;
		$this->ct->LayoutVariables['captcha_secret_key'] = $secret_key;

		if ($site_key === null)
			return 'The tag "{{ html.captcha(SITE_KEY) }}" please provide the reCaptcha Site Key';

		if ($secret_key === null)
			return 'The tag "{{ html.captcha(SITE_KEY, SECRET_KEY) }}" please provide the reCaptcha Secret Key';

		return '
    <div id="my_captcha_div"
		class="g-recaptcha"
		data-sitekey="' . $site_key . '"
		data-callback="recaptchaCallback">
	</div>';
	}

	function button($type = 'save', $title = '', $redirectlink = null, $optional_class = '')
	{
		if (defined('_JEXEC')) {
			if ($this->ct->app->getName() == 'administrator')   //since   3.2
				$formName = 'adminForm';
			else {
				if ($this->ct->Env->isModal)
					$formName = 'ctEditModalForm';
				else {
					$formName = 'ctEditForm';
					$formName .= $this->ct->Params->ModuleId;
				}
			}

		} elseif (defined('WPINC')) {
			$formName = 'ctEditForm';
		}

		if ($this->ct->Env->frmt != '' and $this->ct->Env->frmt != 'html')
			return '1';

		if ($this->ct->Env->isPlugin)
			return '2';

		if ($redirectlink === null and !is_null($this->ct->Params->returnTo))
			$redirectlink = $this->ct->Params->returnTo;

		switch ($type) {
			case 'save':
				$vlu = $this->renderSaveButton($optional_class, $title, $formName);
				break;

			case 'saveandclose':
				$vlu = $this->renderSaveAndCloseButton($optional_class, $title, $redirectlink, $formName);
				break;

			case 'saveandprint':
				$vlu = $this->renderSaveAndPrintButton($optional_class, $title, $redirectlink, $formName);
				break;

			case 'saveascopy':

				if (!isset($this->ct->Table->record[$this->ct->Table->realidfieldname]) or $this->ct->Table->record[$this->ct->Table->realidfieldname] == 0)
					$vlu = '';
				else
					$vlu = $this->renderSaveAsCopyButton($optional_class, $title, $redirectlink, $formName);
				break;

			case 'close':
			case 'cancel':
				$vlu = $this->renderCancelButton($optional_class, $title, $redirectlink, $formName);
				break;

			case 'delete':
				$vlu = $this->renderDeleteButton($optional_class, $title, $redirectlink);
				break;

			default:
				$vlu = '';

		}//switch

		//Not clear where and how this variable used.
		if ($this->ct->Env->frmt == 'json') {
			$this->button_objects[] = ['type' => $type, 'title' => $title, 'redirectlink' => $redirectlink];
			return $title;
		}

		return $vlu;
	}

	protected function renderSaveButton($optional_class, $title, $formName): string
	{
		if ($title == '')
			$title = __("Save", "customtables");

		return $this->renderButtonHTML($optional_class, $title, $formName, "customtables_button_save", $this->ct->Env->encoded_current_url,
			true, "saveandcontinue");
	}

	protected function renderButtonHTML($optional_class, string $title, $formName, string $buttonId,
										string $redirect, bool $checkCaptcha, string $task): string
	{
		if ($this->ct->Env->frmt == 'json')
			return $title;

		$attribute = '';
		if ($checkCaptcha and ($this->ct->LayoutVariables['captcha'] ?? null))
			$attribute = ' disabled="disabled"';

		if ($optional_class != '')
			$the_class = $optional_class;
		else
			$the_class = 'ctEditFormButton btn button-apply btn-success';

		$the_class .= ' validate';

		$isModal = ($this->ct->Env->isModal ? 'true' : 'false');
		$parentField = common::inputGetCmd('parentfield');

		if ($parentField === null)
			$onclick = 'setTask(event, "' . $task . '","' . $redirect . '",true,"' . $formName . '",' . $isModal . ',null);';
		else
			$onclick = 'setTask(event, "' . $task . '","' . $redirect . '",true,"' . $formName . '",' . $isModal . ',"' . $parentField . '");';

		return '<input id="' . $buttonId . '" type="submit" class="' . $the_class . '"' . $attribute . ' onClick=\'' . $onclick . '\' value="' . $title . '">';
	}

	protected function renderSaveAndCloseButton($optional_class, $title, $redirectLink, $formName): string
	{
		if ($title == '')
			$title = __("Save & Close", "customtables");

		$returnToEncoded = common::makeReturnToURL($redirectLink);
		return $this->renderButtonHTML($optional_class, $title, $formName, "customtables_button_saveandclose", $returnToEncoded, true, "save");
	}

	protected function renderSaveAndPrintButton($optional_class, $title, $redirectLink, $formName): string
	{
		if ($title == '')
			$title = __("Next", "customtables");

		$returnToEncoded = common::makeReturnToURL($redirectLink);

		return $this->renderButtonHTML($optional_class, $title, $formName, "customtables_button_saveandprint", $returnToEncoded, true, "saveandprint");
	}

	protected function renderSaveAsCopyButton($optional_class, $title, $redirectLink, $formName): string
	{
		if ($title == '')
			$title = __("Save as Copy", "customtables");

		$returnToEncoded = common::makeReturnToURL($redirectLink);

		return $this->renderButtonHTML($optional_class, $title, $formName, "customtables_button_saveandcopy", $returnToEncoded, true, "saveascopy");
	}

	protected function renderCancelButton($optional_class, $title, $redirectLink, $formName): string
	{
		if ($this->ct->Env->isModal)
			return '';

		if ($title == '')
			$title = __("Cancel", "customtables");

		if ($optional_class != '')
			$cancel_class = $optional_class;
		else
			$cancel_class = 'ctEditFormButton btn button-cancel';

		$returnToEncoded = common::makeReturnToURL($redirectLink);

		return $this->renderButtonHTML($cancel_class, $title, $formName, "customtables_button_cancel", $returnToEncoded, false, "cancel");
	}

	protected function renderDeleteButton($optional_class, $title, $redirectLink)
	{
		if ($title == '')
			$title = __("Delete", "customtables");

		if ($this->ct->Env->frmt == 'json')
			return $title;

		if ($optional_class != '')
			$class = $optional_class;
		else
			$class = 'ctEditFormButton btn button-cancel';

		$returnToEncoded = common::makeReturnToURL($redirectLink) ?? '';

		return '<input id="customtables_button_delete" type="button" class="' . $class . '" value="' . $title . '"
				onClick=\'
                if (confirm("' . __("Do you want to delete?", "customtables") . '"))
                {
                    this.form.task.value="delete";
                    ' . ($returnToEncoded != '' ? 'this.form.returnto.value="' . $returnToEncoded . '";' : '') . '
                    this.form.submit();
                }
                \'>' . PHP_EOL;
	}

	function tablehead(): string
	{
		$result = '<thead>';
		$head_columns = func_get_args();

		foreach ($head_columns as $head_column)
			$result .= '<th>' . $head_column . '</th>';

		$result .= '</thead>';

		return $result;
	}

	function recordlist(): string
	{
		return $this->id_list();
	}

	protected function id_list(): string
	{
		if (!isset($this->ct->Table)) {
			$this->ct->errors[] = '{{ record.list }} - Table not loaded.';
			return '';
		}

		if (!isset($this->ct->Records)) {
			$this->ct->errors[] = '{{ record.list }} - Records not loaded.';
			return '';
		}

		if ($this->ct->Table->recordlist === null)
			$this->ct->getRecordList();

		return implode(',', $this->ct->Table->recordlist);
	}

	/**
	 * @throws Exception
	 * @since 3.2.8
	 */
	function toolbar(): string
	{
		if ($this->ct->Env->print == 1 or ($this->ct->Env->frmt != 'html' and $this->ct->Env->frmt != ''))
			return '';

		if ($this->ct->Env->isPlugin)
			return '';

		$modes = func_get_args();

		$edit_userGroup = (int)$this->ct->Params->editUserGroups;
		$publish_userGroup = (int)$this->ct->Params->publishUserGroups;
		if ($publish_userGroup == 0)
			$publish_userGroup = $edit_userGroup;

		$delete_userGroup = (int)$this->ct->Params->deleteUserGroups;
		if ($delete_userGroup == 0)
			$delete_userGroup = $edit_userGroup;

		$isEditable = CTUser::checkIfRecordBelongsToUser($this->ct, $edit_userGroup);
		$isPublishable = CTUser::checkIfRecordBelongsToUser($this->ct, $publish_userGroup);
		$isDeletable = CTUser::checkIfRecordBelongsToUser($this->ct, $delete_userGroup);

		$RecordToolbar = new RecordToolbar($this->ct, $isEditable, $isPublishable, $isDeletable);

		if (count($modes) == 0)
			$modes = ['edit', 'refresh', 'publish', 'delete'];

		if ($this->ct->Table->record === null)
			return '';

		$icons = [];
		foreach ($modes as $mode)
			$icons[] = $RecordToolbar->render($this->ct->Table->record, $mode);

		return implode('', $icons);
	}

	function checkboxcount(): string
	{
		return '<span id="ctTable' . $this->ct->Table->tableid . 'CheckboxCount">0</span>';
	}
}
