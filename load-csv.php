#!/usr/bin/php -q
<?php

include __DIR__.'/db-conf.php'; # configure database connection variables
include __DIR__.'/lib-php-cli-io.php';

$CSV = $argv[1]; # first script argument, the file to load;
$DELIMITER = ',';
$FIELD_DEF = array( 'first','last','city','state');

$CREATE_SCRIPT = file_get_contents(__DIR__.'/create-tbl-petitioners.sql');
$TABLE = 'petitioners';

### END CONFIG ###

$db = get_db_conn($HOST, $DB, $USER, $PASSWORD);

function import_content_insert_rows($line) {
  global $db;
  global $DELIMITER;
  global $FIELD_DEF;
  global $TABLE;

  $cols = $FIELD_DEF;

  $row = explode($DELIMITER, $line);
  $row = array_slice($row, 0, count($cols)); #drop extra field at end of row

  $insert = generate_sql_insert($TABLE, $cols, $row);
  mysql_query($insert, $db);
}

# mysql_query doesn't support multiple statements
$query = explode(";\n", $CREATE_SCRIPT);

foreach ($query as $statement) {
  mysql_query($statement.';', $db);
}

read_file_and_callback_per_line($CSV, 'import_content_insert_rows');

mysql_close($db);

?>
