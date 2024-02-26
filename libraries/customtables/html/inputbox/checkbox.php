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

class InputBox_checkbox extends BaseInputBox
{
	function __construct(CT &$ct, Field $field, ?array $row, array $option_list = [], array $attributes = [])
	{
		parent::__construct($ct, $field, $row, $option_list, $attributes);
	}

	function render(?string $value, ?string $defaultValue): string
	{
		if ($value === null) {
			$value = common::inputGetInt($this->ct->Env->field_prefix . $this->field->fieldname, 0);
			if ($value == 0)
				$value = (int)$defaultValue;
		} else {
			$value = (int)$value;
		}

		$format = '';

		if (isset($this->option_list[2]) and $this->option_list[2] == 'yesno')
			$format = "yesno";

		$element_id = $this->attributes['id'];

		if ($format == "yesno") {

			$this->attributes['type'] = 'radio';
			$attributes = [];
			$attributes['id'] = $element_id . '0';
			$attributes['name'] = $element_id;
			$attributes['type'] = 'radio';
			$attributes['value'] = '0';

			$attributes['class'] = $this->attributes['class'];
			self::addCSSClass($attributes, 'active');

			if ($value == 0)
				$attributes['checked'] = 'checked';

			$input1 = '<input ' . self::attributes2String($attributes) . ' />'
				. '<label for="' . $attributes['id'] . '0">' . __("No", "customtables") . '</label>';

			$attributes = [];
			$attributes['id'] = $element_id . '1';
			$attributes['name'] = $element_id;
			$attributes['type'] = 'radio';
			$attributes['value'] = '1';
			$attributes['class'] = null;

			if ($value == 1)
				$attributes['checked'] = 'checked';

			$input2 = '<input ' . self::attributes2String($attributes) . ' />'
				. '<label for="' . $attributes['id'] . '">' . __("Yes", "customtables") . '</label>';

			$span = '<span class="toggle-outside"><span class="toggle-inside"></span></span>';

			$hidden = '<input type="hidden"'
				. ' id="' . $element_id . '_off"'
				. ' name="' . $element_id . '_off"'
				. ' class="' . $this->attributes['class'] . '"'
				. ' data-selector="switcher"'
				. ' data-label="' . $this->attributes['data-label'] . '"'
				. ' data-valuerulecaption="' . $this->attributes['data-valuerulecaption'] . '"'
				. ($value == 1 ? ' value="0"' : ' value="1"')
				. ' />';

			self::addCSSClass($this->attributes, 'switcher');
			$cssClass = $this->attributes['class'];

			return '<div class="' . $cssClass . '">' . $input1 . $input2 . $span . $hidden . '</div><script>
							document.getElementById("' . $element_id . '0").onchange = function(){if(this.checked === true)' . $element_id . '_off.value=1;' . $this->attributes['onchange'] . '};
							document.getElementById("' . $element_id . '1").onchange = function(){if(this.checked === true)' . $element_id . '_off.value=0;' . $this->attributes['onchange'] . '};
						</script>';
		} else {

			$onchange = $element_id . '_off.value=(this.checked === true ? 0 : 1);';// this is to save unchecked value as well.
			$this->attributes['onchange'] = (($this->attributes['onchange'] ?? '') == '' ? '' : $this->attributes['onchange'] . ' ') . $onchange;

			if ($value == 1)
				$this->attributes['checked'] = 'checked';

			$this->attributes['type'] = 'checkbox';

			$input = '<input ' . self::attributes2String($this->attributes) . ' />';

			$hidden = '<input type="hidden"'
				. ' id="' . $element_id . '_off" '
				. ' name="' . $element_id . '_off" '
				. ($value == 1 ? ' value="0" ' : 'value="1"')
				. ' >';

			return $input . $hidden;
		}
	}
}
