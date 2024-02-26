<?php
/**
 * Plugin Name:       CustomTables
 * Plugin URI:        https://ct4.us/
 * GitHub:            https://github.com/joomlaboat/customtables-wp
 * Author:            Ivan Komlev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

use CustomTables\common;
use CustomTables\Integrity\IntegrityFields;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$page = common::inputGetCmd('page');

?>
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php
        if (isset($this->admin_field_list->ct->Table) and $this->admin_field_list->ct->Table->tablename !== null) {
            esc_html_e('Custom Tables - Table', 'customtables');
            echo ' "' . esc_html($this->admin_field_list->ct->Table->tabletitle) . '" - ';
            esc_html_e('Fields', 'customtables');
        } else {
            esc_html_e('Custom Tables - Fields', 'customtables');
            echo '<div class="error"><p>' . esc_html(__('Table not selected or not found.', 'customtables')) . '</p></div>';
        }
        ?></h1>

    <?php
    if (isset($this->admin_field_list->ct->Table) and $this->admin_field_list->ct->Table->tablename !== null) {
        echo '<a href="admin.php?page=customtables-fields-edit&table='.esc_html($this->admin_field_list->tableId).'&field=0" class="page-title-action">'
            . esc_html(__('Add New', 'customtables')) . '</a>';
    }
    ?>

    <hr class="wp-header-end">

    <?php
    if ($this->admin_field_list->tableId != 0) {
        $link = 'admin.php?page=customtables-fields&table=' . $this->admin_field_list->tableId;
        $result_clean = IntegrityFields::checkFields($this->admin_field_list->ct, $link);

        if($result_clean !== '')
            echo '<div id="message" class="updated notice is-dismissible">' . wp_kses_post($result_clean) . '</div>';
    }
    ?>


    <div id="customtables">
        <div id="customtables-post-body">
            <form id="customtables-admin-field-list-form" method="post">
                <input type="hidden" name="page" value="<?php echo esc_html($page); ?>"/>
                <?php
                $this->admin_field_list->search_box(__('Find', 'customtables'), 'nds-field-find');
                $this->admin_field_list->views();
                $this->admin_field_list->display();
                ?>
            </form>
        </div>
    </div>
</div>