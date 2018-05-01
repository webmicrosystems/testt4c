<?php
require_once("phpGrid.php");
if(!session_id()){ session_start();}

if (!isset($HTTP_POST_VARS) && isset($_POST)){ $HTTP_POST_VARS = $_POST;}  // backward compability when register_long_arrays = off in config 

$gridName   = isset($_GET['gn']) ? $_GET['gn'] : die('PHPGRID_ERROR: URL parameter "gn" is not defined');

$grid_sql	= $_SESSION[GRID_SESSION_KEY.'_'.$gridName.'_sql'];
$sql_key	= unserialize($_SESSION[GRID_SESSION_KEY.'_'.$gridName.'_sql_key']);
$sql_fkey	= $_SESSION[GRID_SESSION_KEY.'_'.$gridName.'_sql_fkey'];
$sql_table	= $_SESSION[GRID_SESSION_KEY.'_'.$gridName.'_sql_table'];  
$sql_filter	= $_SESSION[GRID_SESSION_KEY.'_'.$gridName.'_sql_filter'];       
$db_connection = unserialize($_SESSION[GRID_SESSION_KEY.'_'.$gridName.'_db_connection']);  
// $is_debug		= $_SESSION[GRID_SESSION_KEY.'_'.$gridName.'_is_debug'];
$has_multiselect = $_SESSION[GRID_SESSION_KEY.'_'.$gridName.'_has_multiselect'];

//establish db connection
$cn = $db_connection;
if(empty($cn)){
    $db = new C_DataBase(PHPGRID_DB_HOSTNAME, PHPGRID_DB_USERNAME, PHPGRID_DB_PASSWORD, PHPGRID_DB_NAME, PHPGRID_DB_TYPE, PHPGRID_DB_CHARSET);
}
else {       
    $db = new C_DataBase($cn["hostname"],$cn["username"],$cn["password"],$cn["dbname"],$cn["dbtype"],$cn["dbcharset"]);        
}

// if it is a masterdetail grid, obtain the value of the foreign key to save it when later adding new 
$src = isset($_GET['src'])?$_GET['src']:'';
if($src=='md'){
	$fkey = $_GET['fkey'];
	$fkey_value = $_GET['fkey_value'];	
}

$arrFields  = array();
$pk         = $sql_key; // $dg->get_sql_key();      // Array: primary key
$pk_val     = explode(",",$_POST[JQGRID_ROWID_KEY]); // e.g. "10104---141,10103---14111", convert to Array
$oper       = isset($_POST['oper']) ? $_POST['oper'] : ''; // operan type
$sqlCrud    = '';     // CRUD sql

// ###### DEBUG ONLY #############
if(C_Utility::is_debug()){
    echo 'pk: ';
    print_r($pk);
    echo 'pk_val: ';
    print_r($pk_val);
}
// ###############################

if($oper != ''){
    $rs     = $db->select_limit($grid_sql, 1, 1);
    
    // EXCLUDING: 'oper', non-table-field, and auto increment, fields.          
    foreach($HTTP_POST_VARS as $key => $value){
        if($key != 'oper'){   
            $obj_field = $db->field_metacolumn($sql_table, $key);

			// check field type. do not save field is either auto increment (MySQL) or meta type is 'R' (MS SQL, PostgreSQL)
            if($obj_field){
				if(isset($obj_field->auto_increment)){		// MySQL, MS SQL
					if(!$obj_field->auto_increment){
						$arrFields[$key] = $value;      
					}                                     
				}elseif((isset($obj_field->type))){
					if($obj_field->type != 'SERIAL'){		// Postgres
						$arrFields[$key] = $value;	
					}			
				}elseif($db->field_metatype($rs, $db->field_index($rs, $key)) != 'R'){   // Others? Check field type directly by field index
					$arrFields[$key] = $value;
				}                                                    
            }else{
                $arrFields[$key] = $value;    
            }         
		}
    }    

	// prefill a detail grid with the value of the foreign key from master grid when adding
	// ONLY prefill when fkey_value is not set or left blank because user CAN enter a different fkey_value via form that is different from what its parent has.
	if($src=='md' && $oper == 'add'){
		if(!isset($_POST[$fkey]) || (isset($_POST[$fkey]) && $_POST[$fkey] == '')){
			$arrFields[$fkey] = $fkey_value;    
		}
	}



    // Add single quote to PK Value if it's not an integer(I), numeric(N), or autocrement int(R)
    // Richard 9/2013 - added composite PK support with quote_fields
    $sql_where = '';
    if($oper != 'add'){
        $pk_val_new = $db->quote_fields($rs, $sql_key, $pk_val);
        $sql_where = ' ('. implode(',', $sql_key) .') IN ('. implode(',', $pk_val_new) .') ';

        // ###### DEBUG ONLY ######################
        if(C_Utility::is_debug()){ echo 'sql_where: '. $sql_where; }
        // #########################################
    }




    // *** Note ***
    // Apparently, the SQL does not put single quote around numerics. This is preferred. 
    // Why GetUpdateSQL, not AutoExecute()? 
    // 1. $GetUpdateSQL($rs, $arrFields, $forceUpdate) does not require table name as parameter
    // 2. *** It only update values with valid field name ***
    // 3. AutoExecute() creates more overhead by validating whether rs is valid
    switch($oper){
        case 'add':
			$sqlCrud = $db->db->GetInsertSQL($rs, $arrFields, get_magic_quotes_gpc(), true);
            break;
        case 'edit':      
			$sqlCrud = $db->db->GetUpdateSQL($rs, $arrFields, true, get_magic_quotes_gpc()) .'  WHERE '. $sql_where; // $pk .'='. $pk_val;
            break;
        case 'del':
            // borrowed from _adodb_getupdatesql() in adodb-lib.inc.php
            preg_match("/FROM\s+".ADODB_TABLE_REGEX."/is", $grid_sql, $tableName);
            $tableName = $tableName[1];
//			if($has_multiselect){
//                $sqlCrud = 'DELETE FROM '. $tableName .'  WHERE '. $pk .' IN('. $pk_val .')';
//            }else{
                $sqlCrud = 'DELETE FROM '. $tableName .'  WHERE '. $sql_where; // $pk .'='. $pk_val;
//            }
            break;
    }
    if(C_Utility::is_debug()){ echo $sqlCrud;}




    
	if($sqlCrud!='') {
		$db->db_query($sqlCrud);    
		if($oper == 'add'){
			echo '{"id":"'. $db->db->Insert_ID() .'"}';
		}else{
			// do not display debug if it's add (which is going to throw off the JSON returned)
            // ###### DEBUG ONLY ######################
			if(C_Utility::is_debug()) {
				print_r($arrFields);
				echo 'SQL: '. $sqlCrud ."\n"; 
			}
            // #########################################
		}
	}

}

$db = null;
?>
