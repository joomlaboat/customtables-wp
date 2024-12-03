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

use CustomTables\common;
use CustomTables\CT;
use CustomTables\ListOfLayouts;

include('customtables-layouts-edit-head.php');

require_once ABSPATH . 'wp-admin/admin-header.php';

$onPageLoads = array();

?>

    <script>
		<?php if ($this->admin_layout_edit->ct->Env->advancedTagProcessor): ?>
        proversion = true;
		<?php endif; ?>
        all_tables = <?php echo wp_kses_post(wp_json_encode($this->admin_layout_edit->allTables)) ?>;
    </script>
<?php
foreach ($this->admin_layout_edit->allTables as $table) {
    $tempCT = new CT;

    $tempCT->getTable($table['id']);
    if($tempCT->Table !== null) {
        $list = [];
        foreach ($tempCT->Table->fields as $field) {
            if((int)$field['published'] === 1)
                $list[] = [$field['id'], $field['fieldname']];
        }

        echo '<div id="fieldsData' . $table['id'] . '" style="display:none;">' . common::ctJsonEncode($list) . '</div>
    ';
    }
}

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
					$buttonText = ($this->admin_layout_edit->layoutId == 0) ? esc_html__('Save New Layout', 'customtables') : esc_html__('Save Layout', 'customtables');
					submit_button($buttonText, 'primary', 'ct-savelayout-top', false, array('id' => 'ct-savelayout-top'));
					?>
                    <input class="button" type="button" onClick="openLayoutWizard();" value="Layout Auto Creator"/>
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
			$buttonText = ($this->admin_layout_edit->layoutId == 0) ? esc_html__('Add New Layout', 'customtables') : esc_html__('Save Layout', 'customtables');
			submit_button($buttonText, 'primary', 'ct-savelayout', true, array('id' => 'ct-savelayout'));
			?>

            <div id="allLayoutRaw"
                 style="display:none;"><?php try {
					echo wp_json_encode(ListOfLayouts::getLayouts());
				} catch (Exception $e) {
					echo 'Cannot load the list of Layouts.';
				} ?></div>
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



        <p><a href="https://ct4.us/contact-us/" target="_blank"><?php echo esc_html__('Have questions? Get in touch with our support team.', 'customtables'); ?></a></p>
    </div>

<?php if($this->admin_layout_edit->layoutId != 0): ?>
<div class="CustomTablesDocumentationTips">
    <h4>Adding Layout Output</h4>
    <p>You can use these shortcodes to display table records or add/edit/details forms using layouts.</p>
    <br/>
    <p style="font-weight:bold;">Basic Catalog Views</p>
    <div><pre>[customtables layout="<?php echo $this->admin_layout_edit->layoutRow['layoutname']; ?>"]</pre> - Displays layout using layout name</div>
    <div><pre>[customtables layout="<?php echo $this->admin_layout_edit->layoutId; ?>"]</pre> - Displays layout using layout ID</div>
    <p style="font-weight:bold;">Edit or Details Forms</p>
    <div><pre>[customtables layout="<?php echo $this->admin_layout_edit->layoutRow['layoutname']; ?>" id="1"]</pre> - Displays edit/details form for record #1</div>
    <p style="font-weight:bold;">Catalog with Parameters</p>
    <div><pre>[customtables layout="<?php echo $this->admin_layout_edit->layoutRow['layoutname']; ?>" limit="5"]</pre> - Shows only 5 records</div>
    <p>Note: The limit parameter controls the number of displayed records. Use limit="0" or omit the parameter to show all records.</p>
</div>
<?php endif; ?>


<?php

require_once ABSPATH . 'wp-admin/admin-footer.php';
