<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

?>

<table class="form-table" role="presentation">
	<!-- Google Map -->
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
				   autocapitalize="none" autocomplete="off" maxlength="40"/>
		</td>
	</tr>
	<!-- Google Drive -->
	<tr class="form-field form-required">
		<th scope="row">
			<label for="googledriveapikey">
				<?php echo esc_html__('Google Drive API Key', 'customtables'); ?>
			</label>
		</th>
		<td>
			<input name="googledriveapikey" type="text" id="googledriveapikey"
				   value="<?php echo esc_html(get_option('customtables-googledriveapikey')); ?>"
				   aria-required="false"
				   autocapitalize="none" autocomplete="off" maxlength="40"/>
		</td>
	</tr>
	<tr class="form-field form-required">
		<th scope="row">
			<label for="googledriveclientid">
				<?php echo esc_html__('Google Drive Client ID', 'customtables'); ?>
			</label>
		</th>
		<td>
			<input name="googledriveclientid" type="text" id="googledriveclientid"
				   value="<?php echo esc_html(get_option('customtables-googledriveclientid')); ?>"
				   aria-required="false"
				   autocapitalize="none" autocomplete="off" maxlength="100"/>
		</td>
	</tr>


</table>
