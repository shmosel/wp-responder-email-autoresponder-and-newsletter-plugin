<?php

function _wpr_subscriber_get($id)
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_subscribers where id=$id";
	$result = $wpdb->get_results($query);
	if (count($result)>0)
	{
		return $result[0];
	}
	else
	{
		return false;
	}
	
	
}
