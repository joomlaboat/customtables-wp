<table class="form-table" role="presentation">

    <tr class="form-field form-required">
        <!-- Layout Name Field -->
        <th scope="row">
            <label for="layoutname">
                <?php echo __('Layout Name', $this->plugin_text_domain); ?>
                <span class="description">(<?php echo __('required', $this->plugin_text_domain); ?>)</span>
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
                <?php echo __('Layout Type', $this->plugin_text_domain); ?>
                <span class="description">(<?php echo __('required', $this->plugin_text_domain); ?>)</span>
            </label>
        </th>
        <td>
            <select name="layouttype" id="layouttype">
                <option value="1">Simple Catalog</option>
                <option value="5">Catalog Page</option>
                <option value="6">Catalog Item</option>
                <option value="2">Edit form</option>
                <option value="4">Details</option>
                <!--<option value="3">COM_CUSTOMTABLES_LAYOUTS_RECORD_LINK</option>-->
                <option value="7">Email Message</option>
                <option value="8">XML File</option>
                <option value="9">CSV File</option>
                <option value="10">JSON File</option>
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
                <?php echo __('Table', $this->plugin_text_domain); ?>
                <span class="description">(<?php echo __('required', $this->plugin_text_domain); ?>)</span>
            </label>
        </th>
        <td>
            <?php echo \CustomTables\Forms::renderHTMLSelectBoxFromDB('table','#__customtables_tables',
                ['id','tablename'],['published=1'],'tablename') ?>
        </td>
    </tr>
</table>





<hr/>