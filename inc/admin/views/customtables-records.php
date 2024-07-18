<?php
/**
 * Plugin Name:       CustomTables
 * Plugin URI:        https://ct4.us/
 * GitHub:            https://github.com/joomlaboat/customtables-wp
 * Author:            Ivan Komlev
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;

$page = common::inputGetCmd('page');

?>
<div class="wrap">
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
<p><a href="https://ct4.us/contact-us/" target="_blank"><?php echo esc_html__('We are here to help. Contact us for support.', 'customtables'); ?></a></p>

