<?php
include "../../../wp-config.php";
get_currentuserinfo();	
$level = $current_user->user_level;
if ($level < 8 )
{
	header("HTTP/1.0 404 Not Found");
	exit;
}

$id = $_GET['mid'];
$query = "delete from ".$wpdb->prefix."wpr_newsletter_mailouts where id=$id";
$wpdb->query($query);
?>
