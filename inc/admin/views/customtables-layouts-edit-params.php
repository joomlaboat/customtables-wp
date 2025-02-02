<table class="form-table" role="presentation">
	<tr class="form-field form-required">
		<!-- Layout Name Field -->
		<th scope="row">
			<label for="filter">
				<?php echo esc_html__('Filter', 'customtables'); ?>
			</label>
		</th>
		<td>
			<input name="filter" type="text" id="filter" style="min-width: 150px;"
				   value="<?php echo esc_attr($this->admin_layout_edit->params['filter'] ?? ''); ?>"
				   aria-required="false"
				   autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="1024"/>
			<br/>

			<span class="description">
  <strong>Basic Format:</strong><br>
  Field=value<br><br>

  <strong>Comparison Operators:</strong><br>
  =, &#60;, &#60;=, &#62;, &#62;=, !=, == (exact match)<br><br>

  <strong>Dynamic Values:</strong><br>
  • User data: {{ user.id }}<br>
  • URL parameters: {{ url.getint('param_name') }}<br>
  • Current date: {{ 'now'|date('m/d/Y') }}<br><br>

  <strong>Multiple Conditions:</strong><br>
  Combine with 'and' or 'or'<br><br>

  <strong>Examples:</strong><br>
  • URL parameter: color={{ url.getstring('string') }}<br>
  • Date formats:<br>
    - Full date: {{ 'now'|date('m/d/Y') }}<br>
    - Year only: {{ 'now'|date('Y') }}<br>
    - Month only: {{ 'now'|date('m') }}
</span>
		</td>
	</tr>

	<hr/>

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