<?php
global $myi;
global $CONFIG_ROOT;
$CONFIG_ROOT = "/var/www/kanji/null.php";
$myi = mysqli_connect("localhost","root","tj18","kanji");
#$myi->query("SET NAMES 'utf-8'");
$myi->query("SET CHARACTER SET 'utf-8'"); 
$myi->query('SET NAMES utf8');
$myi->query ('SET character_set_client = utf8');
$myi->query ('SET character_set_results = utf8');
$myi->query ('SET character_set_connection = utf8'); 
#require_once("/var/www/offline-server/joomla/clslib/class1.php");
function doqueryi($sql,$ln=0,$die_on_error=1,$dbh=0)
{
	global $mysqli;	if(!isset($dbh) || !is_object($dbh))#200902181323:Abhishek:bug 1354 made check for isobject
		$dbh = $mysqli;

	if(!$dbh)
		throw new Exception('mysqli handle is invalid!');

	global $debugprinting;
	global $debug_dump;
	global $last_mysql_error;
	global $last_mysql_errno;
	global $query_counter;
	$query_counter++;

	if($debugprinting)
		echo "<!-- $query_counter: doqueryi ($sql) -->\n";
	
	$debug_dump .= "<font class=query>doquery($sql) /* line $ln */</font>\n";		
	
	$result = mysqli_query($dbh,$sql);
	if($last_mysql_errno=mysqli_errno($dbh))
	{
		$errstr =$last_mysql_error =  mysqli_error($dbh);
		
		$e_out = "<font color=red>$errstr</font> at line $ln (" . session_id() . ")<br/>";
		$debug_dump .= $e_out;
		mysqli_rollback($dbh);
		if($debugprinting)
		{
			echo "<div class='sql_error'>" . $errstr . "</div>";
			echo "<div class='sql'>$sql</div>";
			echo "<pre class='backtrace'>";
			debug_print_backtrace();
			echo "</pre>";
		}
		$errstr = quotemeta($errstr);
		clsLog::logthis(v2_doquery_FAILED,$errstr);
		if($die_on_error)
			exit;
		else
			return false;
	}
	return $result;
}
?>
