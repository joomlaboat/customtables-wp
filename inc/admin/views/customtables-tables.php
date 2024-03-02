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
use CustomTables\IntegrityChecks;

$page = common::inputGetCmd('page');
$result = IntegrityChecks::check($this->admin_table_list->ct, true, false);
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Custom Tables - Tables', 'customtables'); ?></h1>
    <a href="admin.php?page=customtables-tables-edit&table=0" class="page-title-action"><?php esc_html_e('Add New', 'customtables'); ?></a>
    <hr class="wp-header-end">

    <?php if (count($result) > 0): ?>
        <ol>
            <li><?php echo wp_kses_post(implode('</li><li>', $result)); ?></li>
        </ol>
        <hr/>
    <?php endif; ?>

    <div id="customtables">
        <div id="customtables-post-body">
            <form id="customtables-admin-table-list-form" method="post">
                <input type="hidden" name="page" value="<?php echo esc_html($page); ?>" />
                <?php
                $this->admin_table_list->search_box( esc_html__( 'Find', 'customtables' ), 'nds-table-find');
                $this->admin_table_list->views();
                $this->admin_table_list->display();
                ?>
            </form>
        </div>
    </div>
</div>