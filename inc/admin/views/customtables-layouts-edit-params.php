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
			<label for="addusergroups">
				<?php echo esc_html__('Who may add records', 'customtables'); ?>
			</label>
		</th>
		<td>
			<?php echo $this->admin_layout_edit->get_role_selector('addusergroups', true); ?>
			<br/>
			<span class="description">Select user groups that may add new records. If none selected, inherits from parent layout or defaults to Administrator.</span>
		</td>
	</tr>

	<tr class="form-field form-required">
		<th scope="row">
			<label for="editusergroups">
				<?php echo esc_html__('Who may edit records', 'customtables'); ?>
			</label>
		</th>
		<td>
			<?php echo $this->admin_layout_edit->get_role_selector('editusergroups'); ?>
			<br/>
			<span class="description">Select user groups that may edit existing records. If none selected, inherits from parent layout or defaults to Administrator.</span>
		</td>
	</tr>

	<tr class="form-field form-required">
		<th scope="row">
			<label for="publishusergroups">
				<?php echo esc_html__('Who may publish records', 'customtables'); ?>
			</label>
		</th>
		<td>
			<?php echo $this->admin_layout_edit->get_role_selector('publishusergroups'); ?>
			<br/>
			<span class="description">Select user groups that may publish and unpublish records. If none selected, inherits from parent layout or defaults to Administrator.</span>
		</td>
	</tr>

	<tr class="form-field form-required">
		<th scope="row">
			<label for="deleteusergroups">
				<?php echo esc_html__('Who may delete records', 'customtables'); ?>
			</label>
		</th>
		<td>
			<?php echo $this->admin_layout_edit->get_role_selector('deleteusergroups'); ?>
			<br/>
			<span class="description">Select user groups that may delete records. If none selected, inherits from parent layout or defaults to Administrator.</span>
		</td>
	</tr>

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
</table>