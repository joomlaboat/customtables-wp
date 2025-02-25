<?php

use CustomTables\database;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once ABSPATH . 'wp-admin/admin-header.php';

if ($this->admin_table_edit->tableId === null)
	$new_tablename = '';
else
	$new_tablename = $this->admin_table_edit->ct->Table->tablename;

if ($this->admin_table_edit->ct->Env->advancedTagProcessor) {
	if ($this->admin_table_edit->tableId === null)
		$customphp = '';
	else
		$customphp = $this->admin_table_edit->ct->Table->tablerow['customphp'];

	$customTableName = $this->admin_table_edit->ct->Table->tablerow['customtablename'] ?? '';

	$customIdField = $this->admin_table_edit->ct->Table->tablerow['customidfield'] ?? '';

	if (empty($customIdField))
		$customIdField = 'id';

	$customIdFieldType = $this->admin_table_edit->ct->Table->tablerow['customidfieldtype'] ?? '';

	if (empty($customIdFieldType))
		$customIdFieldType = 'int UNSIGNED NOT NULL AUTO_INCREMENT';

	$primaryKeyPattern = $this->admin_table_edit->ct->Table->tablerow['primarykeypattern'] ?? '';

	if (empty($primaryKeyPattern))
		$primaryKeyPattern = 'AUTO_INCREMENT';

	$customFieldPrefix = $this->admin_table_edit->ct->Table->tablerow['customfieldprefix'] ?? '';
}

?>
	<div class="wrap">
		<h1>
			<?php
			if ($this->admin_table_edit->tableId == 0)
				esc_html_e('Add New Custom Table');
			else
				esc_html_e('Edit Custom Table');
			?>
		</h1>

		<?php if (isset($this->admin_table_edit->errors) && is_wp_error($this->admin_table_edit->errors)) : ?>
			<div class="error">
				<ul>
					<?php
					foreach ($this->admin_table_edit->errors->get_error_messages() as $err) {
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

		<?php if (current_user_can('install_plugins')): ?>

			<form method="post" name="createtable" id="createtable" class="validate" novalidate="novalidate">
				<input name="action" type="hidden" value="createtable"/>
				<?php wp_nonce_field('create-edit-table'); ?>

				<h2 class="nav-tab-wrapper wp-clearfix">
					<button type="button" data-toggle="tab" data-tabs=".gtabs.tableEditTabs" data-tab=".tableName-tab"
							class="nav-tab nav-tab-active">Table Name
					</button>
					<button type="button" data-toggle="tab" data-tabs=".gtabs.tableEditTabs"
							data-tab=".advanced-tab"
							class="nav-tab">Advanced
					</button>
					<button type="button" data-toggle="tab" data-tabs=".gtabs.tableEditTabs"
							data-tab=".schema-tab"
							class="nav-tab">Schema
					</button>
				</h2>

				<div class="gtabs tableEditTabs">
					<div class="gtab active tableName-tab" style="margin-left:-20px;">

						<table class="form-table" role="presentation">
							<!-- Table Name Field -->
							<tr class="form-field form-required">
								<th scope="row">
									<label for="tablename">
										<?php echo esc_html__('Table Name', 'customtables'); ?>
										<span class="description">(<?php echo esc_html__('required', 'customtables'); ?>)</span>
									</label>
								</th>
								<td>
									<input name="tablename" type="text" id="tablename"
										   value="<?php echo esc_attr($new_tablename); ?>" aria-required="true"
										   autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="60"/>
								</td>
							</tr>

							<!-- Table Title Fields -->
							<?php
							$moreThanOneLang = false;
							foreach ($this->admin_table_edit->ct->Languages->LanguageList as $lang): ?>
								<?php
								$id = ($moreThanOneLang ? 'tabletitle_' . $lang->sef : 'tabletitle');
								$cssclass = ($moreThanOneLang ? 'form-control valid form-control-success' : 'form-control required valid form-control-success');
								$att = ($moreThanOneLang ? '' : ' required ');

								$vlu = $item_array[$id] ?? ($this->admin_table_edit->ct->Table !== null ? $this->admin_table_edit->ct->Table->tablerow[$id] : '');
								?>

								<tr class="form-field<?php echo esc_html(!$moreThanOneLang ? ' form-required' : ''); ?>">
									<th scope="row">
										<label for="<?php echo esc_html($id); ?>">
											<?php echo esc_html__('Table Title', 'customtables'); ?>
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
						</table>
					</div>

					<div class="gtab advanced-tab" style="margin-left:-20px;">
						<?php if ($this->admin_table_edit->ct->Env->advancedTagProcessor): ?>
							<?php include('customtables-tables-edit-advanced.php'); ?>
						<?php else: ?>
							<a href="https://ct4.us/product/custom-tables-pro-for-wordpress/" target="_blank">
								<?php echo esc_html__("Available in PRO Version", "customtables"); ?>
							</a>
						<?php endif; ?>
					</div>

					<div class="gtab schema-tab" style="margin-left:-20px;">
						<div class="CustomTablesDocumentationTips">
							<?php echo $this->admin_table_edit->getTableSchema(); ?>
						</div>
					</div>

				</div>

				<div style="display:inline-block;">
					<?php
					$buttonText = ($this->admin_table_edit->tableId == 0) ? esc_html__('Save Table', 'customtables') : esc_html__('Save Table', 'customtables');
					submit_button($buttonText, 'primary', 'createtable', true, array('id' => 'createtable-submit'));
					?></div>

				<div style="display:inline-block;margin-left:20px;">
					<!-- Cancel Button -->
					<?php
					submit_button(esc_html__('Cancel', 'customtables'), 'secondary', 'createtable-cancel', true,
						array('id' => 'createtable-cancel', 'onclick' => 'window.location.href="admin.php?page=customtables-tables";return false;'));
					?></div>

			</form>
		<?php endif; ?>
	</div>


<?php if (!empty($new_tablename)): ?>
	<div class="CustomTablesDocumentationTips">
		<h4>Adding Catalog Views and Edit Forms</h4>
		<p>You can use these shortcodes to display table records and add/edit forms:</p>
		<br/>
		<p style="font-weight:bold;">Basic Catalog Views</p>
		<div>
			<pre>[customtables table="<?php echo $new_tablename; ?>"]</pre>
			- Displays catalog view using table name
		</div>
		<div>
			<pre>[customtables table="<?php echo $this->admin_table_edit->tableId; ?>"]</pre>
			- Displays catalog view using table ID
		</div>
		<div>
			<pre>[customtables table="<?php echo $new_tablename; ?>" view="catalog"]</pre>
			- Explicit catalog view
		</div>
		<p style="font-weight:bold;">Edit Forms</p>
		<div>
			<pre>[customtables table="<?php echo $new_tablename; ?>" view="edit"]</pre>
			- Adds a form to create a new record
		</div>
		<p style="font-weight:bold;">Catalog with Parameters</p>
		<div>
			<pre>[customtables table="<?php echo $new_tablename; ?>" view="catalog" limit="5"]</pre>
			- Shows only 5 records
		</div>
		<p>Note: The limit parameter controls the number of displayed records. Use limit="0" or omit the parameter to
			show
			all records.</p>
	</div>
<?php endif; ?>

	<p><a href="https://ct4.us/contact-us/"
		  target="_blank"><?php echo esc_html__('Questions or issues? Contact support.', 'customtables'); ?></a></p>

<?php
require_once ABSPATH . 'wp-admin/admin-footer.php';
