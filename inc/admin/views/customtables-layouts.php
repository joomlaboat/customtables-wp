<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;

$errors = common::getTransientMessages('customtables_error_message');
if (isset($this->admin_layout_list->errors) && is_wp_error($this->admin_layout_list->errors)) {
	foreach ($this->admin_layout_list->errors->get_error_messages() as $error)
		$errors []= $error;
}

$messages = common::getTransientMessages('customtables_success_message');
if (count($this->admin_layout_list->IntegrityChecksResult) > 0)
	$messages = array_merge($messages, $this->admin_layout_list->IntegrityChecksResult);

$page = absint(common::inputGetInt('page', 0));

$allowed_html = array(
	'li' => array()
);

?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Custom Tables - Layouts', 'customtables'); ?></h1>
    <a href="admin.php?page=customtables-layouts-edit&layout=0"
       class="page-title-action"><?php esc_html_e('Add New', 'customtables'); ?></a>

    <hr class="wp-header-end">

	<?php common::showTransient($errors, $messages); ?>

    <div id="customtables">
        <div id="customtables-post-body">
            <form id="customtables-admin-layout-list-form" method="post">
                <input type="hidden" name="page" value="<?php echo esc_html($page); ?>"/>
				<?php
				$this->admin_layout_list->search_box(esc_html__('Find', 'customtables'), 'nds-layout-find');
				$this->admin_layout_list->views();
				$this->admin_layout_list->display();
				?>
            </form>
        </div>
    </div>
    <p><a href="https://ct4.us/contact-us/" target="_blank"><?php echo esc_html__('Need assistance? Reach out to us.', 'customtables'); ?></a></p>
</div>