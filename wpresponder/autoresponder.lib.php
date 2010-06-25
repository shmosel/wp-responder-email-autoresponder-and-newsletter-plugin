<?php
function _wpr_autoresponder_get($id)
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_autoresponders where id=$id";
	$result = $wpdb->get_results($query);
	return $result[0];
	
}


?>
