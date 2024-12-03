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

require_once ABSPATH . 'wp-admin/admin-header.php';

if ($this->admin_table_edit->tableId === null)
    $new_tablename = '';
else
    $new_tablename = $this->admin_table_edit->ct->Table->tablename;

if ($this->admin_table_edit->ct->Env->advancedTagProcessor) {
    if ($this->admin_table_edit->tableId === null)
        $customphp = '';
    else
        $customphp = $this->admin_table_edit->ct->Table->tablerow['customphp'];

    $customidfield = $this->admin_table_edit->ct->Table->tablerow['customidfield'] ?? '';
    $customidfieldtype = $this->admin_table_edit->ct->Table->tablerow['customidfieldtype'] ?? '';
    $customfieldprefix = $this->admin_table_edit->ct->Table->tablerow['customfieldprefix'] ?? '';
}

?>
    <div class="wrap">
        <h1>
            <?php
            if ($this->admin_table_edit->tableId == 0)
                esc_html_e('Add New Custom Table');
            else
                esc_html_e('Edit Custom Table');
            ?>
        </h1>

        <?php if (isset($this->admin_table_edit->errors) && is_wp_error($this->admin_table_edit->errors)) : ?>
            <div class="error">
                <ul>
                    <?php
                    foreach ($this->admin_table_edit->errors->get_error_messages() as $err) {
                        echo '<li>' . esc_html($err) . '</li>';
                    }
                    ?>
                </ul>
            </div>
        <?php
        endif;

        if (!empty($messages)) {
            foreach ($messages as $msg) {
                echo '<div id="message" class="updated notice is-dismissible"><p>' . esc_html($msg) . '</p></div>';
            }
        }
        ?>

        <?php if (isset($add_user_errors) && is_wp_error($add_user_errors)) : ?>
            <div class="error">
                <?php
                foreach ($add_user_errors->get_error_messages() as $message) {
                    echo '<p>' . esc_html($message) . '</p>';
                }
                ?>
            </div>
        <?php endif; ?>
        <div id="ajax-response"></div>

        <?php
        if (current_user_can('install_plugins')) {
            ?>

            <form method="post" name="createtable" id="createtable" class="validate" novalidate="novalidate">
                <input name="action" type="hidden" value="createtable"/>
                <?php wp_nonce_field('create-edit-table'); ?>

                <h2 class="nav-tab-wrapper wp-clearfix">
                    <button type="button" data-toggle="tab" data-tabs=".gtabs.tableEditTabs" data-tab=".tableName-tab"
                            class="nav-tab nav-tab-active">Table Name
                    </button>
                    <?php if ($this->admin_table_edit->ct->Env->advancedTagProcessor): ?>
                        <button type="button" data-toggle="tab" data-tabs=".gtabs.tableEditTabs"
                                data-tab=".advanced-tab"
                                class="nav-tab">Advanced
                        </button>
                    <?php endif; ?>
                </h2>

                <div class="gtabs tableEditTabs">

                    <div class="gtab active tableName-tab" style="margin-left:-20px;">

                        <table class="form-table" role="presentation">
                            <!-- Table Name Field -->
                            <tr class="form-field form-required">
                                <th scope="row">
                                    <label for="tablename">
                                        <?php echo esc_html__('Table Name', 'customtables'); ?>
                                        <span class="description">(<?php echo esc_html__('required', 'customtables'); ?>)</span>
                                    </label>
                                </th>
                                <td>
                                    <input name="tablename" type="text" id="tablename"
                                           value="<?php echo esc_attr($new_tablename); ?>" aria-required="true"
                                           autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="60"/>
                                </td>
                            </tr>

                            <!-- Table Title Fields -->
                            <?php
                            $moreThanOneLang = false;
                            foreach ($this->admin_table_edit->ct->Languages->LanguageList as $lang): ?>
                                <?php
                                $id = ($moreThanOneLang ? 'tabletitle_' . $lang->sef : 'tabletitle');
                                $cssclass = ($moreThanOneLang ? 'form-control valid form-control-success' : 'form-control required valid form-control-success');
                                $att = ($moreThanOneLang ? '' : ' required ');

                                $vlu = $item_array[$id] ?? ($this->admin_table_edit->ct->Table !== null ? $this->admin_table_edit->ct->Table->tablerow[$id] : '');
                                ?>

                                <tr class="form-field<?php echo esc_html(!$moreThanOneLang ? ' form-required' : ''); ?>">
                                    <th scope="row">
                                        <label for="<?php echo esc_html($id); ?>">
                                            <?php echo esc_html__('Table Title', 'customtables'); ?>
                                            <?php if (!$moreThanOneLang): ?>
                                                <span class="description">(<?php echo esc_html__('required', 'customtables'); ?>)</span>
                                            <?php endif; ?>
                                            <br/>
                                            <b><?php echo esc_html($lang->title); ?></b>
                                        </label>
                                    </th>
                                    <td>
                                        <input name="<?php echo esc_html($id); ?>" type="text"
                                               id="<?php echo esc_html($id); ?>"
                                               value="<?php echo esc_html($vlu); ?>" maxlength="255"/>
                                    </td>
                                </tr>
                                <?php $moreThanOneLang = true; ?>
                            <?php endforeach; ?>
                        </table>
                    </div>

                    <?php if ($this->admin_table_edit->ct->Env->advancedTagProcessor): ?>
                        <div class="gtab advanced-tab" style="margin-left:-20px;">
                            <table class="form-table" role="presentation">
                                <tr class="form-field form">
                                    <th scope="row">
                                        <label for="customphp">
                                            <?php echo esc_html__('Custom PHP', 'customtables'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <input name="customphp" type="text" id="customphp"
                                               value="<?php echo esc_attr($customphp); ?>" aria-required="false"
                                               autocapitalize="none" autocorrect="off" autocomplete="off"
                                               maxlength="255"/>
                                    </td>
                                </tr>

                                <tr class="form-field form">
                                    <th scope="row">
                                        <label for="customidfield">
                                            <?php echo esc_html__('Primary Key Field', 'customtables'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <input name="customidfield" type="text" id="customidfield"
                                               value="<?php echo esc_attr($customidfield); ?>" aria-required="false"
                                               autocapitalize="none" autocorrect="off" autocomplete="off"
                                               maxlength="255"/>
                                    </td>
                                </tr>

                                <tr class="form-field form">
                                    <th scope="row">
                                        <label for="customidfieldtype">
                                            <?php echo esc_html__('Primary Key Field Type', 'customtables'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <input name="customidfieldtype" type="text" id="customidfieldtype"
                                               value="<?php echo esc_attr($customidfieldtype); ?>" aria-required="false"
                                               autocapitalize="none" autocorrect="off" autocomplete="off"
                                               maxlength="255"/>
                                    </td>
                                </tr>

                                <tr class="form-field form">
                                    <th scope="row">
                                        <label for="customfieldprefix">
                                            <?php echo esc_html__('Field Name Prefix', 'customtables'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <input name="customfieldprefix" type="text" id="customfieldprefix"
                                               value="<?php echo esc_attr($customfieldprefix); ?>" aria-required="false"
                                               autocapitalize="none" autocorrect="off" autocomplete="off"
                                               maxlength="255"/>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="display:inline-block;">
                    <?php
                    $buttonText = ($this->admin_table_edit->tableId == 0) ? esc_html__('Save New Table', 'customtables') : esc_html__('Save Table', 'customtables');
                    submit_button($buttonText, 'primary', 'createtable', true, array('id' => 'createtable-submit'));
                    ?></div>

                <div style="display:inline-block;margin-left:20px;">
                    <!-- Cancel Button -->
                    <?php
                    submit_button(esc_html__('Cancel', 'customtables'), 'secondary', 'createtable-cancel', true,
                        array('id' => 'createtable-cancel', 'onclick' => 'window.location.href="admin.php?page=customtables-tables";return false;'));
                    ?></div>

            </form>
        <?php } // End if (current_user_can('install_plugins')) ?>


    </div>


<?php if (!empty($new_tablename)): ?>
    <h4>Adding Catalog Views and Edit Forms</h4>
    <p>You can use these shortcodes to display table records and add/edit forms:</p>
    <br/>
    <p style="font-weight:bold;">Basic Catalog Views</p>
    <pre>[customtables table="<?php echo $new_tablename; ?>"] - Displays catalog view using table name</pre>
    <pre>[customtables table="<?php echo $this->admin_table_edit->tableId; ?>"] - Displays catalog view using table ID</pre>
    <pre>[customtables table="<?php echo $new_tablename; ?>" view="catalog"] - Explicit catalog view</pre>
    <p style="font-weight:bold;">Edit Forms</p>
    <pre>[customtables table="<?php echo $new_tablename; ?>" view="edit"] - Adds a form to create a new record</pre>
    <p style="font-weight:bold;">Catalog with Parameters</p>
    <pre>[customtables table="<?php echo $new_tablename; ?>" view="catalog" limit="5"] - Shows only 5 records</pre>
    <p>Note: The limit parameter controls the number of displayed records. Use limit="0" or omit the parameter to show
        all records.</p>
<?php endif; ?>

    <p><a href="https://ct4.us/contact-us/"
          target="_blank"><?php echo esc_html__('Questions or issues? Contact support.', 'customtables'); ?></a></p>

<?php
require_once ABSPATH . 'wp-admin/admin-footer.php';
