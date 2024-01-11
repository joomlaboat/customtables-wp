<?php
/**
 * Plugin Name:       CustomTables
 * Plugin URI:        https://ct4.us/
 * GitHub:            https://github.com/joomlaboat/customtables-wp
 * Author:            Ivan Komlev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>

<table class="form-table" role="presentation">

    <tr class="form-field form-required">
        <!-- Layout Name Field -->
        <th scope="row">
            <label for="layoutname">
                <?php use CustomTables\Forms;
                use CustomTables\Layouts;
                use CustomTables\MySQLWhereClause;

                echo __('Layout Name', 'customtables'); ?>
                <span class="description">(<?php echo __('required', 'customtables'); ?>)</span>
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
                <?php echo __('Layout Type', 'customtables'); ?>
                <span class="description">(<?php echo __('required', 'customtables'); ?>)</span>
            </label>
        </th>
        <td>

            <?php

            $selectedType = $this->admin_layout_edit->layoutRow['layouttype'] ?? '';
            $Layouts = new Layouts($this->admin_layout_edit->ct);
            $types = $Layouts->layoutTypeTranslation();

            $options = '';

            foreach ($types as $key => $type) {
                $options .= '<option value="' . $key . '"';
                if ($key == $selectedType) {
                    $options .= ' selected';
                }
                $options .= '>' . $type . '</option>';
            }
            ?>

            <select name="layouttype" id="layouttype">
                <?php echo $options; ?>
            </select>

            <?php
            /*
                * COM_CUSTOMTABLES_LAYOUTS_SIMPLE_CATALOG = "Simple Catalog"
                * COM_CUSTOMTABLES_LAYOUTS_CATALOG_PAGE = "Catalog Page"
                * COM_CUSTOMTABLES_LAYOUTS_CATALOG_ITEM = "Catalog Item"
                * COM_CUSTOMTABLES_LAYOUTS_EDIT_FORM = "Edit form"
                * COM_CUSTOMTABLES_LAYOUTS_DETAILS = "Details"
                * COM_CUSTOMTABLES_LAYOUTS_EMAIL_MESSAGE = "Email Message"
                * COM_CUSTOMTABLES_LAYOUTS_XML = "XML File"
                * COM_CUSTOMTABLES_LAYOUTS_CSV = "CSV File"
                * COM_CUSTOMTABLES_LAYOUTS_JSON = "JSON File"
             */
            ?>
        </td>

        <!-- Layout Type -->
        <th scope="row" style="text-align: right;">
            <label for="layoutname">
                <?php echo __('Table', 'customtables'); ?>
                <span class="description">(<?php echo __('required', 'customtables'); ?>)</span>
            </label>
        </th>
        <td>

            <?php

            $whereClause = new MySQLWhereClause();
            $whereClause->addCondition('published', 1);

            echo Forms::renderHTMLSelectBoxFromDB('table',$this->admin_layout_edit->layoutRow['tableid'] ?? 0, true,'#__customtables_tables',
                ['id', 'tablename'], $whereClause, 'tablename',['onchange="loadFieldsUpdate(\'WordPress\');"']) ?>
        </td>
    </tr>
</table>


<hr/>