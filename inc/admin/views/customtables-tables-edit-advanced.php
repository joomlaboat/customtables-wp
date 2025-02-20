<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

?>

<table class="form-table" role="presentation">
	<tr class="form-field form">
		<th scope="row">
			<label for="customphp">
				<?php echo esc_html__('Custom PHP', 'customtables'); ?>
			</label>
		</th>
		<td>
			<input name="customphp" type="text" id="customphp"
				   value="<?php echo esc_attr($customphp); ?>" aria-required="false"
				   autocapitalize="none" autocorrect="off" autocomplete="off"
				   maxlength="255"/>
		</td>
	</tr>

	<tr class="form-field form">
		<th scope="row">
			<label for="customtablename">
			a	<?php echo esc_html__('Third-Party Table', 'customtables'); ?>
			</label>
		</th>
		<td>
			<select name="customtablename" id="customtablename">
				<?php foreach ($this->admin_table_edit->allTables as $key => $label) :

					?>
					<option value="<?php echo esc_attr($key); ?>" <?php selected($customTableName, $key); ?>>
						<?php echo esc_html($label); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<br/>
			<span class="description">
				To connect to third-party tables - any table in the database. Except #__users and Custom Tables
			</span>
		</td>
	</tr>

	<tr class="form-field form">
		<th scope="row">
			<label for="customidfield">
				<?php echo esc_html__('Primary Key Field', 'customtables'); ?>
			</label>
		</th>
		<td>
			<input name="customidfield" type="text" id="customidfield"
				   value="<?php echo esc_attr($customIdField); ?>" aria-required="false"
				   autocapitalize="none" autocorrect="off" autocomplete="off"
				   maxlength="255"/>
			<br/>
			<span class="description">Primary key field name</span>
		</td>
	</tr>

	<tr class="form-field form">
		<th scope="row">
			<label for="customidfieldtype">
				<?php echo esc_html__('Primary Key Field Type', 'customtables'); ?>
			</label>
		</th>
		<td>
			<input name="customidfieldtype" type="text" id="customidfieldtype"
				   value="<?php echo esc_attr($customIdFieldType); ?>" aria-required="false"
				   autocapitalize="none" autocorrect="off" autocomplete="off"
				   maxlength="255"/>
			<br/>
			<span class="description">Primary key field type</span>
		</td>
	</tr>

	<tr class="form-field form">
		<th scope="row">
			<label for="primarykeypattern">
				<?php echo esc_html__('Primary Key Generation Pattern', 'customtables'); ?>
			</label>
		</th>
		<td>
			<input name="primarykeypattern" type="text" id="primarykeypattern"
				   value="<?php echo esc_attr($primaryKeyPattern); ?>" aria-required="false"
				   autocapitalize="none" autocorrect="off" autocomplete="off"
				   maxlength="255"/>
			<br/>
			<div class="description">
				<p>Define how primary keys are generated for new records.<br/>
					 Use AUTO_INCREMENT for automatic numbering, or create custom patterns using Twig syntax.</p>
				<b>Examples:</b>
				<ul>
					<li>AUTO_INCREMENT</li>
					<li>PROJECT-{{ random(1000,9999) }}</li>
					<li>{{ 'prefix-' ~ now|date('Y-m-d H:i:s.u')|md5 }}</li>
					<li>PRJ-{{ (now|date('Y-m-d H:i:s.u')|md5)|slice(0,10) }}-{{ random(1000,9999) }}</li>
				</ul>
			</div>
		</td>
	</tr>

	<tr class="form-field form">
		<th scope="row">
			<label for="customfieldprefix">
				<?php echo esc_html__('Field Name Prefix', 'customtables'); ?>
			</label>
		</th>
		<td>
			<input name="customfieldprefix" type="text" id="customfieldprefix"
				   value="<?php echo esc_attr($customFieldPrefix); ?>" aria-required="false"
				   autocapitalize="none" autocorrect="off" autocomplete="off"
				   maxlength="255"/>
			<br/>
			<span class="description">
				Specifies the prefix added to all table field names (e.g., 'ct_FieldName').<br/>
				This prefix helps prevent conflicts with MySQL reserved words and ensures database compatibility.<br/>
				Only modify this if you have a specific reason to use a different prefix scheme.<br/>
				Type NO-PREFIX to have field names without a prefix.<br/>
				Changing the prefix doesn't automatically renames fields. You will have to do it manually.
			</span>
		</td>
	</tr>
</table>
