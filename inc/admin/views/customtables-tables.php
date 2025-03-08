<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;

$errors = common::getTransientMessages('customtables_error_message');
if (isset($this->admin_table_list->errors) && is_wp_error($this->admin_table_list->errors)) {
	foreach ($this->admin_table_list->errors->get_error_messages() as $error)
		$errors []= $error;
}
$messages = common::getTransientMessages('customtables_success_message');

$page = common::inputGetCmd('page');

$allowed_html = array(
	'a' => array(
		'href' => array(),
		'title' => array(),
		'download' => array(),
		'target' => array()
	)
);

$messages = get_transient('customtables_success_message');
delete_transient('customtables_success_message');

$errors = get_transient('customtables_error_message');
delete_transient('customtables_error_message');

?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Custom Tables - Tables', 'customtables'); ?></h1>

    <a href="admin.php?page=customtables-tables-edit&table=0"
       class="page-title-action"><?php esc_html_e('Add New', 'customtables'); ?></a>

	<?php common::showTransient($errors, $messages); ?>

    <hr class="wp-header-end">

    <?php if (count($this->admin_table_list->IntegrityChecksResult) > 0): ?>
        <ol>
            <li><?php echo wp_kses_post(implode('</li><li>', $this->admin_table_list->IntegrityChecksResult)); ?></li>
        </ol>
        <hr/>
    <?php endif; ?>

    <div id="customtables">
        <div id="customtables-post-body">
            <form id="customtables-admin-table-list-form" method="post">
                <input type="hidden" name="page" value="<?php echo esc_html($page); ?>"/>
                <?php
                $this->admin_table_list->search_box(esc_html__('Find', 'customtables'), 'nds-table-find');
                $this->admin_table_list->views();
                $this->admin_table_list->display();
                ?>
            </form>
        </div>
    </div>
</div>

<p><a href="https://ct4.us/contact-us/"
      target="_blank"><?php echo esc_html__('Need help? Connect with us.', 'customtables'); ?></a></p>

