<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;

$page = common::inputGetCmd('page');

?>
<div class="wrap">

    <?php echo '<a href="admin.php?page=customtables-tables" class="page-title-action">'
        . esc_html__('&laquo; Tables', 'customtables') . '</a>&nbsp;<br/>';?>

    <h1 class="wp-heading-inline">
        <?php
        if (!empty($this->admin_record_list->ct->Table)) {
            esc_html_e('Custom Tables - Table', 'customtables');
            echo esc_html(' "' . $this->admin_record_list->ct->Table->tabletitle . '" - ');
            esc_html_e('Records', 'customtables');
        } else {
            esc_html_e('Custom Tables - Records', 'customtables');
            echo '<div class="error"><p>' . esc_html__('Table not selected or not found.', 'customtables') . '</p></div>';
        }
        ?>
    </h1>

    <?php
    if (!empty($this->admin_record_list->ct->Table)) {
        $tableId = (int)$this->admin_record_list->tableId;

        $nonce = wp_create_nonce('customtables-records-edit');
        echo '<a href="admin.php?page=customtables-records-edit&table=' . esc_html($tableId) . '&id=0&_wpnonce=' . $nonce . '" class="page-title-action">';
        echo esc_html__('Add New', 'customtables');
        echo '</a>';

        $nonce = wp_create_nonce('customtables-import-records');
        echo '<a href="admin.php?page=customtables-import-records&table=' . esc_html($tableId) . '&_wpnonce=' . $nonce . '" class="page-title-action">';
        echo esc_html__('Import CSV', 'customtables');
        echo '</a>';
    }
    ?>

    <?php

    $message = get_transient('plugin_error_message', 30); // timeout in seconds
    if ($message) {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($message) . '</p></div>';
        delete_transient('plugin_error_message');
    }

    $success_message = get_transient('plugin_success_message', 30); // timeout in seconds
    if (!empty($success_message)) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($success_message) . '</p></div>';
        delete_transient('plugin_success_message');
    }
    ?>

    <hr class="wp-header-end"/>

    <div id="customtables">
        <div id="customtables-post-body">
            <form id="customtables-admin-record-list-form" method="post">
                <input type="hidden" name="page" value="<?php echo esc_html($page); ?>"/>
                <?php
                $this->admin_record_list->search_box(esc_html__('Find', 'customtables'), 'nds-record-find');
                $this->admin_record_list->views();
                $this->admin_record_list->display();
                ?>
            </form>
        </div>
    </div>
</div>

<?php if(!empty($this->admin_record_list->ct->Table)): ?>
<div class="CustomTablesDocumentationTips">
    <h4>Adding Catalog Views and Edit Forms</h4>
    <p>You can use these shortcodes to display table records and add/edit forms:</p>
    <br/>
    <p style="font-weight:bold;">Basic Catalog Views</p>
    <div><pre>[customtables table="<?php echo $this->admin_record_list->ct->Table->tablename; ?>"]</pre> - Displays catalog view using table name</div>
    <div><pre>[customtables table="<?php echo $this->admin_record_list->ct->Table->tableid; ?>"]</pre> - Displays catalog view using table ID</div>
    <div><pre>[customtables table="<?php echo $this->admin_record_list->ct->Table->tablename; ?>" view="catalog"]</pre> - Explicit catalog view</div>
    <p style="font-weight:bold;">Edit Forms</p>
    <div><pre>[customtables table="<?php echo $this->admin_record_list->ct->Table->tablename; ?>" view="edit"]</pre> - Adds a form to create a new record</div>
    <p style="font-weight:bold;">Catalog with Parameters</p>
    <div><pre>[customtables table="<?php echo $this->admin_record_list->ct->Table->tablename; ?>" view="catalog" limit="5"]</pre> - Shows only 5 records</div>
    <p>Note: The limit parameter controls the number of displayed records. Use limit="0" or omit the parameter to show all records.</p>
</div>
<?php endif; ?>

<p><a href="https://ct4.us/contact-us/" target="_blank"><?php echo esc_html__('We are here to help. Contact us for support.', 'customtables'); ?></a></p>

