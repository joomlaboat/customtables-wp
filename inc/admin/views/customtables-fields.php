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
        if (isset($this->admin_field_list->ct->Table) and $this->admin_field_list->ct->Table->tablename !== null) {
            _e('Custom Tables - Table', $this->plugin_text_domain);
            echo ' "' . $this->admin_field_list->ct->Table->tabletitle . '" - ';
            _e('Fields', $this->plugin_text_domain);
        } else {
            _e('Custom Tables - Fields', $this->plugin_text_domain);
            echo '<div class="error"><p>' . __('Table not selected or not found.', $this->plugin_text_domain) . '</p></div>';
        }
        ?></h1>

    <?php
    if (isset($this->admin_field_list->ct->Table) and $this->admin_field_list->ct->Table->tablename !== null) {
        echo '<a href="admin.php?page=customtables-fields-edit&table='.$this->admin_field_list->tableId.'" class="page-title-action">'
            . __('Add New', $this->plugin_text_domain) . '</a>';
    }
    ?>

    <hr class="wp-header-end">

    <div id="customtables">
        <div id="customtables-post-body">
            <form id="customtables-admin-field-list-form" method="post">
                <input type="hidden" name="page" value="<?php echo $page; ?>"/>
                <?php
                $this->admin_field_list->search_box(__('Find', $this->plugin_text_domain), 'nds-field-find');
                $this->admin_field_list->views();
                $this->admin_field_list->display();
                ?>
            </form>
        </div>
    </div>
</div>