<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;
use CustomTables\CT;

$errors = common::getTransientMessages('customtables_error_message');
if (isset($this->admin_field_edit->errors) && is_wp_error($this->admin_field_edit->errors)) {
	foreach ($this->admin_field_edit->errors->get_error_messages() as $error)
		$errors [] = $error;
}

$messages = common::getTransientMessages('customtables_success_message');

require_once ABSPATH . 'wp-admin/admin-header.php';

$allowed_html = array(
	'a' => array(
		'href' => array(),
		'title' => array(),
		'download' => array(),
		'target' => array()
	)
);

foreach ($this->admin_field_edit->allTables as $table) {
	$tableID = $table['id'];

	try {
		$tempCT = new CT([], true);
		$tempCT->getTable($tableID);

		if ($tempCT->Table !== null) {
			$list = [];
			foreach ($tempCT->Table->fields as $field)
				$list[] = [$field['id'], $field['fieldname']];

			echo '<div id="fieldsData' . $tableID . '" style="display:none;">' . common::ctJsonEncode($list) . '</div>
    ';
		}
	} catch (Exception $e) {
		$errors [] = $e->getMessage();
	}
}
?>
	<div class="wrap">
		<h1 id="add-new-user">
			<?php
			if (isset($this->admin_field_edit->ct->Table) and $this->admin_field_edit->ct->Table->tablename !== null) {
				esc_html_e('Custom Tables - Table', 'customtables');
				echo ' "' . esc_html($this->admin_field_edit->ct->Table->tabletitle) . '" - ';
				if ($this->admin_field_edit->fieldId == 0)
					esc_html_e('Add New Field');
				else
					esc_html_e('Edit Field');
			} else {
				esc_html_e('Custom Tables - Fields', 'customtables');
				$errors [] = 'Table not selected or not found.';
			}
			?>
		</h1>

		<?php common::showTransient($errors, $messages); ?>

		<div id="ajax-response"></div>

		<?php
		if (current_user_can('install_plugins')) {
			?>


			<?php
			if (isset($this->admin_field_edit->ct->Table) and $this->admin_field_edit->ct->Table->tablename !== null):
				?>
				<p><?php

					if ($this->admin_field_edit->fieldId === null)
						esc_html_e('Create a brand new field.');
					else
						esc_html_e('Edit field.');
					?>
				</p>

				<script>
					<?php
					if ($this->admin_field_edit->ct->Env->advancedTagProcessor)
						echo esc_js('proversion=true;') . PHP_EOL;

					//resulting line example: all_tables=[["29","kot3","kot3"],["30","kot5","kot5"],["31","kot6","kot6"],["25","test1","Test 1"]];
					echo 'all_tables=' . wp_kses_post(wp_json_encode($this->admin_field_edit->allTables)) . ';' . PHP_EOL;
					?>
				</script>

				<form method="post" name="createfield" id="createfield" class="validate" novalidate="novalidate">
					<input name="action" type="hidden" value="createfield"/>
					<input name="table" id="table" type="hidden"
						   value="<?php echo esc_html($this->admin_field_edit->tableId); ?>"/>
					<?php wp_nonce_field('create-edit-field'); ?>

					<table class="form-table" role="presentation">
						<!-- Field Name Field -->
						<tr class="form-field form-required">
							<th scope="row">
								<label for="fieldname">
									<?php echo esc_html__('Field Name', 'customtables'); ?>
									<span class="description">(<?php echo esc_html__('required', 'customtables'); ?>)</span>
								</label>
							</th>
							<td>
								<input name="fieldname" type="text" id="fieldname"
									   value="<?php echo esc_attr($this->admin_field_edit->fieldRow['fieldname']); ?>"
									   aria-required="true"
									   autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="60"/>
							</td>
						</tr>

						<!-- Field Title Fields -->
						<?php
						$moreThanOneLang = false;
						foreach ($this->admin_field_edit->ct->Languages->LanguageList as $lang): ?>
							<?php
							$id = ($moreThanOneLang ? 'fieldtitle_' . $lang->sef : 'fieldtitle');
							$cssclass = ($moreThanOneLang ? 'form-control valid form-control-success' : 'form-control required valid form-control-success');
							$att = ($moreThanOneLang ? '' : ' required ');
							$vlu = $this->admin_field_edit->fieldRow[$id] ?? null;
							?>

							<tr class="form-field<?php echo esc_html(!$moreThanOneLang ? ' form-required' : ''); ?>">
								<th scope="row">
									<label for="<?php echo esc_html($id); ?>">
										<?php echo esc_html__('Field Title', 'customtables'); ?>
										<?php if (!$moreThanOneLang): ?>
											<span class="description">(<?php echo esc_html__('required', 'customtables'); ?>)</span>
										<?php endif; ?>
										<br/>
										<b><?php echo esc_html($lang->title); ?></b>
									</label>
								</th>
								<td>
									<input name="<?php echo esc_html($id); ?>" type="text"
										   id="<?php echo esc_html($id); ?>"
										   value="<?php echo esc_html($vlu); ?>" maxlength="255"/>
								</td>
							</tr>

							<?php $moreThanOneLang = true; ?>
						<?php endforeach; ?>

						<!-- Field Type Field -->
						<tr class="form-field form-required">
							<th scope="row">
								<label for="type">
									<?php echo esc_html__('Field Type', 'customtables'); ?>
									<span class="description">(<?php echo esc_html__('required', 'customtables'); ?>)</span>
								</label>
							</th>
							<td>
								<?php

								$allowed_html = array(
									'option' => array(
										'value' => array(),
										'selected' => array()
									)
								);

								$selectBoxOptions = [];

								foreach ($this->admin_field_edit->fieldTypes as $type) {
									$selected = $this->admin_field_edit->fieldRow['type'] == $type['name'];
									$selectBoxOptions[] = '<option value="' . $type['name'] . '"' . ($selected ? ' selected="selected"' : '') . '>' . $type['label'] . '</option>';
								}

								$selectBoxOptionsSafe = implode('', $selectBoxOptions);

								echo '<select name="type" id="type" onchange="typeChanged();">' . wp_kses($selectBoxOptionsSafe, $allowed_html) . '</select>';
								?>
							</td>
						</tr>

						<!-- Field Type Params Field -->
						<tr class="form-field form-required">
							<th scope="row">
								<label for="typeparams">
									<?php echo esc_html__('Type Parameters', 'customtables'); ?>
									<span class="description">(<?php echo esc_html__('required', 'customtables'); ?>)</span>
								</label>
							</th>
							<td>
								<div class="typeparams_box" id="typeparams_box"></div>
								<br/>
								<input type="hidden" name="typeparams" id="typeparams" class=""
									   readonly="readonly" maxlength="1024"
									   value='<?php echo esc_html($this->admin_field_edit->fieldRow['typeparams']); ?>'>
							</td>
						</tr>

						<!-- Is Field Required -->
						<tr class="form-field">
							<th scope="row">
								<label for="isrequired">
									<?php echo esc_html__('Is Required', 'customtables'); ?>
								</label>
							</th>
							<td>
								<fieldset>
									<legend class="screen-reader-text">
										<?php echo esc_html__('Is Required', 'customtables'); ?>
									</legend>

									<label class="radio-container">
										<input type="radio"
											   name="isrequired"
											   value="1"
											<?php checked($this->admin_field_edit->fieldRow['isrequired'], '1'); ?>
										/>
										<span><?php echo esc_html__('Yes', 'customtables'); ?></span>
									</label>

									<br/>

									<label class="radio-container">
										<input type="radio"
											   name="isrequired"
											   value="0"
											<?php checked($this->admin_field_edit->fieldRow['isrequired'], '0'); ?>
										/>
										<span><?php echo esc_html__('No', 'customtables'); ?></span>
									</label>
								</fieldset>
							</td>
						</tr>
					</table>

					<!-- Submit Button -->
					<div style="display:inline-block;">
						<?php submit_button(esc_html__('Save Field', 'customtables'), 'primary', 'createfield', true, array('id' => 'createfield-submit')); ?>
					</div>

					<div style="display:inline-block;margin-left:20px;">
						<!-- Cancel Button -->
						<?php
						submit_button(esc_html__('Cancel', 'customtables'), 'secondary', 'createfield-cancel', true,
							array('id' => 'createfield-cancel', 'onclick' => 'window.location.href="admin.php?page=customtables-fields&table=' . esc_html($this->admin_field_edit->tableId) . '";return false;'));
						?></div>

					<script>
						updateTypeParams("type", "typeparams", "typeparams_box");
						<?php if(!$this->admin_field_edit->ct->Env->advancedTagProcessor): ?>
						//disableProField("jform_defaultvalue");
						//disableProField("jform_valuerule");
						//disableProField("jform_valuerulecaption");
						<?php else: ?>
						proversion = true;
						<?php endif; ?>
					</script>

					<div id="ct_fieldtypeeditor_box"
						 style="display: none;"><?php
						echo implode(',', common::folderList(CUSTOMTABLES_IMAGES_PATH)); ?></div>
				</form>
			<?php endif; ?>
		<?php } // End if (current_user_can('install_plugins')) ?>

		<p><a href="https://ct4.us/contact-us/"
			  target="_blank"><?php echo esc_html__('For support, please reach out to us.', 'customtables'); ?></a></p>
	</div>
<?php
require_once ABSPATH . 'wp-admin/admin-footer.php';
