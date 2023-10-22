<?php

/**
 * The admin area of the plugin to load the User List Table
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Custom Tables - Dashboard', $this->plugin_text_domain); ?></h1>


    <hr style="margin-bottom: 30px; "/>

    <a href="admin.php?page=customtables-tables" style="margin-right:30px;" class="button">

        <img alt="<?php echo __('Tables', $this->plugin_text_domain); ?>"
             src="<?php echo home_url() ?>/wp-content/plugins/customtables/libraries/customtables/media/images/controlpanel/icons/listoftables.png"/>
        <p style="text-align: center"><?php echo __('Tables', $this->plugin_text_domain); ?></p>

    </a>

    <a href="admin.php?page=customtables-layouts" style="margin-right:30px;" class="button">
        <img alt="<?php echo __('Layouts', $this->plugin_text_domain); ?>"
             src="<?php echo home_url() ?>/wp-content/plugins/customtables/libraries/customtables/media/images/controlpanel/icons/listoflayouts.png">
        <p style="text-align: center"><?php echo __('Layouts', $this->plugin_text_domain); ?></p>
        </button>
    </a>


    <a href="admin.php?page=customtables-documentation" class="button">
        <img alt="<?php echo __('Documentation', $this->plugin_text_domain); ?>"
             src="<?php echo home_url() ?>/wp-content/plugins/customtables/libraries/customtables/media/images/controlpanel/icons/documentation.png">
        <p style="text-align: center"><?php echo __('Documentation', $this->plugin_text_domain); ?></p>
        </button>
    </a>
</div>
