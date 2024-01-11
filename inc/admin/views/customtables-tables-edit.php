<?php
/**
 * New User Administration Screen.
 *
 * @package WordPress
 * @subpackage Administration
 */

//include ('customtables-tables-edit-help.php');

require_once ABSPATH . 'wp-admin/admin-header.php';

if ($this->admin_table_edit->tableId === null)
    $new_tablename = '';
else
    $new_tablename = $this->admin_table_edit->ct->Table->tablename;
?>
    <div class="wrap">
        <h1 id="add-new-user">
            <?php
            if ($this->admin_table_edit->tableId == 0)
                _e('Add New Custom Table');
            else
                _e('Edit Custom Table');
            ?>
        </h1>

        <?php if (isset($this->admin_table_edit->errors) && is_wp_error($this->admin_table_edit->errors)) : ?>
            <div class="error">
                <ul>
                    <?php
                    foreach ($this->admin_table_edit->errors->get_error_messages() as $err) {
                        echo "<li>$err</li>\n";
                    }
                    ?>
                </ul>
            </div>
        <?php
        endif;

        if (!empty($messages)) {
            foreach ($messages as $msg) {
                echo '<div id="message" class="updated notice is-dismissible"><p>' . $msg . '</p></div>';
            }
        }
        ?>

        <?php if (isset($add_user_errors) && is_wp_error($add_user_errors)) : ?>
            <div class="error">
                <?php
                foreach ($add_user_errors->get_error_messages() as $message) {
                    echo "<p>$message</p>";
                }
                ?>
            </div>
        <?php endif; ?>
        <div id="ajax-response"></div>

        <?php
        if (current_user_can('install_plugins')) {
            ?>
            <p><?php

                if ($this->admin_table_edit->tableId === null)
                    _e('Create a brand new custom table.');
                else
                    _e('Edit custom table.');
                ?>
            </p>
            <form method="post" name="createtable" id="createtable" class="validate" novalidate="novalidate">
                <input name="action" type="hidden" value="createtable"/>
                <?php wp_nonce_field('create-edit-table' ); ?>

                <table class="form-table" role="presentation">
                    <!-- Table Name Field -->
                    <tr class="form-field form-required">
                        <th scope="row">
                            <label for="tablename">
                                <?php echo __('Table Name', 'customtables'); ?>
                                <span class="description">(<?php echo __('required', 'customtables'); ?>)</span>
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

                        $vlu = isset($item_array[$id]) ? $item_array[$id] : ($this->admin_table_edit->ct->Table !== null ? $this->admin_table_edit->ct->Table->tablerow[$id] : '');
                        ?>

                        <tr class="form-field<?php echo(!$moreThanOneLang ? ' form-required' : ''); ?>">
                            <th scope="row">
                                <label for="<?php echo $id; ?>">
                                    <?php echo __('Table Title', 'customtables'); ?>
                                    <?php if (!$moreThanOneLang): ?>
                                        <span class="description">(<?php echo __('required', 'customtables'); ?>)</span>
                                    <?php endif; ?>
                                    <br/>
                                    <b><?php echo $lang->title; ?></b>
                                </label>
                            </th>
                            <td>
                                <input name="<?php echo $id; ?>" type="text" id="<?php echo $id; ?>"
                                       value="<?php echo $vlu; ?>" maxlength="255"/>
                            </td>
                        </tr>

                        <?php $moreThanOneLang = true; ?>
                    <?php endforeach; ?>
                </table>

                <!-- Submit Button -->
                <?php
                $buttonText = ($this->admin_table_edit->tableId == 0) ? __('Add New Table') : __('Save Table');
                submit_button($buttonText, 'primary', 'createtable', true, array('id' => 'createtablesub'));
                ?>
            </form>
        <?php } // End if (current_user_can('install_plugins')) ?>
    </div>
<?php
require_once ABSPATH . 'wp-admin/admin-footer.php';
