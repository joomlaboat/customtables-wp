<?php

namespace CustomTablesWP\Inc\Admin;

use CustomTables\common;
use CustomTables\CT;
use CustomTables\Layouts;
use CustomTables\ListOfLayouts;

class Admin_Layout_Edit
{
    /**
     * The text domain of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_text_domain The text domain of this plugin.
     */
    public string $plugin_text_domain;
    public CT $ct;
    public ListOfLayouts $helperListOfLayouts;
    public ?int $layoutId;
    public ?array $layoutRow;

    /**
	 *
	 *
	 * @param string $plugin_text_domain	Text domain of the plugin.
	 *
	 * @since 1.0.0
	 */
    public function __construct($plugin_text_domain)
    {
        require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-listoflayouts.php');
        $this->ct = new CT;
        $this->helperListOfLayouts = new ListOfLayouts($this->ct);
        $this->plugin_text_domain = $plugin_text_domain;

	    $this->layoutId = common::inputGetInt('layout');

        if($this->layoutId === 0)
            $this->layoutId = null;

        if($this->layoutId !== null) {
            $layout = new Layouts($this->ct);
            $this->layoutRow = $layout->getLayoutRowById($this->layoutId);
        }
        else
        {
            $this->layoutRow = null;
        }

        add_action('admin_enqueue_scripts', array($this, 'codemirror_enqueue_scripts'));
    }

    function codemirror_enqueue_scripts($hook) {

        $cm_settings1['codeEditor_layoutcode'] = wp_enqueue_code_editor(array('mode'=>'text/html'));
        wp_localize_script('jquery', 'cm_settings_layoutcode', $cm_settings1);
        $cm_settings2['codeEditor_layoutmobile'] = wp_enqueue_code_editor(array('mode'=>'text/html'));
        wp_localize_script('jquery', 'cm_settings_layoutmobile', $cm_settings2);
        $cm_settings3['codeEditor_layoutcss'] = wp_enqueue_code_editor(array('mode'=>'css'));
        wp_localize_script('jquery', 'cm_settings_layoutcss', $cm_settings3);
        $cm_settings4['codeEditor_layoutjs'] = wp_enqueue_code_editor(array('mode'=>'javascript'));
        wp_localize_script('jquery', 'cm_settings_layoutjs', $cm_settings4);

        wp_enqueue_script('wp-theme-plugin-editor');
        wp_enqueue_style('wp-codemirror');
    }

    function handle_layout_actions(): void
    {
	    $action = common::inputPostCmd('action','');
        if('createlayout' === $action || 'savelayout' === $action) {
            $this->helperListOfLayouts->save($this->layoutId);
            $url = 'admin.php?page=customtables-layouts';
            ob_start(); // Start output buffering
            ob_end_clean(); // Discard the output buffer
            wp_redirect(admin_url($url));
            exit;
        }
    }
}