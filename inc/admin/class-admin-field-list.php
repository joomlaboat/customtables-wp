<?php

namespace CustomTablesWP\Inc\Admin;

use CustomTables\common;
use CustomTables\CT;
use CustomTables\database;
use CustomTables\Fields;
use CustomTablesWP\Inc\Libraries;

/**
 * Class for displaying registered WordPress Users
 * in a WordPress-like Admin Table with row actions to
 * perform user meta operations
 *
 *
 * @link       http://nuancedesignstudio.in
 * @since      1.0.0
 *
 * @author     Karan NA Gupta
 */
class Admin_Field_List extends Libraries\WP_List_Table
{
    /**
     * The text domain of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_text_domain The text domain of this plugin.
     */
    public $plugin_text_domain;
    public CT $ct;
    public $helperListOfFields;
    public ?int $tableId;
    public array $fieldTypes;
    protected int $count_all;
    protected int $count_trashed;
    protected int $count_published;
    protected int $count_unpublished;
    protected ?string $current_status;

    /**
	 * Call the parent constructor to override the defaults $args
	 *
	 * @param string $plugin_text_domain	Text domain of the plugin.
	 *
	 * @since 1.0.0
	 */
    public function __construct($plugin_text_domain)
    {
        require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-listoffields.php');
        $this->ct = new CT;
        $this->helperListOfFields = new \CustomTables\ListOfFields($this->ct);
        $this->plugin_text_domain = $plugin_text_domain;

        $this->tableId = get_query_var('table');
        if ($this->tableId) {
            $this->ct->getTable($this->tableId);

            $this->count_all = database::loadColumn('SELECT COUNT(id) FROM #__customtables_fields WHERE tableid='.$this->tableId.' AND published!=-2')[0];
            $this->count_trashed = database::loadColumn('SELECT COUNT(id) FROM #__customtables_fields WHERE tableid='.$this->tableId.' AND published=-2')[0];
            $this->count_published = database::loadColumn('SELECT COUNT(id) FROM #__customtables_fields WHERE tableid='.$this->tableId.' AND published=1')[0];
        } else {
            $this->count_all = 0;
            $this->count_trashed = 0;
            $this->count_published = 0;
        }

        $this->count_unpublished = $this->count_all - $this->count_published;
        $this->current_status = get_query_var('status');

        if ($this->current_status !== null and $this->current_status !== 'all') {
            if ($this->current_status == 'trash' and $this->count_trashed == 0)
                $this->current_status = null;
            if ($this->current_status == 'unpublished' and $this->count_unpublished == 0)
                $this->current_status = null;
            if ($this->current_status == 'published' and $this->count_published == 0)
                $this->current_status = null;
        }
        parent::__construct(array(
            'plural' => 'users',    // Plural value used for labels and the objects being listed.
            'singular' => 'user',        // Singular label for an object being listed, e.g. 'post'.
            'ajax' => false,        // If true, the parent class will call the _js_vars() method in the footer
        ));


        $this->fieldTypes = $this->helperListOfFields->getFieldTypesFromXML();
    }

