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

<div class="wrap ct_doc">
    <h2><?php esc_html_e('Custom Tables - Settings', 'customtables'); ?></h2>

    <?php if (isset($errors) && is_wp_error($errors)) : ?>
        <div class="error">
            <ul>
                <?php
                foreach ($errors->get_error_messages() as $err) {
                    echo "<li>" . esc_html($err) . "</li>";
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
                echo "<p>" . esc_html($message) . "</p>";
            }
            ?>
        </div>
    <?php endif; ?>
    <div id="ajax-response"></div>

    <?php if (current_user_can('install_plugins')): ?>

            <form method="post" name="settings" id="settings" class="validate" novalidate="novalidate">
                <input name="action" type="hidden" value="save-settings"/>
                <?php wp_nonce_field('settings'); ?>

                <table class="form-table" role="presentation">
                    <!-- Google Map -->
                    <tr class="form-field form-required">
                        <th scope="row">
                            <label for="googlemapapikey">
                                <?php echo esc_html__('Google Map API Key', 'customtables'); ?>
                            </label>
                        </th>
                        <td>
                            <input name="googlemapapikey" type="text" id="googlemapapikey"
                                   value="<?php echo esc_html(get_option('customtables-googlemapapikey')); ?>"
                                   aria-required="false"
                                   autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="40"/>
                        </td>
                    </tr>
                    <!-- Google Drive -->
                    <tr class="form-field form-required">
                        <th scope="row">
                            <label for="googledriveapikey">
                                <?php echo esc_html__('Google Drive API Key', 'customtables'); ?>
                            </label>
                        </th>
                        <td>
                            <input name="googledriveapikey" type="text" id="googledriveapikey"
                                   value="<?php echo esc_html(get_option('customtables-googledriveapikey')); ?>"
                                   aria-required="false"
                                   autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="40"/>
                        </td>
                    </tr>
                    <tr class="form-field form-required">
                        <th scope="row">
                            <label for="googledriveclientid">
                                <?php echo esc_html__('Google Drive Client ID', 'customtables'); ?>
                            </label>
                        </th>
                        <td>
                            <input name="googledriveclientid" type="text" id="googledriveclientid"
                                   value="<?php echo esc_html(get_option('customtables-googledriveclientid')); ?>"
                                   aria-required="false"
                                   autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="100"/>
                        </td>
                    </tr>

                    <tr class="form-field form-required">
                        <th scope="row">
                            <label for="fieldprefix">
                                <?php echo esc_html__('Field Name Prefix', 'customtables'); ?>
                            </label>
                        </th>
                        <td>
                            <input name="fieldprefix" type="text" id="fieldprefix"
                                   value="<?php

                                   $vlu = get_option('customtables-fieldprefix');
                                   if(empty($vlu))
                                       $vlu = 'ct_';

                                   echo esc_html($vlu); ?>"
                                   aria-required="false"
                                   autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="100"/>
                            <br/>
                            Specifies the prefix added to all table field names (e.g., 'ct_FieldName'). This prefix helps prevent conflicts with MySQL reserved words and ensures database compatibility. Only modify this if you have a specific reason to use a different prefix scheme. Type NO-PREFIX to have field names without a prefix. Changing the prefix doesn't automatically renames fields. You will have to do it manually.
                        </td>
                    </tr>
                </table>

                <!-- Submit Button -->
                <?php
                $buttonText = esc_html__('Save Settings', 'customtables');
                submit_button($buttonText, 'primary', 'savesettings');
                ?>

            </form>

    <?php endif; ?>

    <p><a href="https://ct4.us/contact-us/" target="_blank"><?php echo esc_html__('Need help? Connect with us.', 'customtables'); ?></a></p>



</div>