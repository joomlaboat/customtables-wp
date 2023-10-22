<?php

namespace CustomTablesWP\Inc\Admin;
use CustomTables\common;
use CustomTables\CT;
use CustomTables\database;
use CustomTablesWP\Inc\Libraries;
use CustomTables\listOfTables;
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
class Admin_Table_List extends Libraries\WP_List_Table  {

	/**
	 * The text domain of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_text_domain    The text domain of this plugin.
	 */
	protected $plugin_text_domain;
	
    /*
	 * Call the parent constructor to override the defaults $args
	 * 
	 * @param string $plugin_text_domain	Text domain of the plugin.	
	 * 
	 * @since 1.0.0
	 */
	public function __construct( $plugin_text_domain ) {

		$this->plugin_text_domain = $plugin_text_domain;
    	parent::__construct( array(
				'plural'	=>	'users',	// Plural value used for labels and the objects being listed.
				'singular'	=>	'user',		// Singular label for an object being listed, e.g. 'post'.
				'ajax'		=>	false,		// If true, the parent class will call the _js_vars() method in the footer		
			) );

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
        // check and process any actions such as bulk actions.
        $this->handle_table_actions();

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
        $ct = new CT;
        require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-listoftables.php');
        $helperListOfLayout = new \CustomTables\listOfTables($ct);

        $orderby = common::inputGetCmd('orderby');
        $order = common::inputGetCmd('order');
        $current_status = common::inputGetCMD('status');

        $published = match ($current_status) {
            'published' => 1,
            'unpublished' => 0,
            'trash' => -2,
            default => null
        };

        $current_status = common::inputGetCMD('status');
        $query = $helperListOfLayout->getListQuery($published, null, null, $orderby, $order);
        $data = database::loadAssocList($query);
        $newData = [];
        foreach ($data as $item) {
            $table_exists = ESTables::checkIfTableExists($item['realtablename']);

            if ($item['published'] == -2)
                $label = '<span>'.$item['tablename'].'</span>';
            else
                $label = '<a class="row-title" href="?page=customtables-tables-edit&action=edit&table=' . $item['id'] . '">' . $item['tablename'] . '</a>'
                    . (($current_status != 'unpublished' and $item['published'] == 0) ? ' — <span class="post-state">Draft</span>' : '');

            $item['tablename'] = '<strong>'.$label.'</strong>';

            $result = '<ul style="list-style: none !important;margin-left:0;padding-left:0;">';
            $moreThanOneLang = false;
            foreach ($ct->Languages->LanguageList as $lang) {
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
                $result .= '<li>' . (count($ct->Languages->LanguageList) > 1 ? $lang->title . ': ' : '') . '<b>' . $item[$tableTitle] . '</b></li>';
                $moreThanOneLang = true; //More than one language installed
            }

            $result .= '</ul>';
            $item['tabletitle'] = $result;

            if (!$table_exists)
                $item['recordcount'] = __('No Table', $this->plugin_text_domain);
            elseif (($item['customtablename'] !== null and $item['customtablename'] != '') and ($item['customidfield'] === null or $item['customidfield'] == ''))
                $item['recordcount'] = __('No Primary Key', $this->plugin_text_domain);
            else {
                $item['recordcount'] = '<a class="btn btn-secondary" aria-describedby="tip-tablerecords' . $item['id'] . '" href="'
                    . common::curPageURL() . '/administrator/index.php?option=com_customtables&view=listofrecords&tableid=' . $item['id'] . '">'
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
	 * @since 1.0.0
	 * 
	 * @return array
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
	 * @since 1.1.0
	 * 
	 * @return array
	 */
    protected function get_sortable_columns()
    {
        $sortable_columns = array(
            'tablename' => array('tablename', false),
            //'status' => array('status', false),
            'id' => array('id', true)
        );
        return $sortable_columns;
    }

    function column_tablename($item)
    {
        $current_status = common::inputGetCMD('status');
        $actions = [];
        $nonce = wp_create_nonce( 'widgets-access' );

        $url = 'admin.php?page=customtables-tables';
        if ($current_status != null)
            $url .= '&status=' . $current_status;

        if ($current_status == 'trash') {
            $actions['restore'] = sprintf('<a href="'.$url.'&action=restore&table=%s&_wpnonce=%s">' . __('Restore', 'customtables') . '</a>', $item['id'],urlencode( $nonce ));
            $actions['delete'] = sprintf('<a href="'.$url.'&action=delete&table=%s&_wpnonce=%s">' . __('Delete Permanently', 'customtables') . '</a>', $item['id'],urlencode( $nonce ));
        } else {
            $actions['edit'] = sprintf('<a href="?page=customtables-tables-edit&action=edit&table=%s">' . __('Edit', 'customtables') . '</a>', $item['id']);
            $actions['trash'] = sprintf('<a href="'.$url.'&action=trash&table=%s&_wpnonce=%s">' . __('Trash', 'customtables') . '</a>', $item['id'],urlencode( $nonce ));
        }
        return sprintf('%1$s %2$s', $item['tablename'], $this->row_actions($actions));
/*
        $admin_page_url =  admin_url( 'admin.php' );

        // row actions to add usermeta.
        $query_args_add_usermeta = array(
            'page'		=>  wp_unslash( $_REQUEST['page'] ),
            'action'	=> 'add_usermeta',
            'user_id'		=> absint( $item['id']),
            '_wpnonce'	=> wp_create_nonce( 'add_usermeta_nonce' ),
        );
        $add_usermeta_link = esc_url( add_query_arg( $query_args_add_usermeta, $admin_page_url ) );
        $actions['add_usermeta'] = '<a href="' . $add_usermeta_link . '">' . __( 'Add  Meta', $this->plugin_text_domain ) . '</a>';


        $row_value = '<strong>' . $item['tablename'] . '</strong>';
        return $row_value . $this->row_actions( $actions );
*/
    }
	
	/** 
	 * Text displayed when no tables found
	 * 
	 * @since   1.0.0
	 * 
	 * @return void
	 */
    public function no_items() {
        _e( 'No tables found.', $this->plugin_text_domain );
    }

	
	/*
	 * Filter the table data based on the user search key
	 * 
	 * @since 1.0.0
	 * 
	 * @param array $table_data
	 * @param string $search_key
	 * @returns array
	 */
	public function filter_table_data( $table_data, $search_key ) {
		$filtered_table_data = array_values( array_filter( $table_data, function( $row ) use( $search_key ) {
			foreach( $row as $row_val ) {
				if( stripos( $row_val, $search_key ) !== false ) {
					return true;
				}				
			}			
		} ) );
		
		return $filtered_table_data;
		
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
            case 'tablename':
                return $item[$column_name];
            case 'tabletitle':
                return $item[$column_name];
            case 'fieldcount':
                return $item[$column_name];
            case 'recordcount':
                return $item[$column_name];
            case 'published':
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
        $current_status = common::inputGetCMD('status');

        $count_all = database::loadColumn('SELECT COUNT(id) FROM #__customtables_tables WHERE published!=-2')[0];
        $count_trashed = database::loadColumn('SELECT COUNT(id) FROM #__customtables_tables WHERE published=-2')[0];
        $count_published = database::loadColumn('SELECT COUNT(id) FROM #__customtables_tables WHERE published=1')[0];
        $count_unpublished = $count_all - $count_published;

        $link = 'admin.php?page=customtables-tables';

        $views = [];
        if ($count_all > 0)
            $views['all'] = '<a href="' . admin_url($link) . '" class="' . (($current_status === 'all' or $current_status === null) ? 'current' : '') . '">' . __('All') . ' <span class="count">(' . $count_all . ')</span></a>';

        if ($count_published > 0)
            $views['published'] = '<a href="' . admin_url($link . '&status=published') . '" class="' . ($current_status === 'published' ? 'current' : '') . '">' . __('Published') . ' <span class="count">(' . $count_published . ')</span></a>';

        if ($count_unpublished > 0)
            $views['unpublished'] = '<a href="' . admin_url($link . '&status=unpublished') . '" class="' . ($current_status === 'unpublished' ? 'current' : '') . '">' . __('Draft') . ' <span class="count">(' . $count_unpublished . ')</span></a>';

        if ($count_trashed > 0)
            $views['trash'] = '<a href="' . admin_url($link . '&status=trash') . '" class="' . ($current_status === 'trash' ? 'current' : '') . '">' . __('Trash') . ' <span class="count">(' . $count_trashed . ')</span></a>';

        return $views;
    }

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @since    1.0.0
	 * 
	 * @return array
	 */
	public function get_bulk_actions() {

		/*
		 * on hitting apply in bulk actions the url params are set as
		 * ?action=action&table=1
		 * 
		 * action and action2 are set based on the triggers above or below the table
		 * 		    
		 */

        $current_status = common::inputGetCMD('status');
        $actions = [];

        if ($current_status != 'trash')
            $actions['customtables-tables-edit'] = __('Edit', 'customtables');

        if ($current_status == '' or $current_status == 'all') {
            $actions['customtables-tables-publish'] = __('Publish', 'customtables');
            $actions['customtables-tables-unpublish'] = __('Draft', 'customtables');
        } elseif ($current_status == 'unpublished')
            $actions['customtables-tables-publish'] = __('Publish', 'customtables');
        elseif ($current_status == 'published')
            $actions['customtables-tables-unpublish'] = __('Draft', 'customtables');

        if ($current_status != 'trash')
            $actions['customtables-tables-trash'] = __('Move to Trash', 'customtables');

        if ($current_status == 'trash') {
            $actions['customtables-tables-restore'] = __('Restore', 'customtables');
            $actions['customtables-tables-delete'] = __('Delete Permanently', 'customtables');
        }
        return $actions;
	}


    function process_bulk_action()
    {
        // Check if a bulk action is selected
        $action = $this->current_action();
        $action_found = false;

        if ($action === 'customtables-tables-edit') {
            // Assuming $_POST['table'] contains the selected items
            $table_id = (int)(isset($_POST['table']) ? $_POST['table'][0] : '');

            // Redirect to the edit page with the appropriate parameters
            wp_redirect(admin_url('admin.php?page=customtables-tables-edit&action=edit&table=' . $table_id));
            exit();
        }

        if ($action === 'customtables-tables-publish') {
            $tables = (isset($_POST['table']) ? $_POST['table'] : []);
            $sets = [];
            $wheres = [];
            foreach ($tables as $table) {
                $sets[] = 'published=1';
                $wheres[] = 'id=' . (int)$table;
            }

            database::updateSets('#__customtables_tables', $sets, ['(' . implode(' OR ', $wheres) . ')']);
            $action_found = true;
        }

        if ($action === 'customtables-tables-unpublish' or $action === 'customtables-tables-restore') {
            $tables = (isset($_POST['table']) ? $_POST['table'] : []);
            $sets = [];
            $wheres = [];
            foreach ($tables as $table) {
                $sets[] = 'published=0';
                $wheres[] = 'id=' . (int)$table;
            }

            database::updateSets('#__customtables_tables', $sets, ['(' . implode(' OR ', $wheres) . ')']);
            $action_found = true;
        }

        if ($action === 'customtables-tables-trash') {
            $tables = (isset($_POST['table']) ? $_POST['table'] : []);
            $sets = [];
            $wheres = [];
            foreach ($tables as $table) {
                $sets[] = 'published=-2';
                $wheres[] = 'id=' . (int)$table;
            }

            database::updateSets('#__customtables_tables', $sets, ['(' . implode(' OR ', $wheres) . ')']);
            $action_found = true;
        }

        if ($action === 'customtables-tables-delete') {
            $tables = (isset($_POST['table']) ? $_POST['table'] : []);

            $ct = new CT;
            require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-listoftables.php');
            $helperListOfLayout = new CustomTables\listOfTables($ct);

            foreach ($tables as $tableId)
                $helperListOfLayout->deleteTable($tableId);

            $action_found = true;
        }

        if($action_found ) {
            // Redirect to the edit page with the appropriate parameters
            $current_status = common::inputGetCMD('status');
            $url = 'admin.php?page=customtables-tables';
            if ($current_status != null)
                $url .= '&status=' . $current_status;

            wp_redirect(admin_url($url));
            exit();
        }
    }

    /**
	 * Process actions triggered by the user
	 *
	 * @since    1.0.0
	 * 
	 */	
	function handle_table_actions() {
		
		/*
		 * Note: Table bulk_actions can be identified by checking $_REQUEST['action'] and $_REQUEST['action2']
		 * 
		 * action - is set if checkbox from top-most select-all is set, otherwise returns -1
		 * action2 - is set if checkbox the bottom-most select-all checkbox is set, otherwise returns -1
		 */

		// check for individual row actions
		$the_table_action = $this->current_action();
        
		if ( 'view_usermeta' === $the_table_action ) {
			$nonce = wp_unslash( $_REQUEST['_wpnonce'] );
			// verify the nonce.
			if ( ! wp_verify_nonce( $nonce, 'view_usermeta_nonce' ) ) {
				$this->invalid_nonce_redirect();
			}
			else {                    
				$this->page_view_usermeta( absint( $_REQUEST['user_id']) );
				$this->graceful_exit();
			}
		}
		
		if ( 'add_usermeta' === $the_table_action ) {
			$nonce = wp_unslash( $_REQUEST['_wpnonce'] );
			// verify the nonce.
			if ( ! wp_verify_nonce( $nonce, 'add_usermeta_nonce' ) ) {
				$this->invalid_nonce_redirect();
			}
			else {                    
				$this->page_add_usermeta( absint( $_REQUEST['user_id']) );
				$this->graceful_exit();
			}
		}
		
		// check for table bulk actions
		if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'bulk-download' ) || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] === 'bulk-download' ) ) {
			
			$nonce = wp_unslash( $_REQUEST['_wpnonce'] );
			// verify the nonce.
			/*
			 * Note: the nonce field is set by the parent class
			 * wp_nonce_field( 'bulk-' . $this->_args['plural'] );
			 * 
			 */
			if ( ! wp_verify_nonce( $nonce, 'bulk-users' ) ) {
				$this->invalid_nonce_redirect();
			}
			else {
				$this->page_bulk_download( $_REQUEST['users']);
				$this->graceful_exit();
			}
		}
		
	}
	
	/**
	 * View a user's meta information.
	 *
	 * @since   1.0.0
	 * 
	 * @param int $user_id  user's ID	 
	 */
    /*
	public function page_view_usermeta( $user_id ) {
		
		$user = get_user_by( 'id', $user_id );		
		include_once( 'views/partials-wp-list-table-demo-view-usermeta.php' );
	}*/
	
	/**
	 * Add a meta information for a user.
	 *
	 * @since   1.0.0
	 * 
	 * @param int $user_id  user's ID	 
	 */	
	
	/*public function page_add_usermeta( $user_id ) {
		
		$user = get_user_by( 'id', $user_id );		
		include_once( 'views/partials-wp-list-table-demo-add-usermeta.php' );
	}*/
	
	/**
	 * Bulk process users.
	 *
	 * @since   1.0.0
	 * 
	 * @param array $bulk_user_ids
	 */		
	/*public function page_bulk_download( $bulk_user_ids ) {
				
		include_once( 'views/partials-wp-list-table-demo-bulk-download.php' );
	}    */
	
	/**
	 * Stop execution and exit
	 *
	 * @since    1.0.0
	 * 
	 * @return void
	 */    
	 public function graceful_exit() {
		 exit;
	 }
	 
	/**
	 * Die when the nonce check fails.
	 *
	 * @since    1.0.0
	 * 
	 * @return void
	 */    	 
	 public function invalid_nonce_redirect() {
		wp_die( __( 'Invalid Nonce', $this->plugin_text_domain ),
				__( 'Error', $this->plugin_text_domain ),
				array( 
						'response' 	=> 403, 
						'back_link' =>  esc_url( add_query_arg( array( 'page' => wp_unslash( $_REQUEST['page'] ) ) , admin_url( 'users.php' ) ) ),
					)
		);
	 }
	
	
}