    /**
     * Prepares the list of items for displaying.
     *
     * Query, filter data, handle sorting, and pagination, and any other data-manipulation required prior to rendering
     *
     * @since   1.0.0
     */
    function prepare_items()
    {
        $data = $this->get_data(); // Fetch your data here

        $columns = $this->get_columns();
        $hidden = array(); // Columns to hide (optional)
        //$sortable = array(); // Columns to make sortable (optional)
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        // Paginate the data
        $per_page = 10; // Number of items per page
        $current_page = $this->get_pagenum(); // Get the current page
        $total_items = count($data); // Total number of items

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));

        // Slice the data to display the correct items for the current page
        $this->items = array_slice($data, (($current_page - 1) * $per_page), $per_page);

        // Set up actions
        $this->process_bulk_action();
    }

    function get_data()
    {
        // Fetch and return your data here
        if ($this->tableId === null or $this->ct->Table == null or $this->ct->Table->tablename === null)
            return [];

        $search = get_query_var('s');
        $orderby = get_query_var('orderby');
        $order = get_query_var('order');

        $published = match ($this->current_status) {
            'published' => 1,
            'unpublished' => 0,
            'trash' => -2,
            default => null
        };

        $query = $this->helperListOfFields->getListQuery($this->tableId, $published, $search, null, $orderby, $order);
        $data = database::loadAssocList($query);
        $newData = [];
        foreach ($data as $item) {
            //$field_exists = ESTables::checkIfTableExists($item['realfieldname']);

            if ($item['published'] == -2)
                $label = '<span>' . $item['fieldname'] . '</span>';
            else
                $label = '<a class="row-title" href="?page=customtables-fields-edit&action=edit&table=' . $this->tableId . '&field=' . $item['id'] . '">' . $item['fieldname'] . '</a>'
                    . (($this->current_status != 'unpublished' and $item['published'] == 0) ? ' — <span class="post-state">Draft</span>' : '');

            $item['fieldname'] = '<strong>' . $label . '</strong>';

            $result = '<ul style="list-style: none !important;margin-left:0;padding-left:0;">';
            $moreThanOneLang = false;
            foreach ($this->ct->Languages->LanguageList as $lang) {
                $fieldTitle = 'fieldtitle';
                $fieldDescription = 'description';

                if ($moreThanOneLang) {
                    $fieldTitle .= '_' . $lang->sef;
                    $fieldDescription .= '_' . $lang->sef;
                }

                if (!array_key_exists($fieldTitle, $item))
                    Fields::addLanguageField('#__customtables_fields', 'fieldtitle', $fieldTitle);

                if (!array_key_exists($fieldTitle, $item))
                    Fields::addLanguageField('#__customtables_fields', 'description', $fieldDescription);

                $result .= '<li>' . (count($this->ct->Languages->LanguageList) > 1 ? $lang->title . ': ' : '') . '<b>' . ($item[$fieldTitle] ?? '') . '</b></li>';
                $moreThanOneLang = true; //More than one language installed
            }

            $result .= '</ul>';
            $item['fieldtitle'] = $result;
            $item['typeparams'] = str_replace('****apos****', "'", str_replace('****quote****', '"', common::escape($item['typeparams'])));
            $item['table'] = str_replace('****apos****', "'", str_replace('****quote****', '"', common::escape($item['tabletitle'])));
            $item['type'] = $this->getFieldTypeLabel($item['type']);
            $newData[] = $item;
        }
        return $newData;
    }

    protected function getFieldTypeLabel($typeName): string
    {
        foreach ($this->fieldTypes as $type)
            if ($type['name'] == $typeName)
                return $type['label'];

        return '<span style="color:red">Unknown Type</span>';
    }

    /**
     * Get a list of columns. The format is:
     * 'internal-name' => 'Title'
     *
     * @return array
     * @since 1.0.0
     *
     */
    function get_columns()
    {
        return array(
            'cb' => '<input type="checkbox" />',
            'fieldname' => __('Field Name', $this->plugin_text_domain),
            'fieldtitle' => __('Field Title', $this->plugin_text_domain),
            'type' => __('Field Type', $this->plugin_text_domain),
            'typeparams' => __('Type Parameters', $this->plugin_text_domain),
            'isrequired' => __('Required', $this->plugin_text_domain),
            'table' => __('Table', $this->plugin_text_domain),
            'id' => __('Id', $this->plugin_text_domain)
        );
    }

    /**
     * Get a list of sortable columns. The format is:
     * 'internal-name' => 'orderby'
     * or
     * 'internal-name' => array( 'orderby', true )
     *
     * The second format will make the initial sorting order be descending
     *
     * @return array
     * @since 1.1.0
     *
     */
    protected function get_sortable_columns()
    {
        $sortable_columns = array(
            'fieldname' => array('fieldname', false),
            'type' => array('type', false),
            'id' => array('id', true)
        );
        return $sortable_columns;
    }

    /**
     * Generates clickable tools in the first column "fieldname" like delete, trash, edit.
     *
     * @param array $item The table row item.
     *
     * @return string The table row with the clickable tools added.
     */
    function column_fieldname($item)
    {
        $url = 'admin.php?page=customtables-fields';
        if ($this->current_status !== null) {
            $url .= '&status=' . $this->current_status;
        }

        $actions = [];
        if ($this->current_status === 'trash') {
            $actions['restore'] = sprintf('<a href="' . $url . '&action=restore&table=%s&field=%s&_wpnonce=%s">' . __('Restore', 'customtables') . '</a>',
                $this->tableId,
                $item['id'],
                urlencode(wp_create_nonce('restore_nonce'))
            );

            $actions['delete'] = sprintf('<a href="' . $url . '&action=delete&table=%s&field=%s&_wpnonce=%s">' . __('Delete Permanently', 'customtables') . '</a>',
                $this->tableId,
                $item['id'],
                urlencode(wp_create_nonce('delete_nonce'))
            );
        } else {
            $actions['edit'] = sprintf('<a href="?page=customtables-fields-edit&action=edit&table=%s&field=%s">' . __('Edit', 'customtables') . '</a>',
                $this->tableId,
                $item['id']
            );

            $actions['trash'] = sprintf('<a href="' . $url . '&action=trash&table=%s&field=%s&_wpnonce=%s">' . __('Trash', 'customtables') . '</a>',
                $this->tableId,
                $item['id'],
                urlencode(wp_create_nonce('trash_nonce'))
            );
        }

        return sprintf('%1$s %2$s', $item['fieldname'], $this->row_actions($actions));
    }

    /**
     * Text displayed when no fields found
     *
     * @return void
     * @since   1.0.0
     *
     */
    public function no_items()
    {
        _e('No fields found.', $this->plugin_text_domain);
    }

    /**
     * Render a column when no column specific method exists.
     *
     * @param array $item
     * @param string $column_name
     *
     * @return mixed
     */
    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'fieldname':
                return $item[$column_name];
            case 'fieldtitle':
                return $item[$column_name];
            case 'type':
                return $item[$column_name];
            case 'typeparams':
                return $item[$column_name];
            case 'isrequired':
                return $item[$column_name];
            case 'table':
                return $item[$column_name];
            case 'id':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

    /**
     * Get value for checkbox column.
     *
     * The special 'cb' column
     *
     * @param object $item A row's data
     * @return string Text to be placed inside the column <td>.
     */
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="field[]" value="%s" />',
            $item['id']
        );
    }

    public function views()
    {
        $views = $this->get_views();

        if (!empty($views)) {
            echo '<ul class="subsubsub">';
            foreach ($views as $view) {
                echo '<li>' . $view . '</li>';
            }
            echo '</ul>';
        }
    }

    public function get_views()
    {
        $link = 'admin.php?page=customtables-fields&table=' . $this->tableId;

        $views = [];

        $views['all'] = '<a href="' . admin_url($link) . '" class="' . (($this->current_status === 'all' or $this->current_status === null) ? 'current' : '') . '">'
            . __('All') . ' <span class="count">(' . $this->count_all . ')</span></a>';

        if ($this->count_published > 0)
            $views['published'] = '<a href="' . admin_url($link . '&status=published') . '" class="' . ($this->current_status === 'published' ? 'current' : '') . '">'
                . __('Published') . ' <span class="count">(' . $this->count_published . ')</span></a>';

        if ($this->count_unpublished > 0)
            $views['unpublished'] = '<a href="' . admin_url($link . '&status=unpublished') . '" class="' . ($this->current_status === 'unpublished' ? 'current' : '') . '">'
                . __('Draft') . ' <span class="count">(' . $this->count_unpublished . ')</span></a>';

        if ($this->count_trashed > 0)
            $views['trash'] = '<a href="' . admin_url($link . '&status=trash') . '" class="' . ($this->current_status === 'trash' ? 'current' : '') . '">' . __('Trash')
                . ' <span class="count">(' . $this->count_trashed . ')</span></a>';

        return $views;
    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     * @since    1.0.0
     *
     */
    public function get_bulk_actions()
    {
        /*
         * on hitting apply in bulk actions the url params are set as
         * ?action=action&field=1
         *
         * action and action2 are set based on the triggers above or below the table
         *
         */

        $actions = [];

        if ($this->current_status != 'trash')
            $actions['customtables-fields-edit'] = __('Edit', 'customtables');

        if ($this->current_status == '' or $this->current_status == 'all') {
            $actions['customtables-fields-publish'] = __('Publish', 'customtables');
            $actions['customtables-fields-unpublish'] = __('Draft', 'customtables');
        } elseif ($this->current_status == 'unpublished')
            $actions['customtables-fields-publish'] = __('Publish', 'customtables');
        elseif ($this->current_status == 'published')
            $actions['customtables-fields-unpublish'] = __('Draft', 'customtables');

        if ($this->current_status != 'trash')
            $actions['customtables-fields-trash'] = __('Move to Trash', 'customtables');

        if ($this->current_status == 'trash') {
            $actions['customtables-fields-restore'] = __('Restore', 'customtables');
            $actions['customtables-fields-delete'] = __('Delete Permanently', 'customtables');
        }
        return $actions;
    }

    /**
     * Process actions triggered by the user
     *
     * @since    1.0.0
     *
     */
    function handle_field_actions()
    {
        /*
         * Note: Field bulk_actions can be identified by checking $_REQUEST['action'] and $_REQUEST['action2']
         *
         * action - is set if checkbox from top-most select-all is set, otherwise returns -1
         * action2 - is set if checkbox the bottom-most select-all checkbox is set, otherwise returns -1
         */

        // check for individual row actions
        $the_table_action = $this->current_action();

        if ('restore' === $the_table_action) {
            $nonce = wp_unslash($_REQUEST['_wpnonce']);
            // verify the nonce.
            if (!wp_verify_nonce($nonce, 'restore_nonce')) {
                $this->invalid_nonce_redirect();
            } else {
                $fieldId = get_query_var('field');
                database::update('#__customtables_fields', ['published'=>0], ['id'=> $fieldId]);
                //echo '<div id="message" class="updated notice is-dismissible"><p>1 field restored from the Trash.</p></div>';
                $this->graceful_redirect();
            }
        }

        if ('trash' === $the_table_action) {
            $nonce = wp_unslash($_REQUEST['_wpnonce']);
            // verify the nonce.
            if (!wp_verify_nonce($nonce, 'trash_nonce')) {
                $this->invalid_nonce_redirect();
            } else {
                $fieldId = get_query_var('field');
                database::update('#__customtables_fields', ['published'=>-2], ['id'=> $fieldId]);
                //echo '<div id="message" class="updated notice is-dismissible"><p>1 field moved to the Trash.</p></div>';
                $this->graceful_redirect();
            }
        }

        if ('delete' === $the_table_action) {
            $nonce = wp_unslash($_REQUEST['_wpnonce']);
            // verify the nonce.
            if (!wp_verify_nonce($nonce, 'delete_nonce')) {
                $this->invalid_nonce_redirect();
            } else {
                $fieldId = get_query_var('field');
                if ($fieldId !== null) {
                    Fields::deleteField_byID($this->ct, $fieldId);
                    //echo '<div id="message" class="updated notice is-dismissible"><p>1 field permanently deleted.</p></div>';
                    $this->graceful_redirect();
                } else {
                    echo '<div id="message" class="updated error is-dismissible"><p>Field not selected.</p></div>';
                }
            }
        }

        // check for field bulk actions

        if ($this->is_table_action('customtables-fields-edit'))
            $this->handle_field_actions_edit();

        if ($this->is_table_action('customtables-fields-publish'))
            $this->handle_field_actions_publish(1);

        if ($this->is_table_action('customtables-fields-unpublish') or $this->is_table_action('customtables-fields-restore'))
            $this->handle_field_actions_publish(0);

        if ($this->is_table_action('customtables-fields-trash'))
            $this->handle_field_actions_publish(-2);

        if ($this->is_table_action('customtables-fields-delete'))
            $this->handle_field_actions_delete();
    }

    /**
     * Process tasks triggered by the user
     *
     * @since    1.0.0
     *
     */
    function handle_field_tasks()
    {

    }

    /**
     * Die when the nonce check fails.
     *
     * @return void
     * @since    1.0.0
     *
     */
    public function invalid_nonce_redirect()
    {
        wp_die(__('Invalid Nonce', $this->plugin_text_domain),
            __('Error', $this->plugin_text_domain),
            array(
                'response' => 403,
                'back_link' => esc_url(add_query_arg(array('page' => wp_unslash($_REQUEST['page'])), admin_url('users.php'))),
            )
        );
    }

    /**
     * Stop execution, redirect and exit
     *
     * @param string $url
     *
     * @return void
     * @since    1.0.0
     *
     */
    public function graceful_redirect(?string $url = null)
    {
        if ($url === null)
            $url = 'admin.php?page=customtables-fields&table=' . $this->tableId;

        if ($this->current_status != null)
            $url .= '&status=' . $this->current_status;

        ob_start(); // Start output buffering
        ob_end_clean(); // Discard the output buffer
        wp_redirect(admin_url($url));
        exit;
    }

    function is_table_action($action): bool
    {
        if (isset($_REQUEST['action']) && ($_REQUEST['action'] === $action) || (isset($_REQUEST['action2']) && $_REQUEST['action2'] === $action))
            return true;

        return false;
    }

    function handle_field_actions_edit()
    {
        // Assuming $_POST['field'] contains the selected items
        $field_id = (int)(isset($_POST['field']) ? $_POST['field'][0] : '');

        // Redirect to the edit page with the appropriate parameters
        $this->graceful_redirect('admin.php?page=customtables-fields-edit&action=edit&table=' . $this->tableId . '&field=' . $field_id);
    }

    function handle_field_actions_publish(int $state): void
    {
        $nonce = wp_unslash($_REQUEST['_wpnonce']);
        // verify the nonce.
        if (!wp_verify_nonce($nonce, 'bulk-' . $this->_args['plural'])) {
            $this->invalid_nonce_redirect();
        } else {
            $fields = (isset($_POST['field']) ? $_POST['field'] : []);
            foreach ($fields as $field)
                database::update('#__customtables_fields', ['published' => $state], ['id' => (int)$field]);

            if (count($fields) > 0)
                $this->graceful_redirect();

            echo '<div id="message" class="updated error is-dismissible"><p>Fields not selected.</p></div>';
        }
    }

    function handle_field_actions_delete()
    {
        $fields = (isset($_POST['field']) ? $_POST['field'] : []);
        if (count($fields) > 0) {
            foreach ($fields as $fieldId)
                Fields::deleteField_byID($this->ct, $fieldId);

            $this->graceful_redirect();
        }
        echo '<div id="message" class="updated error is-dismissible"><p>Fields not selected.</p></div>';
    }
}
