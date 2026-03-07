<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

?>

<table class="form-table" role="presentation">
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
				   if (empty($vlu))
					   $vlu = 'ct_';

				   echo esc_html($vlu); ?>"
				   aria-required="false"
				   autocapitalize="none" autocomplete="off" maxlength="100"/>
			<br/>
			Specifies the prefix added to all table field names (e.g., 'ct_FieldName'). This prefix helps prevent
			conflicts with MySQL reserved words and ensures database compatibility. Only modify this if you have a
			specific reason to use a different prefix scheme. Type NO-PREFIX to have field names without a prefix.
			Changing the prefix doesn't automatically rename fields. You will have to do it manually.
		</td>
	</tr>


	<tr class="form-field form-required">
		<th scope="row">
			<label for="sqlselecttag">
				<?php echo esc_html__('{{ tables.sqlselect() }} Tag', 'customtables'); ?>
			</label>
		</th>
		<td>
			<?php
			$vlu = get_option('customtables-sqlselecttag', ''); // Default is empty

			$types = [
					'0' => esc_html__('Disabled', 'customtables'),
					'1' => esc_html__('Enabled', 'customtables'), // Added UM icon set
			];
			?>

			<select name="sqlselecttag" id="sqlselecttag">
				<?php foreach ($types as $key => $label) : ?>
					<option value="<?php echo esc_attr($key); ?>" <?php selected($vlu, $key); ?>>
						<?php echo esc_html($label); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<br/>
			Allows execution of custom SELECT queries via the {{ tables.sqlselect() }} tag.
			This feature is intended for advanced users only.
			A dedicated READ-ONLY MySQL database user must be configured in Joomla configuration.php.
			UPDATE, DELETE, INSERT and other write operations are not supported.
		</td>
	</tr>

</table>