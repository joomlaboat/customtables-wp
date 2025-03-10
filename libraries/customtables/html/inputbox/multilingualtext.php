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
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;

class InputBox_multilingualtext extends BaseInputBox
{
	function __construct(CT &$ct, Field $field, ?array $row, array $option_list = [], array $attributes = [])
	{
		parent::__construct($ct, $field, $row, $option_list, $attributes);
	}

	/**
	 * @throws Exception
	 * @since 3.0.0
	 */
	function render(?string $value, ?string $defaultValue): string
	{
		$RequiredLabel = 'Field is required';
		$result = '';
		$firstLanguage = true;
		foreach ($this->ct->Languages->LanguageList as $lang) {
			if ($firstLanguage) {
				$postfix = '';
				$firstLanguage = false;
			} else
				$postfix = '_' . $lang->sef;

			$fieldname = $this->field->fieldname . $postfix;

			if ($this->ct->isRecordNull($this->row)) {
				$value = null; // Because the value has to be read from the field with the postfix depending on the language
			} else {
				if (isset($this->row) and array_key_exists($this->ct->Table->fieldPrefix . $fieldname, $this->row)) {
					$value = $this->row[$this->ct->Table->fieldPrefix . $fieldname];
				} else {
					Fields::addLanguageField($this->ct->Table->realtablename, $this->ct->Table->fieldPrefix . $this->field->fieldname,
						$this->ct->Table->fieldPrefix . $fieldname);

					throw new Exception('Field "' . $this->ct->Table->fieldPrefix . $fieldname . '" not yet created. Go to /Custom Tables/Database schema/Checks to create that field.');
				}
			}

			if ($value === null) {
				$value = common::inputGetString($this->ct->Table->fieldPrefix . $this->field->fieldname, '');
				if ($value == '')
					$value = $defaultValue;
			}

			$result .= ($this->field->isrequired == 1 ? ' ' . $RequiredLabel : '');

			$attributes = $this->attributes;
			$attributes['id'] = $this->attributes['id'] . $postfix;
			$attributes['name'] = $this->attributes['name'] . $postfix;

			$result .= '<div id="' . $fieldname . '_div" class="multilangtext">';

			if (defined('_JEXEC')) {
				if ($this->field->params !== null and count($this->field->params) > 0 and $this->field->params[0] == 'rich') {
					$result .= '<span class="language_label_rich">' . $lang->caption . '</span>';

					$w = 500;
					$h = 200;
					$c = 0;
					$l = 0;

					$editor_name = Factory::getApplication()->get('editor');
					$editor = Editor::getInstance($editor_name);

					$input = '<div>' . $editor->display($attributes['name'], $value, $w, $h, $c, $l) . '</div>';
				} else {
					$input = '<textarea ' . self::attributes2String($attributes) . '>' . htmlspecialchars($value ?? '') . '</textarea>'
						. '<span class="language_label">' . $lang->caption . '</span>';
				}
			} elseif (defined('WPINC')) {
				// WordPress Handling
				$editorType = $this->field->params[0] ?? '';
				if (isset($this->option_list[4]))
					$editorType = $this->option_list[4];

				if ($editorType == 'rich') {
					$result .= '<span class="language_label_rich">' . $lang->caption . '</span>';
					ob_start();
					$editor_settings = [
						'textarea_name' => $attributes['id'],
						'media_buttons' => true,
						'textarea_rows' => 10,
						'tinymce' => true,
					];
					wp_editor($value, $attributes['id'], $editor_settings);
					$input = ob_get_clean();
				} else {
					$input = '<textarea ' . self::attributes2String($attributes) . '>' . htmlspecialchars($value ?? '') . '</textarea>'
						. '<span class="language_label">' . $lang->caption . '</span>';
				}

			} else {
				throw new Exception('Multilingual textarea not supported in the current version of the Custom Tables');
			}

			$result .= '<div id="' . $fieldname . '_div" class="multilangtext">' . $input . '</div>';
			$result .= '</div>';
		}
		return $result;
	}
}