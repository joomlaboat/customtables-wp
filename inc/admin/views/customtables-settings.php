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
                    <!-- Field Name Field -->
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
                </table>

                <!-- Submit Button -->
                <?php
                $buttonText = esc_html__('Save Settings', 'customtables');
                submit_button($buttonText, 'primary', 'savesettings');
                ?>

            </form>

    <?php endif; ?>

</div>