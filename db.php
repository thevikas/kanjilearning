<?php
session_start();
error_reporting(E_ALL);
global $myi;
global $CONFIG_ROOT;
$CONFIG_ROOT = "/var/www/kanji/null.php";

require_once("clsWord.php");
require_once("clsMysqli2.php");

$myi = new mysqli2("localhost","root","tj18","kanji");

#mysqli_connect();
#$myi->query("SET NAMES 'utf-8'");
$myi->query("SET CHARACTER SET 'utf-8'"); 
$myi->query('SET NAMES utf8');
$myi->query ('SET character_set_client = utf8');
$myi->query ('SET character_set_results = utf8');
$myi->query ('SET character_set_connection = utf8'); 
#require_once("/var/www/offline-server/joomla/clslib/class1.php");
function doqueryi($sql,$ln=0,$die_on_error=1,$dbh=0)
{
	global $myi;
	global $debugprinting;
	global $debug_dump;
	global $last_mysql_error;
	global $last_mysql_errno;
	global $query_counter;

	$query_counter++;

	if($debugprinting)
		echo "<!-- $query_counter: doqueryi ($sql) -->\n";
	
	$debug_dump .= "<font class=query>doquery($sql) /* line $ln */</font>\n";		
	
    $result = $myi->query($sql);
	if($last_mysql_errno=mysqli_errno($myi))
	{
		$errstr =$last_mysql_error =  mysqli_error($myi);
		
	    echo "<font color=red>$errstr</font> at line $ln (" . session_id() . ")<br/>";
		return false;
	}
	return $result;
}

function cleanvarg($varname,$def="")
{
    if(isset($_GET[$varname]))
        return $_GET[$varname];
    return $def;
}

function cleanvarp($varname,$def="")
{
    if(isset($_POST[$varname]))
        return $_POST[$varname];
    return $def;
}

#200912011156:vikas:L62:very commonly required function
function getcount($sql)
{
    global $myi;
    $r = doqueryi($sql);
    if(!$r)
        throw new Exception("EOF! or error" . $myi->error);
    $rs = $r->fetch_array();
    return $rs[0];
}

require_once("clsWord.php");
?>
