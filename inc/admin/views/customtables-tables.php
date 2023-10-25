<?php
use CustomTables\IntegrityChecks;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Check if 'page' is set in $_REQUEST
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';

$result = IntegrityChecks::check($this->admin_table_list->ct, true, false);

?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Custom Tables - Tables', $this->plugin_text_domain); ?></h1>
    <a href="admin.php?page=customtables-tables-edit&table=0" class="page-title-action"><?php _e('Add New', $this->plugin_text_domain); ?></a>
    <hr class="wp-header-end">

    <?php if (count($result) > 0): ?>
        <ol>
            <li><?php echo implode('</li><li>', $result); ?></li>
        </ol>
        <hr/>
    <?php endif; ?>

    <div id="customtables">
        <div id="customtables-post-body">
            <form id="customtables-admin-table-list-form" method="post">
                <input type="hidden" name="page" value="<?php echo $page; ?>" />
                <?php
                $this->admin_table_list->search_box( __( 'Find', $this->plugin_text_domain ), 'nds-table-find');
                $this->admin_table_list->views();
                $this->admin_table_list->display();
                ?>
            </form>
        </div>
    </div>
</div>