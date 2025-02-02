<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

?>

<h2 class="nav-tab-wrapper wp-clearfix">
	<button type="button" onclick="CustomTablesAdminLayoutsTabClicked(0,'layoutcode');return false;" data-toggle="tab"
			data-tabs=".gtabs.layouteditorTabs" data-tab=".layoutcode-tab" class="nav-tab nav-tab-active">HTML (Desktop)
	</button>

	<button type="button" onclick="CustomTablesAdminLayoutsTabClicked(1,'layoutmobile');return false;"
			data-toggle="tab" data-tabs=".gtabs.layouteditorTabs" data-tab=".layoutmobile-tab" class="nav-tab">HTML
		(Mobile)
	</button>
	<button type="button" onclick="CustomTablesAdminLayoutsTabClicked(2,'layoutcss');return false;"
			data-toggle="tab" data-tabs=".gtabs.layouteditorTabs" data-tab=".layoutcss-tab" class="nav-tab">CSS
	</button>
	<button type="button" onclick="CustomTablesAdminLayoutsTabClicked(3,'layoutjs');return false;" data-toggle="tab"
			data-tabs=".gtabs.layouteditorTabs" data-tab=".layoutjs-tab" class="nav-tab">JavaScript
	</button>
	<button type="button" data-toggle="tab"
			data-tabs=".gtabs.layouteditorTabs" data-tab=".params-tab" class="nav-tab">Parameters
	</button>
</h2>

<div class="gtabs layouteditorTabs">

	<div class="gtab active layoutcode-tab" style="margin-left:-20px;">
		<textarea id="layoutcode"
				  name="layoutcode"><?php echo esc_textarea($this->admin_layout_edit->layoutRow['layoutcode'] ?? ''); ?></textarea>
	</div>

	<div class="gtab layoutmobile-tab" style="margin-left:-20px;">
		<?php if ($this->admin_layout_edit->ct->Env->advancedTagProcessor): ?>
			<textarea id="layoutmobile" name="layoutmobile">
				<?php if (isset($this->admin_layout_edit->layoutRow['layoutmobile'])): ?>
					<?php echo esc_textarea($this->admin_layout_edit->layoutRow['layoutmobile']) ?? ''; ?>
				<?php endif; ?>
			</textarea>
		<?php else: ?>
			<a href="https://ct4.us/product/custom-tables-pro-for-wordpress/" target="_blank">
				<?php echo esc_html__("Available in PRO Version", "customtables"); ?>
			</a>
		<?php endif; ?>
	</div>

	<div class="gtab layoutcss-tab" style="margin-left:-20px;">
		<?php if ($this->admin_layout_edit->ct->Env->advancedTagProcessor): ?>
			<textarea id="layoutcss" name="layoutcss">
				<?php if (isset($this->admin_layout_edit->layoutRow['layoutcss'])): ?>
					<?php echo esc_textarea($this->admin_layout_edit->layoutRow['layoutcss']) ?? ''; ?>
				<?php endif; ?>
			</textarea>
		<?php else: ?>
			<a href="https://ct4.us/product/custom-tables-pro-for-wordpress/" target="_blank">
				<?php echo esc_html__("Available in PRO Version", "customtables"); ?>
			</a>
		<?php endif; ?>
	</div>

	<div class="gtab layoutjs-tab" style="margin-left:-20px;">
		<?php if ($this->admin_layout_edit->ct->Env->advancedTagProcessor): ?>
			<textarea id="layoutjs" name="layoutjs">
				<?php if (isset($this->admin_layout_edit->layoutRow['layoutjs'])): ?>
					<?php echo esc_textarea($this->admin_layout_edit->layoutRow['layoutjs']) ?? ''; ?>
				<?php endif; ?>
			</textarea>
		<?php else: ?>
			<a href="https://ct4.us/product/custom-tables-pro-for-wordpress/" target="_blank">
				<?php echo esc_html__("Available in PRO Version", "customtables"); ?>
			</a>
		<?php endif; ?>
	</div>

	<div class="gtab params-tab" style="margin-left:-20px;">
		<?php if ($this->admin_layout_edit->ct->Env->advancedTagProcessor): ?>
			<?php include('customtables-layouts-edit-params.php'); ?>
		<?php else: ?>
			<a href="https://ct4.us/product/custom-tables-pro-for-wordpress/" target="_blank">
				<?php echo esc_html__("Available in PRO Version", "customtables"); ?>
			</a>
		<?php endif; ?>
	</div>
</div>
