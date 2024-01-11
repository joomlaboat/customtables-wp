<?php
/**
 * Plugin Name:       CustomTables
 * Plugin URI:        https://ct4.us/
 * GitHub:            https://github.com/joomlaboat/customtables-wp
 * Author:            Ivan Komlev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use CustomTables\common;

$page = common::inputGetCmd('page');

?>
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php
        if (isset($this->admin_record_list->ct->Table) and $this->admin_record_list->ct->Table->tablename !== null) {
            _e('Custom Tables - Table', 'customtables');
            echo ' "' . $this->admin_record_list->ct->Table->tabletitle . '" - ';
            _e('Records', 'customtables');
        } else {
            _e('Custom Tables - Records', 'customtables');
            echo '<div class="error"><p>' . __('Table not selected or not found.', 'customtables') . '</p></div>';
        }
        ?></h1>

    <?php
    if (isset($this->admin_record_list->ct->Table) and $this->admin_record_list->ct->Table->tablename !== null) {
        echo '<a href="admin.php?page=customtables-records-edit&table='.$this->admin_record_list->tableId.'&id=0" class="page-title-action">'
            . __('Add New', 'customtables') . '</a>';
    }
    ?>

    <hr class="wp-header-end">

    <div id="customtables">
        <div id="customtables-post-body">
            <form id="customtables-admin-record-list-form" method="post">
                <input type="hidden" name="page" value="<?php echo $page; ?>"/>
                <?php
                $this->admin_record_list->search_box(__('Find', 'customtables'), 'nds-record-find');
                $this->admin_record_list->views();
                $this->admin_record_list->display();
                ?>
            </form>
        </div>
    </div>
</div>