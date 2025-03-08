<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;
use CustomTables\CTMiscHelper;

$errors = common::getTransientMessages('customtables_error_message');
$messages = common::getTransientMessages('customtables_success_message');

$max_file_size = CTMiscHelper::file_upload_max_size();

?>

<div class="wrap">
    <h1 class="wp-heading-inline">Import Tables</h1>

	<?php common::showTransient($errors, $messages); ?>

    <p class="install-help">This function allows for the importation of table structures from .txt files encoded in JSON
        format.</p>
    <form method="post" action="" id="esFileUploaderForm_Tables" enctype="multipart/form-data">
		<?php wp_nonce_field('import-table'); ?>

        <ul style="list-style: none;">
			<li><input type="checkbox" id="importfields" name="importfields" value="1" checked="checked"/> <label for="importfields">Import Table Fields</label></li>
            <li><input type="checkbox" id="importlayouts" name="importlayouts" value="1" checked="checked"/> <label for="importlayouts">Import Layouts</label></li>
            <!--<li><input type="checkbox" name="importmenu" value="1" checked="checked" /> Import Menu</li>-->
        </ul>
        <p>
			<?php
			echo esc_html__('Maximum allowed file size', 'customtables');
			echo ': ';
			echo esc_html(CTMiscHelper::formatSizeUnits($max_file_size));
			?>
        </p>

        <input type="file" name="filetosubmit" accept=".txt"
               onchange="document.getElementById('upload-file').disabled=false"/>
        <input type="submit" id="upload-file" name="upload_file" class="button" value="Upload File"
               disabled=""/>
        <input type="hidden" name="action" value="import"/>
    </form>
    <p><a href="https://ct4.us/contact-us/" target="_blank"><?php echo esc_html__('Contact us for any support inquiries.', 'customtables'); ?></a></p>
</div>

