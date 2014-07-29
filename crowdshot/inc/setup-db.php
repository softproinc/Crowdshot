<?php
//***** Temporary Code - Start
// load and setup mySQL framework
	$dbconfig = parse_ini_file('db.ini');

	require_once 'meekrodb.2.2.class.php';

	DB::$host = $dbconfig['host'];
	DB::$dbName = $dbconfig['database'];
	DB::$user = $dbconfig['user'];
	DB::$password = $dbconfig['password'];

	global $TABLE_USER, $TABLE_ASSET, $TABLE_ASSET_RELATIONSHIP, $TABLE_EVENT, $TABLE_ACTIVITY;

	$TABLE_USER               = $dbconfig['table_prefix'] . 'user'               . $dbconfig['table_suffix'];
	$TABLE_ASSET              = $dbconfig['table_prefix'] . 'asset'              . $dbconfig['table_suffix'];
	$TABLE_ASSET_RELATIONSHIP = $dbconfig['table_prefix'] . 'asset_relationship' . $dbconfig['table_suffix'];
	$TABLE_EVENT              = $dbconfig['table_prefix'] . 'event'              . $dbconfig['table_suffix'];
	$TABLE_ACTIVITY           = $dbconfig['table_prefix'] . 'activity'           . $dbconfig['table_suffix'];

	date_default_timezone_set('GMT');
//***** Temporary Code - End
?>