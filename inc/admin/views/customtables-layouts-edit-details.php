<?php
/**
 * Plugin Name:       CustomTables
 * Plugin URI:        https://ct4.us/
 * GitHub:            https://github.com/joomlaboat/customtables-wp
 * Author:            Ivan Komlev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

?>

<table class="form-table" role="presentation">

    <tr class="form-field form-required">
        <!-- Layout Name Field -->
        <th scope="row">
            <label for="layoutname">
				<?php use CustomTables\Forms;
				use CustomTables\Layouts;
				use CustomTables\MySQLWhereClause;

				echo esc_html(__('Layout Name', 'customtables')); ?>
                <span class="description">(<?php echo esc_html(__('required', 'customtables')); ?>)</span>
            </label>
        </th>
        <td>
            <input name="layoutname" type="text" id="layoutname" style="min-width: 150px;"
                   value="<?php echo esc_attr($this->admin_layout_edit->layoutRow['layoutname'] ?? ''); ?>"
                   aria-required="true"
                   autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="60"/>
        </td>

        <!-- Layout Type -->
        <th scope="row" style="text-align: right;">
            <label for="layouttype">
				<?php echo esc_html(__('Layout Type', 'customtables')); ?>
                <span class="description">(<?php echo esc_html(__('required', 'customtables')); ?>)</span>
            </label>
        </th>
        <th>

			<?php

			$selectedType = $this->admin_layout_edit->layoutRow['layouttype'] ?? '';
			$Layouts = new Layouts($this->admin_layout_edit->ct);
			$types = $Layouts->layoutTypeTranslation();

			$allowed_html = array(
				'option' => array(
					'value' => array(),
					'selected' => array())
			);

			$options_escaped = '';

			foreach ($types as $key => $type) {
				$options_escaped .= '<option value="' . esc_html($key) . '"';
				if ($key == $selectedType) {
					$options_escaped .= ' selected';
				}
				$options_escaped .= '>' . esc_html($type) . '</option>';
			}
			?>

            <select name="layouttype" id="layouttype">
				<?php echo wp_kses($options_escaped, $allowed_html); ?>
            </select>

			<?php
			/*
				* Custom Tables_LAYOUTS_SIMPLE_CATALOG = "Simple Catalog"
				* Custom Tables_LAYOUTS_CATALOG_PAGE = "Catalog Page"
				* Custom Tables_LAYOUTS_CATALOG_ITEM = "Catalog Item"
				* Custom Tables_LAYOUTS_EDIT_FORM = "Edit form"
				* Custom Tables_LAYOUTS_DETAILS = "Details"
				* Custom Tables_LAYOUTS_EMAIL_MESSAGE = "Email Message"
				* Custom Tables_LAYOUTS_XML = "XML File"
				* Custom Tables_LAYOUTS_CSV = "CSV File"
				* Custom Tables_LAYOUTS_JSON = "JSON File"
			 */
			?>
        </th>

        <!-- Table -->
        <th scope="row" style="text-align: right;">
            <label for="layoutname">
				<?php echo esc_html(__('Table', 'customtables')); ?>
                <span class="description">(<?php echo esc_html(__('required', 'customtables')); ?>)</span>
            </label>
        </th>
        <td>
			<?php

			$whereClause = new MySQLWhereClause();
			$whereClause->addCondition('published', 1);

			$list_of_tables_safe = Forms::renderHTMLSelectBoxFromDB('table', $this->admin_layout_edit->layoutRow['tableid'] ?? 0, true, '#__customtables_tables',
				['id', 'tablename'], $whereClause, 'tablename', ['onchange="loadFieldsUpdate(\'WordPress\');"']);

			$allowed_html = array(
				'select' => array(
					'id' => array(),
					'name' => array()),
				'option' => array(
					'value' => array(),
					'selected' => array())
			);

			echo wp_kses($list_of_tables_safe,$allowed_html); ?>
        </td>
    </tr>
</table>
<hr/>