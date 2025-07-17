<table class="form-table" role="presentation">

	<?php /*
	<tr class="form-field form-required">
		<th scope="row">
			<label for="viewusergroups">
				<?php echo esc_html__('Who may view records', 'customtables'); ?>
			</label>
		</th>
		<td>
			<?php echo $this->admin_layout_edit->get_role_selector('viewusergroups', true); ?>
			<br/>
			<span class="description">Select user groups that may view records. If none selected, inherits from parent layout or defaults to Administrator.</span>
		</td>
	</tr>
	*/ ?>

	<tr class="form-field form-required">
		<th scope="row">
			<label for="publishstatus">
				<?php echo esc_html__('Default Publish Status', 'customtables'); ?>
			</label>
		</th>
		<td>
			<?php echo $this->admin_layout_edit->get_publish_status_selector('publishstatus',
				isset($this->admin_layout_edit->params['publishstatus']) ?
					(int)$this->admin_layout_edit->params['publishstatus'] :
					0
			); ?>
			<br/>
			<span class="description">Sets the default publish status for newly created records. If none selected, inherits from parent layout or defaults to Unpublished.</span>
		</td>
	</tr>

	<tr class="form-field form-required">
		<th scope="row">
			<label for="publishstatus">
				<?php echo esc_html__(' Output Format (MIME Type)', 'customtables'); ?>
			</label>
		</th>
		<td>
			<?php echo $this->admin_layout_edit->get_mimetype_selector('mimetype',
				$this->admin_layout_edit->params['mimetype'] ?? 'html'
			); ?>
			<br/>
			<span class="description">Select the MIME type for the content output. This determines the format used in the HTTP response. </span>
		</td>
	</tr>
</table>