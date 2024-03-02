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

//include ('customtables-layouts-edit-help.php');

use CustomTables\ListOfLayouts;

include('customtables-layouts-edit-head.php');

require_once ABSPATH . 'wp-admin/admin-header.php';

$onPageLoads = array();

?>
    <div class="wrap">

        <form method="post" name="createlayout" id="createlayout" class="validate" novalidate="novalidate">
            <input name="action" type="hidden" value="createlayout"/>
			<?php wp_nonce_field('create-edit-layout'); ?>

            <h1 id="add-new-user">
				<?php
				if ($this->admin_layout_edit->layoutId == 0)
					esc_html_e('Add New Custom Layout');
				else
					esc_html_e('Edit Custom Layout');
				?>
                <div style="display: inline-block;margin-left:20px;"><?php
					$buttonText = ($this->admin_layout_edit->layoutId == 0) ? esc_html(__('Save New Layout', 'customtables')) : esc_html(__('Save Layout', 'customtables'));
					submit_button($buttonText, 'primary', 'ct-savelayout-top', false, array('id' => 'ct-savelayout-top'));
					?>
                    <input class="button" type="button" onClick="showFieldTagModalForm();" value="Field Tags"/>
                    <input class="button" type="button" onClick="showLayoutTagModalForm();" value="Layout Tags"/>
                </div>
            </h1>

			<?php if (isset($errors) && is_wp_error($errors)) : ?>
                <div class="error">
                    <ul>
						<?php
						foreach ($errors->get_error_messages() as $err) {
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
            <!--<p><?php

			if ($this->admin_layout_edit->layoutId === null)
				esc_html_e('Create a new layout.');
			else
				esc_html_e('Edit layout.');
			?>
            </p>-->


			<?php include('customtables-layouts-edit-details.php'); ?>

			<?php include('customtables-layouts-edit-editors.php'); ?>

            <!-- Submit Button -->
			<?php
			$buttonText = ($this->admin_layout_edit->layoutId == 0) ? esc_html(__('Add New Layout', 'customtables')) : esc_html(__('Save Layout', 'customtables'));
			submit_button($buttonText, 'primary', 'ct-savelayout', true, array('id' => 'ct-savelayout'));
			?>

            <div id="allLayoutRaw"
                 style="display:none;"><?php echo wp_json_encode(ListOfLayouts::getLayouts()); ?></div>
        </form>
		<?php } // End if (current_user_can('install_plugins')) ?>
    </div>

    <div id="layouteditor_Modal" class="layouteditor_modal">

        <!-- Modal content -->
        <div class="layouteditor_modal-content" id="layouteditor_modalbox">
            <span class="layouteditor_close">&times;</span>
            <div id="layouteditor_modal_content_box">
            </div>
        </div>

    </div>
<?php

require_once ABSPATH . 'wp-admin/admin-footer.php';
