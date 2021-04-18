<?php
// Direkten Aufruf verhindern
if(!defined( 'WPINC'))
{
	die;
}

if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

function schmAVIndex()
{
    ?>
		<h1>
			<?php esc_html_e( 'Arbeitsstunden-Verwaltung', 'schmav_index_title' ); ?>
		</h1>
	<?php
}

function schmAVListDone()
{
	$table = new SCHM_AV_List_Table();
	$table->prepare_items();
	
	?>
    <div class="wrap">

        <h1>Geleistete Arbeitsstunden</h1>

        <?php $table->display() ?>

    </div>
	<?php
}

function schmAVListOpen()
{
	?>
    <h1>
		<?php esc_html_e( 'Offene Arbeitsstunden', 'schmav_open_title' ); ?>
    </h1>
	<?php
}



class SCHM_AV_List_Table extends WP_List_Table
{
    function __construct()
    {
		global $status, $page;
		parent::__construct( array(
		    'singular' => 'Arbeitsstunden-Eintrag',
		    'plural'   => 'Arbeitsstunden-Einträge',
		    'ajax'     => false                         //does this table support ajax?
	    ));
	}
 
 
	function column_default($item, $column_name)
    {
		switch($column_name)
        {
			case 'Name':
			case 'Beschreibung':
				return $item[$column_name];
			default:
				return print_r($item,true); //Show the whole array for troubleshooting purposes
		}
	}
	
	function column_Dauer($item)
    {
        return number_format_i18n( $item['Dauer'], 1).' h';
    }
    
    function column_Datum($item)
    {
	    $dateformat = get_option( 'date_format' );
        return date_i18n($dateformat, strtotime($item['Datum']));
    }
	
	function get_columns(){
		$columns = array(
            //'cb'           => '<input type="checkbox" />', //Render a checkbox instead of text
			'Name'         => 'Mitglied',
			'Datum'        => 'Datum',
			'Dauer'        => 'Dauer',
			'Beschreibung' => 'Beschreibung'
		);
		return $columns;
	}
	
	
	function get_sortable_columns() {
		$sortable_columns = array(
			'Name'      => array('Name',false),
			'Datum'     => array('Datum',true),     //true means it's already sorted
			'Dauer'     => array('Dauer',false)
		);
		return $sortable_columns;
	}
	
	
	
	function prepare_items()
    {
		global $wpdb; //This is used only if making any database queries
		
		/**
		 * First, lets decide how many records per page to show
		 */
		$per_page = 10;
		
		
		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		
		
		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		
		/**
		 * Optional. You can handle your bulk actions however you see fit. In this
		 * case, we'll handle them within our package just to keep things clean.
		 */
		//$this->process_bulk_action();
		
		
	
	    
	
	    $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'Datum'; //If no sort, default to title
	    $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
	
	    $sql = "SELECT arbeitsstunden.*, CONCAT(mitglieder.Nachname, ', ', mitglieder.Vorname) AS Name
FROM {$wpdb->prefix}schm_av_arbeitsstunden arbeitsstunden
LEFT JOIN {$wpdb->prefix}schm_av_mitglieder mitglieder
    ON arbeitsstunden.Mitglied = mitglieder.Id
ORDER BY $orderby $order";
        $data = $wpdb->get_results( $sql, ARRAY_A );
		
		
		/**
		 * REQUIRED for pagination. Let's figure out what page the user is currently
		 * looking at. We'll need this later, so you should always include it in
		 * your own package classes.
		 */
		$current_page = $this->get_pagenum();
		
		/**
		 * REQUIRED for pagination. Let's check how many items are in our data array.
		 * In real-world use, this would be the total number of items in your database,
		 * without filtering. We'll need this later, so you should always include it
		 * in your own package classes.
		 */
		$total_items = count($data);
		
		
		/**
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to
		 */
		$data = array_slice($data,(($current_page-1)*$per_page),$per_page);
		
		
		
		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = $data;
		
		
		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
			'total_items' => $total_items,                  //WE have to calculate the total number of items
			'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
			'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
		) );
	}
}
