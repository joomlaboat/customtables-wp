<?php
/**
 * New User Administration Screen.
 *
 * @package WordPress
 * @subpackage Administration
 */

//include ('customtables-records-edit-help.php');

use CustomTables\Layouts;

require_once ABSPATH . 'wp-admin/admin-header.php';

?>
    <div class="wrap">
        <h1 id="add-new-user">
            <?php
            if (isset($this->admin_record_edit->ct->Table) and $this->admin_record_edit->ct->Table->tablename !== null) {
                _e('Custom Tables - Table', $this->plugin_text_domain);
                echo ' "' . $this->admin_record_edit->ct->Table->tabletitle . '" - ';
                if ($this->admin_record_edit->listing_id === null)
                    _e('Add New Record');
                else
                    _e('Edit Record');
            } else {
                _e('Custom Tables - Records', $this->plugin_text_domain);
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
            if (isset($this->admin_record_edit->ct->Table) and $this->admin_record_edit->ct->Table->tablename !== null):
                ?>
                <p><?php

                    if ($this->admin_record_edit->listing_id === null)
                        _e('Create a brand new record.');
                    else
                        _e('Edit record.');
                    ?>
                </p>

                    <?php

                    echo '
                    <form method="post" name="createrecord" id="createrecord" class="validate" novalidate="novalidate">
                    <input name="action" type="hidden" value="createrecord"/>
                    <input name="table" type="hidden" value="'.$this->admin_record_edit->tableId.'"/>';
                    echo wp_nonce_field('create-edit-record' );

                    $buttonText = ($this->admin_record_edit->listing_id == 0) ? __('Add New Record') : __('Save Record');

                    $editForm = new Edit($this->admin_record_edit->ct);
                    $editForm->layoutContent = $this->admin_record_edit->pageLayout;

                    echo $editForm->render($this->admin_record_edit->recordRow, $this->admin_record_edit->formLink, 'adminForm',false);


                    ?>







                    <?php submit_button($buttonText, 'primary', 'createrecord', true, array('id' => 'createrecordsub')); ?>
                </form>
            <?php endif; ?>
        <?php } // End if (current_user_can('install_plugins')) ?>
    </div>
<?php
require_once ABSPATH . 'wp-admin/admin-footer.php';
