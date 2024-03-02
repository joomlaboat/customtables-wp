<?php
/**
 * Plugin Name:       CustomTables
 * Plugin URI:        https://ct4.us/
 * GitHub:            https://github.com/joomlaboat/customtables-wp
 * Author:            Ivan Komlev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace CustomTablesWP\Inc\Admin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use CustomTables\common;
use CustomTables\CT;
use CustomTables\database;
use CustomTables\MySQLWhereClause;
use Exception;
use WP_List_Table;

class Admin_Record_List extends WP_List_Table
{
    public CT $ct;
    public ?int $tableId;

    protected int $count_all;
    protected int $count_trashed;
    protected int $count_published;
    protected int $count_unpublished;
    protected ?string $current_status;
    protected ?string $firstFieldRealName;

	/**
	 * @throws Exception
	 * @since 1.0.0
	 */
    public function __construct()
    {
        $this->ct = new CT;
        $this->count_all = 0;
        $this->count_trashed = 0;
        $this->count_published = 0;

	    $this->tableId = common::inputGetInt('table');
        if ($this->tableId) {
            $this->ct->getTable($this->tableId);
            if ($this->ct->Table !== null and $this->ct->Table->published_field_found) {

	            $whereClause = new MySQLWhereClause();
	            $whereClause->addCondition('published', -2,'!=');
	            $this->count_all = database::loadColumn($this->ct->Table->realtablename,['COUNT_ROWS'], $whereClause)[0] ?? 0;

	            $whereClause = new MySQLWhereClause();
	            $whereClause->addCondition('published', -2);
	            $this->count_trashed = database::loadColumn($this->ct->Table->realtablename,['COUNT_ROWS'], $whereClause)[0] ?? 0;

	            $whereClause = new MySQLWhereClause();
	            $whereClause->addCondition('published', 1);
	            $this->count_published = database::loadColumn($this->ct->Table->realtablename,['COUNT_ROWS'], $whereClause)[0] ?? 0;
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
	 * @throws Exception
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

	/**
	 * @throws Exception
	 */
	function get_data()
    {
        // Fetch and return your data here
        if ($this->tableId === null or $this->ct->Table == null or $this->ct->Table->tablename === null)
            return [];

	    $search = common::inputGetString('s');
	    $orderby = common::inputGetCmd('orderby');
        if ($orderby == 'customtables_record_firstfield' and $this->firstFieldRealName !== null)
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
            //$this->ct->app->enqueueMessage(CTMiscHelper::JTextExtended('Table not found.'), 'error');
            die('Table not found');
        }

	    if($this->firstFieldRealName !== null)
	    {
            $newData = [];
            foreach ($this->ct->Records as $item) {

	            //$labelText = $this->firstFieldRealName !== null ? $item[$this->firstFieldRealName] : $item[$this->ct->Table->realidfieldname];
	            $labelText = $item[$this->ct->Table->realidfieldname];

	            if ($item['listing_published'] == -2)
		            $label = '<span>' . $labelText . '</span>';
	            else {

		            $label = '<a class="row-title" href="?page=customtables-records-edit&action=edit&table=' . $this->tableId . '&id=' . $item[$this->ct->Table->realidfieldname] . '">'
			            . $labelText . '</a>';

		            if ($this->ct->Table->published_field_found)
			            $label .= (($this->current_status != 'unpublished' and $item['listing_published'] == 0) ? ' — <span class="post-state">Draft</span>' : '');
	            }

	            $item[$this->firstFieldRealName] = '<strong>' . $label . '</strong>';

	            $newData[] = $item;
            }
		    return $newData;
        }
		return $this->ct->Records;
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

        $columns['id'] = __('Id', 'customtables');

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
            $actions['restore'] = sprintf('<a href="' . $url . '&action=restore&table=%s&id=%s&_wpnonce=%s">' . __('Restore') . '</a>',
                $this->tableId,
                $item['id'],
                urlencode(wp_create_nonce('restore_nonce'))
            );

            $actions['delete'] = sprintf('<a href="' . $url . '&action=delete&table=%s&id=%s&_wpnonce=%s">' . __('Delete Permanently') . '</a>',
                $this->tableId,
                $item['id'],
                urlencode(wp_create_nonce('delete_nonce'))
            );
        } else {
            $actions['edit'] = sprintf('<a href="?page=customtables-records-edit&action=edit&table=%s&id=%s">' . __('Edit') . '</a>',
                $this->tableId,
                $item['id']
            );

            $actions['trash'] = sprintf('<a href="' . $url . '&action=trash&table=%s&id=%s&_wpnonce=%s">' . __('Trash') . '</a>',
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
        esc_html_e('No records found.', 'customtables');
    }

    /**
     * Render a column when no column specific method exists.
     *
     * @param array $item
     * @param string $column_name
     *
     * @return mixed
     */

    function column_default($item, $column_name): mixed
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

	    $allowed_html = array(
		    'a' => array(
			    'href' => array(),
			    'class' => array()
		    ),
		    'span' => array(
			    'class' => array()
		    )
	    );

        if (!empty($views)) {
            echo '<ul class="subsubsub">';
            foreach ($views as $view) {
                echo '<li>' . wp_kses($view,$allowed_html) . '</li>';
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
            $actions['customtables-records-edit'] = __('Edit');

        if ($this->current_status == '' or $this->current_status == 'all') {
            $actions['customtables-records-publish'] = __('Publish', 'customtables');
            $actions['customtables-records-unpublish'] = __('Draft', 'customtables');
        } elseif ($this->current_status == 'unpublished')
            $actions['customtables-records-publish'] = __('Publish', 'customtables');
        elseif ($this->current_status == 'published')
            $actions['customtables-records-unpublish'] = __('Draft', 'customtables');

        if ($this->current_status != 'trash')
            $actions['customtables-records-trash'] = __('Move to Trash');

        if ($this->current_status == 'trash') {
            $actions['customtables-records-restore'] = __('Restore');
            $actions['customtables-records-delete'] = __('Delete Permanently');
        }
        return $actions;
    }

	/**
	 * Process actions triggered by the user
	 *
	 * @throws Exception
	 * @since    1.0.0
	 *
	 */
    function handle_record_actions()
    {
        /*
         * Note: Field bulk_actions can be identified by checking $REQUEST['action'] and $REQUEST['action2']
         *
         * action - is set if checkbox from top-most select-all is set, otherwise returns -1
         * action2 - is set if checkbox the bottom-most select-all checkbox is set, otherwise returns -1
         */

        // check for individual row actions
	    $the_table_action = esc_html(wp_strip_all_tags($this->current_action()));

        if ($this->ct->Table->published_field_found) {
            if ('restore' === $the_table_action) {
                // verify the nonce.
                if (!wp_verify_nonce(sanitize_text_field($_REQUEST['_wpnonce']), 'restore_nonce')) {
                    $this->invalid_nonce_redirect();
                } else {
	                $recordId = common::inputGetInt('id');

	                $whereClauseUpdate = new MySQLWhereClause();
	                $whereClauseUpdate->addCondition($this->ct->Table->realidfieldname, $recordId);

                    database::update($this->ct->Table->realtablename, ['published'=>0], $whereClauseUpdate);
                    //echo '<div id="message" class="updated notice is-dismissible"><p>1 record restored from the Trash.</p></div>';
                    $this->graceful_redirect();
                }
            }

            if ('trash' === $the_table_action) {
                // verify the nonce.
                if (!wp_verify_nonce(sanitize_text_field($_REQUEST['_wpnonce']), 'trash_nonce')) {
                    $this->invalid_nonce_redirect();
                } else {
	                $recordId = common::inputGetInt('id');

	                $whereClauseUpdate = new MySQLWhereClause();
	                $whereClauseUpdate->addCondition($this->ct->Table->realidfieldname, $recordId);

                    database::update($this->ct->Table->realtablename, ['published' =>-2], $whereClauseUpdate);
                    //echo '<div id="message" class="updated notice is-dismissible"><p>1 record moved to the Trash.</p></div>';
                    $this->graceful_redirect();
                }
            }
        }

        if ('delete' === $the_table_action) {
            // verify the nonce.
            if (!wp_verify_nonce(sanitize_text_field($_REQUEST['_wpnonce']), 'delete_nonce')) {
                $this->invalid_nonce_redirect();
            } else {
	            $recordId = common::inputGetCmd('id');
                if ($recordId !== null) {
					database::deleteRecord($this->ct->Table->realtablename,$this->ct->Table->realidfieldname,$recordId);
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
	    $page = common::inputGetCmd('page');

        wp_die(__('Invalid Nonce', 'customtables'),
            __('Error', 'customtables'),
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
	    $action1 = common::inputPostCmd('action','','bulk-' . $this->_args['plural']);
	    $action2 = common::inputPostCmd('action2','','bulk-' . $this->_args['plural']);

        if ($action1 === $action || $action2 === $action)
            return true;

        return false;
    }

    function handle_record_actions_edit()
    {
	    if (!wp_verify_nonce(sanitize_text_field($_REQUEST['_wpnonce']), 'bulk-' . $this->_args['plural'])) {
		    $this->invalid_nonce_redirect();
	    } else {
		    $recordId = (int)(isset($_POST['ids']) ? intval($_POST['id'][0]) : '');
		    // Redirect to the edit page with the appropriate parameters
		    $this->graceful_redirect('admin.php?page=customtables-records-edit&action=edit&table=' . $this->tableId . '&id=' . $recordId);
	    }
    }

	/**
	 * @throws Exception
	 */
	function handle_record_actions_publish(int $state): void
    {
        // verify the nonce.
        if (!wp_verify_nonce(sanitize_text_field($_REQUEST['_wpnonce']), 'bulk-' . $this->_args['plural'])) {
            $this->invalid_nonce_redirect();
        } else {

			$records = (isset($_POST['ids']) && is_array($_POST['ids'])) ? common::sanitize_post_field_array($_POST['ids']) : [];

            foreach ($records as $recordId) {

	            $whereClauseUpdate = new MySQLWhereClause();
	            $whereClauseUpdate->addCondition('id', $recordId);

	            database::update($this->ct->Table->realtablename, ['published' => $state], $whereClauseUpdate);
            }

            if (count($records) > 0)
                $this->graceful_redirect();

            echo '<div id="message" class="updated error is-dismissible"><p>Records not selected.</p></div>';
        }
    }

	/**
	 * @throws Exception
	 */
	function handle_record_actions_delete()
    {
	    // verify the nonce.
	    if (!wp_verify_nonce(sanitize_text_field($_REQUEST['_wpnonce']), 'bulk-' . $this->_args['plural'])) {
		    $this->invalid_nonce_redirect();
	    } else {

		    $records = (isset($_POST['ids']) && is_array($_POST['ids'])) ? common::sanitize_post_field_array($_POST['ids']) : [];

		    if (count($records) > 0) {
			    foreach ($records as $recordId)
				    database::deleteRecord($this->ct->Table->realtablename,$this->ct->Table->realidfieldname,$recordId);

			    $this->graceful_redirect();
		    }
		    echo '<div id="message" class="updated error is-dismissible"><p>Records not selected.</p></div>';
	    }
    }
}
