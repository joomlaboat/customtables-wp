<?php

/**
 * The admin area of the plugin to load the User List Table
 */

// Include the necessary file
use CustomTables\common;
use CustomTables\CT;
use CustomTables\database;
use CustomTables\Fields;
use CustomTables\IntegrityChecks;
use CustomTables\listOfTables;

if (!class_exists('CustomTableList')) {
    require_once 'customtables-tables-list.php';
}

$ct = new CT;
$list_table = new CustomTableList();

switch ($list_table->current_action()) {
    case 'restore':
        $tableId = common::inputGetInt('table');
        database::updateSets('#__customtables_tables', ['published=0'], ['id=' . $tableId]);
        echo '<div id="message" class="updated notice is-dismissible"><p>1 table restored from the Trash.</p></div>';
        break;
    case 'delete':
        $tableId = common::inputGetInt('table');
        require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-listoftables.php');
        $helperListOfLayout = new CustomTables\listOfTables($ct);
        $helperListOfLayout->deleteTable($tableId);
        break;
    case 'trash':
        //check_admin_referer('trash', '_wpnonce');
        $tableId = common::inputGetInt('table');
        database::updateSets('#__customtables_tables', ['published=-2'], ['id=' . $tableId]);
        echo '<div id="message" class="updated notice is-dismissible"><p>1 table moved to the Trash.</p></div>';
        break;
    default:
        break;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Custom Tables - Tables', $this->plugin_text_domain); ?></h1>
    <a href="admin.php?page=customtables-tables-edit"
       class="page-title-action"><?php _e('Add New', $this->plugin_text_domain); ?></a>
    <hr class="wp-header-end">

    <?php
    $result = IntegrityChecks::check($ct, true, false);
    if (count($result) > 0)
        echo '<ol><li>' . implode('</li><li>', $result) . '</li></ol>';


    ?>
    <!-- Add form tag here -->
    <form method="post">

        <?php
        // Display the table
        $list_table->display_table();
        wp_nonce_field('bulk-action-nonce', '_wpnonce');
        ?>
    </form>
</div>

