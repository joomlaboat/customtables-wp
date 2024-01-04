<?php

namespace CustomTablesWP\Inc\Admin;

use CustomTables\common;
use CustomTables\CT;
use CustomTables\database;
use CustomTables\Fields;
use CustomTables\MySQLWhereClause;
use CustomTablesWP\Inc\Libraries;
use CustomTables\ListOfTables;
use ESTables;

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
class Admin_Table_List extends Libraries\WP_List_Table
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
    public ListOfTables $helperListOfTables;

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
        require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-listoftables.php');
        $this->ct = new CT;
        $this->helperListOfTables = new ListOfTables($this->ct);
        $this->plugin_text_domain = $plugin_text_domain;

	    $whereClause = new MySQLWhereClause();
	    $whereClause->addCondition('published', -2,'!=');
	    $this->count_all = database::loadColumn('#__customtables_tables',['COUNT(id) AS c'], $whereClause)[0] ?? 0;

	    $whereClause = new MySQLWhereClause();
	    $whereClause->addCondition('published', -2);
	    $this->count_trashed = database::loadColumn('#__customtables_tables',['COUNT(id) AS c'], $whereClause)[0] ?? 0;

	    $whereClause = new MySQLWhereClause();
	    $whereClause->addCondition('published', 1);
	    $this->count_published = database::loadColumn('#__customtables_tables',['COUNT(id) AS c'], $whereClause)[0] ?? 0;

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

    function get_data(): array
    {
        // Fetch and return your data here

	    $search = common::inputGetString('s');
	    $orderby = common::inputGetCmd('orderby');
	    $order = common::inputGetCmd('order');

        $published = match ($this->current_status) {
            'published' => 1,
            'unpublished' => 0,
            'trash' => -2,
            default => null
        };

	    $data = [];

        try {
	        $data = $this->helperListOfTables->getListQuery($published, $search, null, $orderby, $order);
        }catch(\Exception $exception)
        {
			return [];
        }

        $newData = [];
        foreach ($data as $item) {
            $table_exists = ESTables::checkIfTableExists($item['realtablename']);

            if ($item['published'] == -2)
                $label = '<span>' . $item['tablename'] . '</span>';
            else
                $label = '<a class="row-title" href="?page=customtables-tables-edit&action=edit&table=' . $item['id'] . '">' . $item['tablename'] . '</a>'
                    . (($this->current_status != 'unpublished' and $item['published'] == 0) ? ' — <span class="post-state">Draft</span>' : '');

            $item['tablename'] = '<strong>' . $label . '</strong>';

            $result = '<ul style="list-style: none !important;margin-left:0;padding-left:0;">';
            $moreThanOneLang = false;
            foreach ($this->ct->Languages->LanguageList as $lang) {
                $tableTitle = 'tabletitle';
                $tableDescription = 'description';

                if ($moreThanOneLang) {
                    $tableTitle .= '_' . $lang->sef;
                    $tableDescription .= '_' . $lang->sef;

                    if (!array_key_exists($tableTitle, $item)) {
                        Fields::addLanguageField('#__customtables_tables', 'tabletitle', $tableTitle);
                    }

                    if (!array_key_exists($tableTitle, $item)) {
                        Fields::addLanguageField('#__customtables_tables', 'description', $tableDescription);
                    }
                }
                $result .= '<li>' . (count($this->ct->Languages->LanguageList) > 1 ? $lang->title . ': ' : '') . '<b>' . ($item[$tableTitle] ?? '') . '</b></li>';
                $moreThanOneLang = true; //More than one language installed
            }

            $result .= '</ul>';
            $item['tabletitle'] = $result;


            $item['fieldcount'] = '<a class="button action" aria-describedby="tip-tablerecords' . $item['id'] . '" href="'
                . 'admin.php?page=customtables-fields&table=' . $item['id'] . '">'
                . $item['fieldcount']
                . ' ' . __('Fields', $this->plugin_text_domain) . '</a>';


            if (!$table_exists)
                $item['recordcount'] = __('No Table', $this->plugin_text_domain);
            elseif (($item['customtablename'] !== null and $item['customtablename'] != '') and ($item['customidfield'] === null or $item['customidfield'] == ''))
                $item['recordcount'] = __('No Primary Key', $this->plugin_text_domain);
            else {
                $item['recordcount'] = '<a class="button action" aria-describedby="tip-tablerecords' . $item['id'] . '" href="'
                    . 'admin.php?page=customtables-records&table=' . $item['id'] . '">'
                    . listOfTables::getNumberOfRecords($item['realtablename'], $item['realidfieldname'])
                    . ' ' . __('Records', $this->plugin_text_domain) . '</a>';
            }

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
    function get_columns()
    {
        return array(
            'cb' => '<input type="checkbox" />',
            'tablename' => __('Table Name', $this->plugin_text_domain),
            'tabletitle' => __('Table Title', $this->plugin_text_domain),
            'fieldcount' => __('Fields', $this->plugin_text_domain),
            'recordcount' => __('Records', $this->plugin_text_domain),
            //'published' => __('Status', $this->plugin_text_domain),
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
        return array(
            'tablename' => array('tablename', false),
            //'status' => array('status', false),
            'id' => array('id', true)
        );
    }

    function column_tablename($item): string
    {
        $actions = [];

        $url = 'admin.php?page=customtables-tables';
        if ($this->current_status != null)
            $url .= '&status=' . $this->current_status;

        if ($this->current_status == 'trash') {
            $actions['restore'] = sprintf('<a href="' . $url . '&action=restore&table=%s&_wpnonce=%s">' . __('Restore', 'customtables') . '</a>',
                $item['id'], urlencode(wp_create_nonce('restore_nonce')));

            $actions['delete'] = sprintf('<a href="' . $url . '&action=delete&table=%s&_wpnonce=%s">' . __('Delete Permanently', 'customtables') . '</a>',
                $item['id'], urlencode(wp_create_nonce('delete_nonce')));
        } else {
            $actions['edit'] = sprintf('<a href="?page=customtables-tables-edit&action=edit&table=%s">' . __('Edit', 'customtables') . '</a>', $item['id']);
            $actions['trash'] = sprintf('<a href="' . $url . '&action=trash&table=%s&_wpnonce=%s">' . __('Trash', 'customtables') . '</a>',
                $item['id'], urlencode(wp_create_nonce('trash_nonce')));
        }
        return sprintf('%1$s %2$s', $item['tablename'], $this->row_actions($actions));
    }

    /**
     * Text displayed when no tables found
     *
     * @return void
     * @since   1.0.0
     *
     */
    public function no_items(): void
    {
        _e('No tables found.', $this->plugin_text_domain);
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
        return match ($column_name) {
            'tablename', 'tabletitle', 'fieldcount', 'recordcount', 'published', 'id' => $item[$column_name],
            default => print_r($item, true),
        };
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
            '<input type="checkbox" name="table[]" value="%s" />',
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
        $link = 'admin.php?page=customtables-tables';

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
         * ?action=action&table=1
         *
         * action and action2 are set based on the triggers above or below the table
         *
         */

        $actions = [];

        if ($this->current_status != 'trash')
            $actions['customtables-tables-edit'] = __('Edit', 'customtables');

        if ($this->current_status == '' or $this->current_status == 'all') {
            $actions['customtables-tables-publish'] = __('Publish', 'customtables');
            $actions['customtables-tables-unpublish'] = __('Draft', 'customtables');
        } elseif ($this->current_status == 'unpublished')
            $actions['customtables-tables-publish'] = __('Publish', 'customtables');
        elseif ($this->current_status == 'published')
            $actions['customtables-tables-unpublish'] = __('Draft', 'customtables');

        if ($this->current_status != 'trash')
            $actions['customtables-tables-trash'] = __('Move to Trash', 'customtables');

        if ($this->current_status == 'trash') {
            $actions['customtables-tables-restore'] = __('Restore', 'customtables');
            $actions['customtables-tables-delete'] = __('Delete Permanently', 'customtables');
        }
        return $actions;
    }

    /**
     * Process actions triggered by the user
     *
     * @since    1.0.0
     *
     */
    function handle_table_actions()
    {

        /*
         * Note: Table bulk_actions can be identified by checking $REQUEST['action'] and $REQUEST['action2']
         *
         * action - is set if checkbox from top-most select-all is set, otherwise returns -1
         * action2 - is set if checkbox the bottom-most select-all checkbox is set, otherwise returns -1
         */

        // check for individual row actions
	    $filter_action = $_REQUEST['filter_action'] ?? null;
	    $action = $_REQUEST['action'] ?? null;
	    $action2 = $_REQUEST['action2'] ?? null;
	    $the_table_action = $this->current_action($filter_action,$action,$action2);

        if ('restore' === $the_table_action) {
            $nonce = wp_unslash($_REQUEST['_wpnonce']);
            // verify the nonce.
            if (!wp_verify_nonce($nonce, 'restore_nonce')) {
                $this->invalid_nonce_redirect();
            } else {
	            $tableId = common::inputGetInt('table');
                database::update('#__customtables_tables', ['published' => 0], ['id' => $tableId]);
                //echo '<div id="message" class="updated notice is-dismissible"><p>1 table restored from the Trash.</p></div>';
                $this->graceful_redirect();
            }
        }

        if ('trash' === $the_table_action) {
            $nonce = wp_unslash($_REQUEST['_wpnonce']);
            // verify the nonce.
            if (!wp_verify_nonce($nonce, 'trash_nonce')) {
                $this->invalid_nonce_redirect();
            } else {
	            $tableId = common::inputGetInt('table');
                database::update('#__customtables_tables', ['published' => -2], ['id' => $tableId]);
                //echo '<div id="message" class="updated notice is-dismissible"><p>1 table moved to the Trash.</p></div>';
                $this->graceful_redirect();
            }
        }

        if ('delete' === $the_table_action) {
            $nonce = wp_unslash($_REQUEST['_wpnonce']);
            // verify the nonce.
            if (!wp_verify_nonce($nonce, 'delete_nonce')) {
                $this->invalid_nonce_redirect();
            } else {
	            $tableId = common::inputGetInt('table');
                $this->helperListOfTables->deleteTable($tableId);
                //echo '<div id="message" class="updated notice is-dismissible"><p>1 table permanently deleted.</p></div>';
                $this->graceful_redirect();
            }
        }

        // check for table bulk actions

        if ($this->is_table_action('customtables-tables-edit'))
            $this->handle_table_actions_edit();

        if ($this->is_table_action('customtables-tables-publish'))
            $this->handle_table_actions_publish(1);

        if ($this->is_table_action('customtables-tables-unpublish') or $this->is_table_action('customtables-tables-restore'))
            $this->handle_table_actions_publish(0);

        if ($this->is_table_action('customtables-tables-trash'))
            $this->handle_table_actions_publish(-2);

        if ($this->is_table_action('customtables-tables-delete'))
            $this->handle_table_actions_delete();
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
	    $page = common::inputGetCmd('page');
        wp_die(__('Invalid Nonce', $this->plugin_text_domain),
            __('Error', $this->plugin_text_domain),
            array(
                'response' => 403,
                'back_link' => esc_url(add_query_arg(array('page' => wp_unslash($page)), admin_url('users.php'))),
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
            $url = 'admin.php?page=customtables-tables';

        if ($this->current_status != null)
            $url .= '&status=' . $this->current_status;

        ob_start(); // Start output buffering
        ob_end_clean(); // Discard the output buffer
        wp_redirect(admin_url($url));
        exit;
    }

    function is_table_action($action): bool
    {
	    $action1 = common::inputGetCmd('action','');
	    $action2 = common::inputGetCmd('action2','');
        if ($action1 === $action || $action2 === $action)
            return true;

        return false;
    }

    function handle_table_actions_edit()
    {
        // Assuming $_POST['table'] contains the selected items
        $table_id = (int)(isset($_POST['table']) ? $_POST['table'][0] : '');

        // Redirect to the edit page with the appropriate parameters
        $this->graceful_redirect('admin.php?page=customtables-tables-edit&action=edit&table=' . $table_id);
    }

    function handle_table_actions_publish(int $state): void
    {
        $nonce = wp_unslash($_REQUEST['_wpnonce']);
        // verify the nonce.
        if (!wp_verify_nonce($nonce, 'bulk-' . $this->_args['plural'])) {
            $this->invalid_nonce_redirect();
        } else {
            $tables = ($_POST['table'] ?? []);

            foreach ($tables as $table)
                database::update('#__customtables_tables', ['published' => $state], ['id' => (int)$table]);

            if (count($tables) > 0)
                $this->graceful_redirect();

            echo '<div id="message" class="updated error is-dismissible"><p>Tables not selected.</p></div>';
        }
    }

    function handle_table_actions_delete()
    {
        $tables = ($_POST['table'] ?? []);
        if (count($tables) > 0) {
            foreach ($tables as $tableId)
                $this->helperListOfTables->deleteTable($tableId);

            $this->graceful_redirect();
        }
        echo '<div id="message" class="updated error is-dismissible"><p>Tables not selected.</p></div>';
    }
}
