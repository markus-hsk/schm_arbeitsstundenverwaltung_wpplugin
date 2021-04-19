<?php
// Direkten Aufruf verhindern
if(!defined( 'WPINC'))
{
	die;
}

if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


const TABLE_DEFAULT_NUM_ROWS = 10;

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
	$table = new SCHM_AV_ListDone_Table();
	$table->prepare_items();
	
	?>
    <div class="wrap">

        <h1>Geleistete Arbeitsstunden</h1>
        <form action="?page=test" method="post">
            <?php  submit_button('Weitere Arbeitsstunden verbuchen', '', 'add_new', false); ?>
        </form>

        <?php $table->display() ?>

    </div>
	<?php
}

function schmAVListOpen()
{
	$table = new SCHM_AV_ListOpen_Table();
	$table->prepare_items();
	
	?>
    <div class="wrap">

        <h1>Offene Arbeitsstunden</h1>
		<?php $table->display() ?>

    </div>
	<?php
	
	
}



class SCHM_AV_ListDone_Table extends SCHM_AV_Basic_Table
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
            case 'Datum':
	            return $this->formatColumnDate($item[$column_name]);
			default:
				return print_r($item,true); //Show the whole array for troubleshooting purposes
		}
	}
	
	function column_Dauer($item)
    {
        return $this->formatColumnStunden($item['Dauer']);
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
		
		[$orderby, $order] = $this->getOrderBy('Datum', 'asc');
	
	    $sql = "SELECT
                    arbeitsstunden.*,
                    CONCAT(mitglieder.Nachname, ', ', mitglieder.Vorname) AS Name
                FROM {$wpdb->prefix}schm_av_arbeitsstunden arbeitsstunden
                LEFT JOIN {$wpdb->prefix}schm_av_mitglieder mitglieder
                    ON arbeitsstunden.Mitglied = mitglieder.Id
                ORDER BY $orderby $order";
        $data = $wpdb->get_results( $sql, ARRAY_A );
        
        $this->items = $data;
		
		parent::prepare_items();
	}
}


class SCHM_AV_ListOpen_Table extends SCHM_AV_Basic_Table
{
	function __construct()
	{
		global $status, $page;
		parent::__construct( array(
			'singular' => 'noch offene Arbeitsstunden',
			'plural'   => 'noch offene Arbeitsstunden',
			'ajax'     => false                         //does this table support ajax?
		));
	}
	
	
	function column_default($item, $column_name)
	{
		switch($column_name)
		{
			case 'Name':
			case 'Arbeitsgruppe':
            case 'Jahr':
				return $item[$column_name];
            case 'Stunden':
	           return $this->formatColumnStunden($item[$column_name]);
            case 'Stichtag':
	            return $this->formatColumnDate($item[$column_name]);
			default:
			
				return print_r($item,true); //Show the whole array for troubleshooting purposes
		}
	}
	
	function get_columns(){
		$columns = array(
			//'cb'           => '<input type="checkbox" />', //Render a checkbox instead of text
			'Name'          => 'Mitglied',
			'Arbeitsgruppe' => 'Arbeitsgruppe',
			'Jahr'          => 'Jahr',
			'Stunden'       => 'Stunden',
			'Stichtag'      => 'Stichtag'
		);
		return $columns;
	}
	
	
	function get_sortable_columns() {
		$sortable_columns = array(
			'Name'          => array( 'Name', true ),     //true means it's already sorted
			'Arbeitsgruppe' => array( 'Arbeitsgruppe', false ),
			'Jahr'          => array( 'Dauer', false ),
			'Stunden'       => array( 'Stunden', false ),
			'Stichtag'      => array( 'Stichtag', false ),
		);
		return $sortable_columns;
	}
	
	
	
	function prepare_items()
	{
		global $wpdb; //This is used only if making any database queries
		
		[$orderby, $order] = $this->getOrderBy('Name', 'asc');
        
        $jahr = (!empty($_REQUEST['jahr'])) ? 'WHERE Jahr = ' . intval($_REQUEST['jahr']) : '';
		
		$sql = "SELECT
                    saison.*,
                    CONCAT(mitglieder.Nachname, ', ', mitglieder.Vorname) AS Name,
                    arbeitsgruppen.Name AS Arbeitsgruppe
		        FROM {$wpdb->prefix}schm_av_saison saison
                INNER JOIN {$wpdb->prefix}schm_av_mitglieder mitglieder
                    ON saison.Mitglied = mitglieder.Id
                LEFT JOIN {$wpdb->prefix}schm_av_arbeitsgruppen arbeitsgruppen
                    ON saison.Arbeitsgruppe = arbeitsgruppen.Id
                $jahr
                ORDER BY $orderby $order";
		$data = $wpdb->get_results( $sql, ARRAY_A );
		
		$this->items = $data;
		
		parent::prepare_items();
	}
}


abstract class SCHM_AV_Basic_Table extends WP_List_Table
{
	function prepare_items()
	{
		$per_page = TABLE_DEFAULT_NUM_ROWS;
		
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		$current_page = $this->get_pagenum();
		
		$total_items = count($this->items);
		$this->items = array_slice($this->items,(($current_page-1)*$per_page),$per_page);
		
		$this->set_pagination_args( array(
			'total_items' => $total_items,                  //WE have to calculate the total number of items
			'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
			'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
		) );
	}
	
	function formatColumnDate($val)
    {
        if($val)
        {
	        $dateformat = get_option( 'date_format' );
	        return date_i18n($dateformat, strtotime($val));
        }
	    else
        {
            return '';
        }
    }
    
    function formatColumnStunden($val)
    {
	    return number_format_i18n( $val, 1).' h';
    }
    
    function getOrderBy($default_field, $default_direction)
    {
        if(!empty($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns())))
        {
	        $orderby = $_REQUEST['orderby'];
        }
        else
        {
            $orderby = $default_field;
        }
	    
        if(!empty($_REQUEST['order']) && in_array($_REQUEST['order'], ['asc', 'desc']))
        {
            $order = $_REQUEST['order'];
        }
        else
        {
            $order = $default_direction;
        }
        
        return [$orderby, $order];
    }
}
