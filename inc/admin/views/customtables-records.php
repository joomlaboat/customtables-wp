<?php
/**
 * Plugin Name:       CustomTables
 * Plugin URI:        https://ct4.us/
 * GitHub:            https://github.com/joomlaboat/customtables-wp
 * Author:            Ivan Komlev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;

$page = common::inputGetCmd('page');

?>
<div class="wrap">
    <h1 class="wp-heading-inline">
		<?php
		if (isset($this->admin_record_list->ct->Table) and $this->admin_record_list->ct->Table->tablename !== null) {
			esc_html_e('Custom Tables - Table', 'customtables');
			echo esc_html(' "' . $this->admin_record_list->ct->Table->tabletitle . '" - ');
			esc_html_e('Records', 'customtables');
		} else {
			esc_html_e('Custom Tables - Records', 'customtables');
			echo '<div class="error"><p>' . esc_html__('Table not selected or not found.', 'customtables') . '</p></div>';
		}
		?></h1>

	<?php
	if (isset($this->admin_record_list->ct->Table) and $this->admin_record_list->ct->Table->tablename !== null) {
		echo '<a href="admin.php?page=customtables-records-edit&table=' . esc_html((int)$this->admin_record_list->tableId) . '&id=0" class="page-title-action">';
		echo esc_html__('Add New', 'customtables');
		echo '</a>';
	}
	?>

    <hr class="wp-header-end">

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