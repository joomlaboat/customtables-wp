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

?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Custom Tables - Dashboard', 'customtables'); ?></h1>


    <hr style="margin-bottom: 30px; "/>

    <a href="admin.php?page=customtables-tables" style="margin-right:30px;" class="button">

        <img alt="<?php echo esc_html(__('Tables', 'customtables')); ?>"
             src="<?php echo esc_html(home_url()); ?>/wp-content/plugins/customtables/libraries/customtables/media/images/controlpanel/icons/listoftables.png"/>
        <p style="text-align: center"><?php echo esc_html(__('Tables', 'customtables')); ?></p>

    </a>

    <a href="admin.php?page=customtables-layouts" style="margin-right:30px;" class="button">
        <img alt="<?php echo esc_html(__('Layouts', 'customtables')); ?>"
             src="<?php echo esc_html(home_url()) ?>/wp-content/plugins/customtables/libraries/customtables/media/images/controlpanel/icons/listoflayouts.png">
        <p style="text-align: center"><?php echo esc_html(__('Layouts', 'customtables')); ?></p>
        </button>
    </a>


    <a href="admin.php?page=customtables-documentation" class="button">
        <img alt="<?php echo esc_html(__('Documentation', 'customtables')); ?>"
             src="<?php echo esc_html(home_url()); ?>/wp-content/plugins/customtables/libraries/customtables/media/images/controlpanel/icons/documentation.png">
        <p style="text-align: center"><?php echo esc_html(__('Documentation', 'customtables')); ?></p>
        </button>
    </a>
</div>
