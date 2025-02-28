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

class Search_radio extends BaseSearch
{
	function __construct(CT &$ct, Field $field, string $moduleName, array $attributes, int $index, string $where, string $whereList, string $objectName)
	{
		parent::__construct($ct, $field, $moduleName, $attributes, $index, $where, $whereList, $objectName);
		BaseInputBox::selectBoxAddCSSClass($this->attributes);
	}

	function render($value): string
	{
		$options = [];
		$options[] = '<option value="" ' . ($value == '' ? 'SELECTED' : '') . '>- ' . esc_html__("Select", "customtables") . ' ' . $this->field->title . '</option>';

		foreach ($this->field->params as $param)
			$options[] = '<option value="' . $param . '" ' . ($value == $param ? 'SELECTED' : '') . '>' . $param . '</option>';

		return '<select'
			. ' id="' . $this->objectName . '"'
			. ' name="' . $this->objectName . '"'
			. BaseInputBox::attributes2String($this->attributes) . '>' . implode('', $options) . '</select>';
	}
}