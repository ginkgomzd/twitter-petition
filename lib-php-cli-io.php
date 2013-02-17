<?php
ini_set('display_errors',1); 
error_reporting(E_ALL);
# necessary to up the memory limit for regex backtracking... whatever that is...
#echo ini_get('pcre.backtrack_limit');
ini_set('pcre.backtrack_limit', 100000000);

function stdout_print($msg = "say what?") {
	fwrite(STDOUT, $msg);
}

function read_file_and_callback_per_line($filename, $callback) {
    $fd = fopen ($filename, "r");

    while (!feof($fd) ) {
        $line = fgets($fd);
    	#var_dump($line);
	$callback($line);
    }
    fclose ($fd); 
}

function get_db_conn($host, $db, $user, $password) {
   $DBCONN = mysql_connect($host, $user, $password) OR die(mysql_error());
    mysql_select_db($db, $DBCONN); 
    return $DBCONN;
}

function generate_sql_insert($table, $arrColumns, $arrValues) {
    $columns = $values = '';

    if (count($arrColumns) != count($arrValues)) {
        echo 'WARNING: count(Cols): '. count($arrColumns).' count(Vals): '.count($arrValues);
	echo 'Chopping Values array to match Column Count;';

	$arrValues = array_slice($arrValues, 0, count($arrColumns));
    }
    
    foreach ($arrColumns as $col) {
	if (strlen($columns) > 0) $columns .= ', ';

	$columns .= " `$table`.`$col` ";
    }
    foreach ($arrValues as $val) {
	if (strlen($values) > 0) $values .= ', ';

	$values .= " '".mysql_real_escape_string($val)."' ";
    }

    return sprintf('INSERT INTO %s ( %s ) VALUES ( %s ) ;', $table, $columns, $values);
}
