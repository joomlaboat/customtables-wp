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
			<br/><span class="description">Field to search in = (or &#60; or &#60;= or &#62; or &#62;= or != and == 'exact match') value (or Twig tag as {{ user.id }} for example ) to have more then one condition use 'and', 'or' to get a value from the URL query parameter use {{ url.getint('param_name') }} tag or equivalent. Example 'color={{ url.getstring('string') }}' this will read value 'color' from the url query. To get the current date use {{ 'now'|date('m/d/Y') }} or {{ 'now'|date('Y') }} for the year or {{ 'now'|date('m') }} for the month. Also you can format the date using MySql date_format() format specifiers, example 1: {now:%m}. Example 2: 'birthdate:%m%d' to get the month and the day of the field value.</span>
		</td>
	</tr>

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

	<?php /*
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
					null
			); ?>
			<br/>
			<span class="description">Sets the default publish status for newly created records. If none selected, inherits from parent layout or defaults to Published.</span>
		</td>
	</tr> */ ?>
</table>