<?php
/**
 * New User Administration Screen.
 *
 * @package WordPress
 * @subpackage Administration
 */

//include ('customtables-layouts-edit-help.php');

require_once ABSPATH . 'wp-admin/admin-header.php';
?>
    <div class="wrap">
        <h1 id="add-new-user">
            <?php
            if ($this->admin_layout_edit->layoutId == 0)
                _e('Add New Custom Layout');
            else
                _e('Edit Custom Layout');
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
            <p><?php

                if ($this->admin_layout_edit->layoutId === null)
                    _e('Create a brand new custom layout.');
                else
                    _e('Edit custom layout.');
                ?>
            </p>
            <form method="post" name="createlayout" id="createlayout" class="validate" novalidate="novalidate">
                <input name="action" type="hidden" value="createlayout"/>
                <?php wp_nonce_field('create-layout', '_wpnonce_create-layout'); ?>

                <table class="form-table" role="presentation">
                    <!-- Layout Name Field -->
                    <tr class="form-field form-required">
                        <th scope="row">
                            <label for="layoutname">
                                <?php echo __('Layout Name', $this->plugin_text_domain); ?>
                                <span class="description">(<?php echo __('required', $this->plugin_text_domain); ?>)</span>
                            </label>
                        </th>
                        <td>
                            <input name="layoutname" type="text" id="layoutname"
                                   value="<?php echo esc_attr($this->admin_layout_edit->layoutRow['layoutname'] ?? ''); ?>" aria-required="true"
                                   autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="60"/>
                        </td>
                    </tr>
                </table>

                <!-- Submit Button -->
                <?php
                $buttonText = ($this->admin_layout_edit->layoutId == 0) ? __('Add New Layout') : __('Save Layout');
                submit_button($buttonText, 'primary', 'createlayout', true, array('id' => 'createlayoutsub'));
                ?>
            </form>
        <?php } // End if (current_user_can('install_plugins')) ?>
    </div>
<?php
require_once ABSPATH . 'wp-admin/admin-footer.php';
