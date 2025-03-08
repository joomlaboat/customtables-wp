<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;
use CustomTables\Integrity\IntegrityFields;

$errors = common::getTransientMessages('customtables_error_message');
if (isset($this->admin_field_list->errors) && is_wp_error($this->admin_field_list->errors)) {
	foreach ($this->admin_field_list->errors->get_error_messages() as $error)
		$errors []= $error;
}
$messages = common::getTransientMessages('customtables_success_message');

$page = common::inputGetCmd('page');

?>
<div class="wrap">

	<?php echo '<a href="admin.php?page=customtables-tables" class="page-title-action">'
		. esc_html__('&laquo; Tables', 'customtables') . '</a>&nbsp;<br/>'; ?>

	<h1 class="wp-heading-inline">
		<?php
		if (isset($this->admin_field_list->ct->Table) and $this->admin_field_list->ct->Table->tablename !== null) {
			esc_html_e('Custom Tables - Table', 'customtables');
			echo ' "' . esc_html($this->admin_field_list->ct->Table->tabletitle) . '" - ';
			esc_html_e('Fields', 'customtables');
		} else {
			esc_html_e('Custom Tables - Fields', 'customtables');
			$errors [] = 'Table not selected or not found.';
		}
		?></h1>

	<?php
	if (isset($this->admin_field_list->ct->Table) and $this->admin_field_list->ct->Table->tablename !== null) {
		echo '<a href="admin.php?page=customtables-fields-edit&table=' . esc_html($this->admin_field_list->tableId) . '&field=0" class="page-title-action">'
			. esc_html__('Add New', 'customtables') . '</a>';
	}
	?>

	<hr class="wp-header-end">

	<?php
	if ($this->admin_field_list->tableId != 0) {
		$link = 'admin.php?page=customtables-fields&table=' . $this->admin_field_list->tableId;
		try {
			$result_clean = IntegrityFields::checkFields($this->admin_field_list->ct, $link);

			if ($result_clean !== '')
				$messages [] = $result_clean;
		} catch (Exception $e) {
			$errors [] = 'Error in integrity check.';
		}
	}
	?>

	<?php common::showTransient($errors, $messages); ?>

	<div id="customtables">
		<div id="customtables-post-body">
			<form id="customtables-admin-field-list-form" method="post">
				<input type="hidden" name="page" value="<?php echo esc_html($page); ?>"/>
				<?php
				$this->admin_field_list->search_box(esc_html__('Search Fields', 'customtables'), 'nds-field-find');
				$this->admin_field_list->views();
				$this->admin_field_list->display();
				?>
			</form>
		</div>
	</div>

	<p><a href="https://ct4.us/contact-us/"
		  target="_blank"><?php echo esc_html__('Questions or issues? Contact support.', 'customtables'); ?></a></p>
</div>