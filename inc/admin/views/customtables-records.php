<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Check if 'page' is set in $_REQUEST
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';

?>
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php
        if (isset($this->admin_record_list->ct->Table) and $this->admin_record_list->ct->Table->tablename !== null) {
            _e('Custom Tables - Table', $this->plugin_text_domain);
            echo ' "' . $this->admin_record_list->ct->Table->tabletitle . '" - ';
            _e('Records', $this->plugin_text_domain);
        } else {
            _e('Custom Tables - Records', $this->plugin_text_domain);
            echo '<div class="error"><p>' . __('Table not selected or not found.', $this->plugin_text_domain) . '</p></div>';
        }
        ?></h1>

    <?php
    if (isset($this->admin_record_list->ct->Table) and $this->admin_record_list->ct->Table->tablename !== null) {
        echo '<a href="admin.php?page=customtables-records-edit&table='.$this->admin_record_list->tableId.'&id=0" class="page-title-action">'
            . __('Add New', $this->plugin_text_domain) . '</a>';
    }
    ?>

    <hr class="wp-header-end">

    <div id="customtables">
        <div id="customtables-post-body">
            <form id="customtables-admin-record-list-form" method="post">
                <input type="hidden" name="page" value="<?php echo $page; ?>"/>
                <?php
                $this->admin_record_list->search_box(__('Find', $this->plugin_text_domain), 'nds-record-find');
                $this->admin_record_list->views();
                $this->admin_record_list->display();
                ?>
            </form>
        </div>
    </div>
</div>