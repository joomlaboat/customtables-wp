<?php
/**
 * New User Administration Screen.
 *
 * @package WordPress
 * @subpackage Administration
 */

//include ('customtables-fields-edit-help.php');

require_once ABSPATH . 'wp-admin/admin-header.php';

?>
    <div class="wrap">
        <h1 id="add-new-user">
            <?php
            if (isset($this->admin_field_edit->ct->Table) and $this->admin_field_edit->ct->Table->tablename !== null) {
                _e('Custom Tables - Table', $this->plugin_text_domain);
                echo ' "' . $this->admin_field_edit->ct->Table->tabletitle . '" - ';
                if ($this->admin_field_edit->fieldId == 0)
                    _e('Add New Field');
                else
                    _e('Edit Field');
            } else {
                _e('Custom Tables - Fields', $this->plugin_text_domain);
                echo '<div class="error"><p>' . __('Table not selected or not found.', $this->plugin_text_domain) . '</p></div>';
            }
            ?>
        </h1>

        <?php if (isset($errors) && is_wp_error($errors)) : ?>
            <div class="error">
                <ul>
                    <?php
                    foreach ($errors->get_error_messages() as $err) {
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


            <?php
            if (isset($this->admin_field_edit->ct->Table) and $this->admin_field_edit->ct->Table->tablename !== null):
                ?>
                <p><?php

                    if ($this->admin_field_edit->fieldId === null)
                        _e('Create a brand new field.');
                    else
                        _e('Edit field.');
                    ?>
                </p>
                <form method="post" name="createfield" id="createfield" class="validate" novalidate="novalidate">
                    <input name="action" type="hidden" value="createfield"/>
                    <input name="table" type="hidden" value="<?php echo $this->admin_field_edit->tableId; ?>"/>
                    <?php wp_nonce_field('create-field', '_wpnonce_create-field'); ?>

                    <table class="form-table" role="presentation">
                        <!-- Field Name Field -->
                        <tr class="form-field form-required">
                            <th scope="row">
                                <label for="fieldname">
                                    <?php echo __('Field Name', $this->plugin_text_domain); ?>
                                    <span class="description">(<?php echo __('required', $this->plugin_text_domain); ?>)</span>
                                </label>
                            </th>
                            <td>
                                <input name="fieldname" type="text" id="fieldname"
                                       value="<?php echo esc_attr($this->admin_field_edit->fieldRow['fieldname']); ?>"
                                       aria-required="true"
                                       autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="60"/>
                            </td>
                        </tr>

                        <!-- Field Title Fields -->
                        <?php
                        $moreThanOneLang = false;
                        foreach ($this->admin_field_edit->ct->Languages->LanguageList as $lang): ?>
                            <?php
                            $id = ($moreThanOneLang ? 'fieldtitle_' . $lang->sef : 'fieldtitle');
                            $cssclass = ($moreThanOneLang ? 'form-control valid form-control-success' : 'form-control required valid form-control-success');
                            $att = ($moreThanOneLang ? '' : ' required ');
                            $vlu = $this->admin_field_edit->fieldRow[$id] ?? null;
                            ?>

                            <tr class="form-field<?php echo(!$moreThanOneLang ? ' form-required' : ''); ?>">
                                <th scope="row">
                                    <label for="<?php echo $id; ?>">
                                        <?php echo __('Field Title', $this->plugin_text_domain); ?>
                                        <?php if (!$moreThanOneLang): ?>
                                            <span class="description">(<?php echo __('required', $this->plugin_text_domain); ?>)</span>
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

                        <!-- Field Type Field -->
                        <tr class="form-field form-required">
                            <th scope="row">
                                <label for="type">
                                    <?php echo __('Field Type', $this->plugin_text_domain); ?>
                                    <span class="description">(<?php echo __('required', $this->plugin_text_domain); ?>)</span>
                                </label>
                            </th>
                            <td>
                                <?php
                                $selectBoxOptions = [];

                                foreach ($this->admin_field_edit->fieldTypes as $type)
                                    $selectBoxOptions[] = '<option value="' . $type['name'] . '">' . $type['label'] . '</option>';

                                echo '<select name="type" id="type">' . implode('', $selectBoxOptions) . '</select>';
                                ?>
                            </td>
                        </tr>
                    </table>

                    <!-- Submit Button -->
                    <?php
                    $buttonText = ($this->admin_field_edit->fieldId == 0) ? __('Add New Field') : __('Save Field');
                    submit_button($buttonText, 'primary', 'createfield', true, array('id' => 'createfieldsub'));
                    ?>
                </form>
            <?php endif; ?>
        <?php } // End if (current_user_can('install_plugins')) ?>
    </div>
<?php
require_once ABSPATH . 'wp-admin/admin-footer.php';
