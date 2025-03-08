<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

//include ('customtables-layouts-edit-help.php');

use CustomTables\common;
use CustomTables\CT;
use CustomTables\ListOfLayouts;

$errors = common::getTransientMessages('customtables_error_message');
if (isset($this->admin_layout_edit->errors) && is_wp_error($this->admin_layout_edit->errors)) {
	foreach ($this->admin_layout_edit->errors->get_error_messages() as $error)
		$errors []= $error;
}
$messages = common::getTransientMessages('customtables_success_message');

include('customtables-layouts-edit-head.php');

require_once ABSPATH . 'wp-admin/admin-header.php';

$allowed_html = array(
	'a' => array(
		'href' => array(),
		'title' => array(),
		'download' => array(),
		'target' => array()
	)
);

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

	$list = [];

	try {
		$tempCT = new CT([], true);
		$tempCT->getTable($table['id']);
		if ($tempCT->Table !== null) {
			foreach ($tempCT->Table->fields as $field) {
				if ((int)$field['published'] === 1)
					$list[] = [$field['id'], $field['fieldname']];
			}
		}
	} catch (Exception $e) {
		$errors[] = $e->getMessage();
	}

	echo '<div id="fieldsData' . $table['id'] . '" style="display:none;">' . common::ctJsonEncode($list) . '</div>';

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
				<div style="display: inline-block;margin-left:20px;">
					<?php submit_button(esc_html__('Save Layout', 'customtables'), 'primary', 'ct-savelayout-top', false, array('id' => 'ct-savelayout-top')); ?>
					<input class="button" type="button" onClick="openLayoutWizard();" value="Layout Auto Creator"/>
					<input class="button" type="button" onClick="showFieldTagModalForm();" value="Field Tags"/>
					<input class="button" type="button" onClick="showLayoutTagModalForm();" value="Layout Tags"/>
				</div>
			</h1>

			<?php common::showTransient($errors, $messages); ?>

			<div id="ajax-response"></div>

			<?php
			if (current_user_can('install_plugins')) {

			if ($this->admin_layout_edit->layoutId === null)
				esc_html_e('Create a new layout.');
			else
				esc_html_e('Edit layout.');
			?>

			<?php include('customtables-layouts-edit-details.php'); ?>

			<?php include('customtables-layouts-edit-editors.php'); ?>

			<!-- Submit Button -->
			<?php
			submit_button(esc_html__('Save Layout', 'customtables'), 'primary', 'ct-savelayout', true, array('id' => 'ct-savelayout'));
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


		<p><a href="https://ct4.us/contact-us/"
			  target="_blank"><?php echo esc_html__('Have questions? Get in touch with our support team.', 'customtables'); ?></a>
		</p>
	</div>

<?php if ($this->admin_layout_edit->layoutId != 0): ?>
	<div class="CustomTablesDocumentationTips">
		<h4>Adding Layout Output</h4>
		<p>You can use these shortcodes to display table records or add/edit/details forms using layouts.</p>
		<br/>
		<p style="font-weight:bold;">Basic Catalog Views</p>
		<div>
			<pre>[customtables layout="<?php echo $this->admin_layout_edit->layoutRow['layoutname']; ?>"]</pre>
			- Displays layout using layout name
		</div>
		<div>
			<pre>[customtables layout="<?php echo $this->admin_layout_edit->layoutId; ?>"]</pre>
			- Displays layout using layout ID
		</div>
		<p style="font-weight:bold;">Edit or Details Forms</p>
		<div>
			<pre>[customtables layout="<?php echo $this->admin_layout_edit->layoutRow['layoutname']; ?>" id="1"]</pre>
			- Displays edit/details form for record #1
		</div>
		<p style="font-weight:bold;">Catalog with Parameters</p>
		<div>
			<pre>[customtables layout="<?php echo $this->admin_layout_edit->layoutRow['layoutname']; ?>" limit="5"]</pre>
			- Shows only 5 records
		</div>
		<p>Note: The limit parameter controls the number of displayed records. Use limit="0" or omit the parameter to
			show all records.</p>
	</div>
<?php endif; ?>


<?php

require_once ABSPATH . 'wp-admin/admin-footer.php';
