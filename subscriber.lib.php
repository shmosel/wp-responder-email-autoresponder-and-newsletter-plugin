<?php

function _wpr_subscriber_get($id)
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_subscribers where id=$id";
	$result = $wpdb->get_results($query);
	return $result[0];
	
}


?>