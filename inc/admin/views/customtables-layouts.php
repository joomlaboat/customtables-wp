<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;
use CustomTables\IntegrityChecks;

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

	<?php if (count($this->admin_layout_list->IntegrityChecksResult) > 0): ?>
        <ol>
            <li><?php echo wp_kses(implode('</li><li>', $this->admin_layout_list->IntegrityChecksResult), $allowed_html); ?></li>
        </ol>
        <hr/>
	<?php endif; ?>

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