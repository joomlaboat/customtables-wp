<?php

use CustomTables\Edit;

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
                echo '<div class="error"><p>' . esc_html__('Table not selected or not found.', 'customtables') . '</p></div>';
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

            if (isset($this->admin_record_edit->ct->Table) and $this->admin_record_edit->ct->Table->tablename !== null):
                ?>
                <p><?php

                    if ($this->admin_record_edit->listing_id === null)
                        esc_html_e('Create a brand new record.');
                    else
                        esc_html_e('Edit record.');
                    ?>
                </p>
                <form method="post" name="createrecord" id="createrecord" class="validate" novalidate="novalidate" enctype="multipart/form-data">
                    <input name="action" type="hidden" value="createrecord"/>
                    <input name="table" type="hidden" value="<?php echo esc_html($this->admin_record_edit->tableId); ?>"/>
                    <?php echo wp_nonce_field('create-edit-record' ); ?>


                    <?php
                    $buttonText = (empty($this->admin_record_edit->listing_id)) ? __('Save Record') : __('Save Record');

                    $editForm = new Edit($this->admin_record_edit->ct);
                    $editForm->layoutContent = $this->admin_record_edit->pageLayout;
                    $editForm_render_safe = $editForm->render($this->admin_record_edit->recordRow, $this->admin_record_edit->formLink, 'adminForm',false);
                    echo $editForm_render_safe;//Rendered by the CT library
                    ?>
                    
                    <div style="display:inline-block;">
                    <?php submit_button($buttonText, 'primary', 'createrecord', true, array('id' => 'createrecord-submit')); ?>
                    </div>
                    <div style="display:inline-block;margin-left:20px;">
                        <!-- Cancel Button -->
                        <?php
                        submit_button(esc_html__('Cancel', 'customtables'), 'secondary', 'createrecord-cancel', true,
                            array('id' => 'createrecord-cancel', 'onclick' => 'window.location.href="admin.php?page=customtables-records&table=' . esc_html($this->admin_record_edit->tableId) . '";return false;'));
                        ?></div>
                </form>
            <?php endif; ?>
        <?php } // End if (current_user_can('install_plugins')) ?>
    </div>


<?php if(!empty($this->admin_record_edit->ct->Table)): ?>
    <h4>Adding Record Edit Forms</h4>
    <p>You can use these shortcodes to insert forms for adding or editing records:</p>
    <br/>
    <pre>[customtables table="<?php echo $this->admin_record_edit->ct->Table->tablename; ?>" view="edit"] - Adds a form to create a new record</pre>

    <?php if(!empty($this->admin_record_edit->listing_id)): ?>
    <pre>[customtables table="<?php echo $this->admin_record_edit->ct->Table->tablename; ?>" view="edit" id="<?php echo $this->admin_record_edit->listing_id; ?>"] - Adds a form to edit record #<?php echo $this->admin_record_edit->listing_id; ?></pre>
    <pre>[customtables table="<?php echo $this->admin_record_edit->ct->Table->tablename; ?>" view="details" id="<?php echo $this->admin_record_edit->listing_id; ?>"] - Adds a details form of the record #<?php echo $this->admin_record_edit->listing_id; ?></pre>
    <p>Note: Replace "<?php echo $this->admin_record_edit->listing_id; ?>" with the ID of the record you want to edit.</p>
    <?php endif; ?>

<?php endif; ?>

    <p><a href="https://ct4.us/contact-us/" target="_blank"><?php echo esc_html__('Support needed? Contact us.', 'customtables'); ?></a></p>
<?php
require_once ABSPATH . 'wp-admin/admin-footer.php';
