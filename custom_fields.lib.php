<?php
function _wpr_newsletter_custom_fields_get($id)
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_custom_fields where id=$id";
	$result = $wpdb->get_results($query);
	return $result[0];	
}

function _wpr_newsletter_all_custom_fields_get($id)
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_custom_fields where nid=$id";
	$result = $wpdb->get_results($query);
	return $result[0];
	
}

function _wpr_newsletter_custom_fields_update($info)
{
	global $wpdb;
	$info = (object) $info;
	$query = "UPDATE  ".$wpdb->prefix."wpr_newsletters_custom_fields SET name='$info->name', type='$info->type', enum='$info->enum', label='$info->label' where id='$info->id';";	
	$result = $wpdb->query($query);
}

?>