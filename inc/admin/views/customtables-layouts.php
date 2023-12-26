<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

use CustomTables\common;
use CustomTables\IntegrityChecks;

$page = absint(common::inputGetInt('page',0));

$result = IntegrityChecks::check($this->admin_layout_list->ct, true, false);

?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Custom Tables - Layouts', $this->plugin_text_domain); ?></h1>
    <a href="admin.php?page=customtables-layouts-edit&layout=0" class="page-title-action"><?php _e('Add New', $this->plugin_text_domain); ?></a>

    <hr class="wp-header-end">

	<?php if (count($result) > 0): ?>
        <ol>
            <li><?php echo implode('</li><li>', $result); ?></li>
        </ol>
        <hr/>
	<?php endif; ?>

    <div id="customtables">
        <div id="customtables-post-body">
            <form id="customtables-admin-layout-list-form" method="post">
                <input type="hidden" name="page" value="<?php echo $page; ?>" />
                <?php
                $this->admin_layout_list->search_box( __( 'Find', $this->plugin_text_domain ), 'nds-layout-find');
                $this->admin_layout_list->views();
                $this->admin_layout_list->display();
                ?>
            </form>
        </div>
    </div>
</div>