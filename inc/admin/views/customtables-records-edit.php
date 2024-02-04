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

//include ('customtables-records-edit-help.php');

require_once ABSPATH . 'wp-admin/admin-header.php';

?>
    <div class="wrap">
        <h1 id="add-new-user">
            <?php
            if (isset($this->admin_record_edit->ct->Table) and $this->admin_record_edit->ct->Table->tablename !== null) {
                esc_html_e('Custom Tables - Table', 'customtables');
                echo ' "' . esc_html($this->admin_record_edit->ct->Table->tabletitle) . '" - ';
                if ($this->admin_record_edit->listing_id === null)
                    esc_html_e('Add New Record');
                else
                    esc_html_e('Edit Record');
            } else {
                esc_html_e('Custom Tables - Records', 'customtables');
                echo '<div class="error"><p>' . esc_html(__('Table not selected or not found.', 'customtables')) . '</p></div>';
            }
            ?>
        </h1>

        <?php if (isset($errors) && is_wp_error($errors)) : ?>
            <div class="error">
                <ul>
                    <?php
                    foreach ($errors->get_error_messages() as $err) {
                        echo "<li>" . esc_html($err) ."</li>\n";
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
                    echo "<p>".esc_html($message)."</p>";
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
                        esc_html_e('Create a brand new record.');
                    else
                        esc_html_e('Edit record.');
                    ?>
                </p>

                    <?php

                    echo '
                    <form method="post" name="createrecord" id="createrecord" class="validate" novalidate="novalidate">
                    <input name="action" type="hidden" value="createrecord"/>
                    <input name="table" type="hidden" value="'.esc_html($this->admin_record_edit->tableId).'"/>';
                    echo wp_nonce_field('create-edit-record' );

                    $buttonText = ($this->admin_record_edit->listing_id == 0) ? __('Add New Record') : __('Save Record');

                    $editForm = new Edit($this->admin_record_edit->ct);
                    $editForm->layoutContent = $this->admin_record_edit->pageLayout;
                    $editForm_render_safe = $editForm->render($this->admin_record_edit->recordRow, $this->admin_record_edit->formLink, 'adminForm',false);
                    echo wp_kses_post($editForm_render_safe);

                    submit_button($buttonText, 'primary', 'createrecord', true, array('id' => 'createrecordsub')); ?>
                </form>
            <?php endif; ?>
        <?php } // End if (current_user_can('install_plugins')) ?>
    </div>
<?php
require_once ABSPATH . 'wp-admin/admin-footer.php';
