<?php

/**
 * The admin area of the plugin to load the List of Tables
 */

use CustomTables\CT;
use CustomTables\IntegrityChecks;
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Custom Tables - Tables', $this->plugin_text_domain); ?></h1>
    <a href="admin.php?page=customtables-tables-edit"
       class="page-title-action"><?php _e('Add New', $this->plugin_text_domain); ?></a>
    <hr class="wp-header-end">

    <?php
    $ct = new CT;
    $result = IntegrityChecks::check($ct, true, false);
    if (count($result) > 0) {
        echo '<ol><li>' . implode('</li><li>', $result) . '</li></ol>';
        echo '<hr/>';
    }

    ?>

        <div id="customtables">
            <div id="customtables-post-body">
				<form id="customtables-admin-table-list-form" method="get">
					<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
					<?php 
						$this->user_list_table->search_box( __( 'Find', $this->plugin_text_domain ), 'nds-user-find');
                        $this->user_list_table->views();
						$this->user_list_table->display(); 
					?>					
				</form>
            </div>			
        </div>
</div>