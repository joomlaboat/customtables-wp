<?php

namespace CustomTablesWP\Inc\Admin;

use CustomTables\common;
use CustomTables\CT;
use CustomTables\database;
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
class Admin_Record_List extends Libraries\WP_List_Table
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
    //public $helperListOfFields;
    public ?int $tableId;

    protected int $count_all;
    protected int $count_trashed;
    protected int $count_published;
    protected int $count_unpublished;
    protected ?string $current_status;
    protected ?string $firstFieldRealName;

    /**
	 * Call the parent constructor to override the defaults $args
	 *
	 * @param string $plugin_text_domain	Text domain of the plugin.
	 *
	 * @since 1.0.0
	 */
    public function __construct($plugin_text_domain)
    {
        //require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-listoffields.php');
        $this->ct = new CT;
        //$this->helperListOfFields = new \CustomTables\ListOfFields($this->ct);
        $this->plugin_text_domain = $plugin_text_domain;

        $this->count_all = 0;
        $this->count_trashed = 0;
        $this->count_published = 0;

        $this->tableId = common::inputGetInt('table');
        if ($this->tableId) {
            $this->ct->getTable($this->tableId);
            if ($this->ct->Table !== null and $this->ct->Table->published_field_found) {
                $query = 'SELECT COUNT(' . $this->ct->Table->realidfieldname . ') FROM ' . $this->ct->Table->realtablename . ' WHERE ';

                $this->count_all = database::loadColumn($query . 'published!=-2')[0];
                $this->count_trashed = database::loadColumn($query . 'published=-2')[0];
                $this->count_published = database::loadColumn($query . 'published=1')[0];
            }
        }

        $this->count_unpublished = $this->count_all - $this->count_published;
        $this->current_status = common::inputGetCmd('status');

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

        //Get first field real name
        $this->firstFieldRealName = null;
        if (count($this->ct->Table->fields) > 0)
            $this->firstFieldRealName = $this->ct->Table->fields[0]['realfieldname'];
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
    }

    function get_data()
    {
        // Fetch and return your data here
        if ($this->tableId === null or $this->ct->Table == null or $this->ct->Table->tablename === null)
            return [];

        $search = common::inputGetString('s');
        $orderby = common::inputGetCmd('orderby');
        if ($orderby == 'customtables_record_firstfield')
            $orderby = $this->firstFieldRealName;

        $order = common::inputGetCmd('order');

        //TODO: Fix this mess by replacing the state with a text code like 'published','unpublished','everything','any','trash'
        //$showPublished = 0 - show published
        //$showPublished = 1 - show unpublished
        //$showPublished = 2 - show everything
        //$showPublished = -1 - show published and unpublished
        //$showPublished = -2 - show trashed
        $published = match ($this->current_status) {
            'published' => 0,
            'unpublished' => 1,
            'trash' => -2,
            default => -1
        };

        $this->ct->setFilter($search ?? '', $published);
        if ($orderby !== null)
            $this->ct->Ordering->orderby = $orderby . ($order !== null ? ' ' . $order : '');

        //$this->ct->Ordering->parseOrderByParam();
        //$this->ct->applyLimits($limit);

        if (!$this->ct->getRecords()) {
            //$this->ct->app->enqueueMessage(JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_ERROR_TABLE_NOT_FOUND'), 'error');
            die('Table not found');
        }

        $newData = [];
        foreach ($this->ct->Records as $item) {

            if ($item['listing_published'] == -2)
                $label = '<span>' . $item[$this->firstFieldRealName] . '</span>';
            else {
                $label = '<a class="row-title" href="?page=customtables-records-edit&action=edit&table='.$this->tableId.'&id=' . $item[$this->ct->Table->realidfieldname] . '">'
                    . $item[$this->firstFieldRealName] . '</a>';

                if ($this->ct->Table->published_field_found)
                    $label .= (($this->current_status != 'unpublished' and $item['listing_published'] == 0) ? ' — <span class="post-state">Draft</span>' : '');
            }

            $item[$this->firstFieldRealName] = '<strong>' . $label . '</strong>';
            $newData[] = $item;
        }
        return $newData;
    }

    /**
     * Get a list of columns. The format is:
     * 'internal-name' => 'Title'
     *
     * @return array
     * @since 1.0.0
     *
     */
    function get_columns(): array
    {
        $columns = ['cb' => '<input type="checkbox" />'];
        $first = true;

        foreach ($this->ct->Table->fields as $field) {

            if ($field['type'] != 'dummy' and $field['type'] != 'log' and $field['type'] != 'ordering') {
                $id = 'fieldtitle';
                $title = $field[$id];

                if ($this->ct->Languages->Postfix != '')
                    $id .= '_' . $this->ct->Languages->Postfix;

                if (isset($field[$id]))
                    $title = $field[$id];

                if ($first)
                    $columns['customtables_record_firstfield'] = $title;
                else
                    $columns[$this->ct->Env->field_prefix . $field['fieldname']] = $title;

                $first = false;
            }
        }

        $columns['id'] = __('Id', $this->plugin_text_domain);

        return $columns;
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

    protected function get_sortable_columns(): array
    {
        $sortable_columns = [];

        $first = true;
        foreach ($this->ct->Table->fields as $field) {
            if ($field['type'] != 'dummy' and $field['type'] != 'log' and $field['type'] != 'ordering') {
                if ($first)
                    $sortable_columns['customtables_record_firstfield'] = ['customtables_record_firstfield', true];
                else
                    $sortable_columns[$this->ct->Env->field_prefix . $field['fieldname']] = [$this->ct->Env->field_prefix . $field['fieldname'], false];
            }

            $first = false;
        }
        $sortable_columns['customtables_record_id'] = ['customtables_record_id', false];

        return $sortable_columns;
    }

    /**
     * Generates clickable tools in the first column "fieldname" like delete, trash, edit.
     *
     * @param array $item The table row item.
     *
     * @return string The table row with the clickable tools added.
     */

    function column_customtables_record_firstfield(array $item): string
    {
        $url = 'admin.php?page=customtables-records';
        if ($this->current_status !== null) {
            $url .= '&status=' . $this->current_status;
        }

        $actions = [];
        if ($this->current_status === 'trash') {
            $actions['restore'] = sprintf('<a href="' . $url . '&action=restore&table=%s&id=%s&_wpnonce=%s">' . __('Restore', 'customtables') . '</a>',
                $this->tableId,
                $item['id'],
                urlencode(wp_create_nonce('restore_nonce'))
            );

            $actions['delete'] = sprintf('<a href="' . $url . '&action=delete&table=%s&id=%s&_wpnonce=%s">' . __('Delete Permanently', 'customtables') . '</a>',
                $this->tableId,
                $item['id'],
                urlencode(wp_create_nonce('delete_nonce'))
            );
        } else {
            $actions['edit'] = sprintf('<a href="?page=customtables-records-edit&action=edit&table=%s&id=%s">' . __('Edit', 'customtables') . '</a>',
                $this->tableId,
                $item['id']
            );

            $actions['trash'] = sprintf('<a href="' . $url . '&action=trash&table=%s&id=%s&_wpnonce=%s">' . __('Trash', 'customtables') . '</a>',
                $this->tableId,
                $item['id'],
                urlencode(wp_create_nonce('trash_nonce'))
            );
        }

        return sprintf('%1$s %2$s', $item[$this->firstFieldRealName], $this->row_actions($actions));
    }

    /**
     * Text displayed when no records found
     *
     * @return void
     * @since   1.0.0
     *
     */
    public function no_items(): void
    {
        _e('No records found.', $this->plugin_text_domain);
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
        return $item[$column_name];
    }

    /**
     * Get value for checkbox column.
     *
     * The special 'cb' column
     *
     * @param object $item A row's data
     * @return string Text to be placed inside the column <td>.
     */
    function column_cb($item): string
    {
        return sprintf(
            '<input type="checkbox" name="ids[]" value="%s" />',
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

    public function get_views(): array
    {
        $link = 'admin.php?page=customtables-records&table=' . $this->tableId;

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
    public function get_bulk_actions(): array
    {
        /*
         * on hitting apply in bulk actions the url params are set as
         * ?action=action&id=1
         *
         * action and action2 are set based on the triggers above or below the table
         *
         */

        $actions = [];

        if ($this->current_status != 'trash')
            $actions['customtables-records-edit'] = __('Edit', 'customtables');

        if ($this->current_status == '' or $this->current_status == 'all') {
            $actions['customtables-records-publish'] = __('Publish', 'customtables');
            $actions['customtables-records-unpublish'] = __('Draft', 'customtables');
        } elseif ($this->current_status == 'unpublished')
            $actions['customtables-records-publish'] = __('Publish', 'customtables');
        elseif ($this->current_status == 'published')
            $actions['customtables-records-unpublish'] = __('Draft', 'customtables');

        if ($this->current_status != 'trash')
            $actions['customtables-records-trash'] = __('Move to Trash', 'customtables');

        if ($this->current_status == 'trash') {
            $actions['customtables-records-restore'] = __('Restore', 'customtables');
            $actions['customtables-records-delete'] = __('Delete Permanently', 'customtables');
        }
        return $actions;
    }

    /**
     * Process actions triggered by the user
     *
     * @since    1.0.0
     *
     */
    function handle_record_actions()
    {
        /*
         * Note: Field bulk_actions can be identified by checking $_REQUEST['action'] and $_REQUEST['action2']
         *
         * action - is set if checkbox from top-most select-all is set, otherwise returns -1
         * action2 - is set if checkbox the bottom-most select-all checkbox is set, otherwise returns -1
         */

        // check for individual row actions
        $the_table_action = $this->current_action();
        if ($this->ct->Table->published_field_found) {
            if ('restore' === $the_table_action) {
                $nonce = wp_unslash($_REQUEST['_wpnonce']);
                // verify the nonce.
                if (!wp_verify_nonce($nonce, 'restore_nonce')) {
                    $this->invalid_nonce_redirect();
                } else {
                    $recordId = common::inputGetInt('id');
                    database::update($this->ct->Table->realtablename, ['published'=>0], [$this->ct->Table->realidfieldname  =>  $recordId]);
                    //echo '<div id="message" class="updated notice is-dismissible"><p>1 record restored from the Trash.</p></div>';
                    $this->graceful_redirect();
                }
            }

            if ('trash' === $the_table_action) {
                $nonce = wp_unslash($_REQUEST['_wpnonce']);
                // verify the nonce.
                if (!wp_verify_nonce($nonce, 'trash_nonce')) {
                    $this->invalid_nonce_redirect();
                } else {
                    $recordId = common::inputGetInt('id');
                    database::update($this->ct->Table->realtablename, ['published' =>-2], [$this->ct->Table->realidfieldname  =>  $recordId]);
                    //echo '<div id="message" class="updated notice is-dismissible"><p>1 record moved to the Trash.</p></div>';
                    $this->graceful_redirect();
                }
            }
        }

        if ('delete' === $the_table_action) {
            $nonce = wp_unslash($_REQUEST['_wpnonce']);
            // verify the nonce.
            if (!wp_verify_nonce($nonce, 'delete_nonce')) {
                $this->invalid_nonce_redirect();
            } else {
                $recordId = common::inputGetInt('id');
                if ($recordId !== null) {
                    database::setQuery('DELETE FROM '.$this->ct->Table->realtablename.' WHERE '.$this->ct->Table->realidfieldname.'='.database::quote($recordId));
                    //echo '<div id="message" class="updated notice is-dismissible"><p>1 record permanently deleted.</p></div>';
                    $this->graceful_redirect();
                } else {
                    echo '<div id="message" class="updated error is-dismissible"><p>Field not selected.</p></div>';
                }
            }
        }

        // check for record bulk actions

        if ($this->is_table_action('customtables-records-edit'))
            $this->handle_record_actions_edit();

        if ($this->ct->Table->published_field_found) {
            if ($this->is_table_action('customtables-records-publish'))
                $this->handle_record_actions_publish(1);

            if ($this->is_table_action('customtables-records-unpublish') or $this->is_table_action('customtables-records-restore'))
                $this->handle_record_actions_publish(0);

            if ($this->is_table_action('customtables-records-trash'))
                $this->handle_record_actions_publish(-2);
        }

        if ($this->is_table_action('customtables-records-delete'))
            $this->handle_record_actions_delete();
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
     * @param ?string $url
     *
     * @return void
     * @since    1.0.0
     *
     */
    public function graceful_redirect(?string $url = null)
    {
        if ($url === null)
            $url = 'admin.php?page=customtables-records&table=' . $this->tableId;

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

    function handle_record_actions_edit()
    {
        $recordId = (int)(isset($_POST['ids']) ? $_POST['id'][0] : '');

        // Redirect to the edit page with the appropriate parameters
        $this->graceful_redirect('admin.php?page=customtables-records-edit&action=edit&table=' . $this->tableId . '&id=' . $recordId);
    }

    function handle_record_actions_publish(int $state): void
    {
        $nonce = wp_unslash($_REQUEST['_wpnonce']);
        // verify the nonce.
        if (!wp_verify_nonce($nonce, 'bulk-' . $this->_args['plural'])) {
            $this->invalid_nonce_redirect();
        } else {
            $records = ($_POST['ids'] ?? []);
            foreach ($records as $recordId)
                database::update($this->ct->Table->realtablename, ['published' => $state], ['id' => $recordId]);

            if (count($records) > 0)
                $this->graceful_redirect();

            echo '<div id="message" class="updated error is-dismissible"><p>Records not selected.</p></div>';
        }
    }

    function handle_record_actions_delete()
    {
        $records = ($_POST['ids'] ?? []);
        if (count($records) > 0) {
            foreach ($records as $recordId)
                database::setQuery('DELETE FROM '.$this->ct->Table->realtablename.' WHERE '.$this->ct->Table->realidfieldname.'='.database::quote($recordId));

            $this->graceful_redirect();
        }
        echo '<div id="message" class="updated error is-dismissible"><p>Records not selected.</p></div>';
    }
}
