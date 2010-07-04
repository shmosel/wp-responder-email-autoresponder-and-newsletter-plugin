<?php
function show_graph($nid)
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters where id=$nid";
	$results = $wpdb->get_results($query);
	if (count($results) ==0)
	{
		$nid=0;
	}
	if ($nid == 0)
	{
		?>
<div align="center"><h2>Oops. Something's Wrong.</h2>
The graph cannot be generated because I can't find the newsletter. <a href="admin.php?page=wpresponder/wpresponder.php">Click here</a> to go to dashboard. If this keeps happening, please <a href="admin.php?page=wpresponder/wpresponder.php">report a bug</a>.</div>
		<?php
		return;
	}
	?>
    <a href="admin.php?page=wpresponder/wpresponder.php" style="float:left; margin-top:5px;" class="button">&laquo; Back To Dashboard</a>
    <div style="clear:both;"></div>
       <h2>Subscription graph for '<?php echo $results[0]->name; ?>' newsletter.</h2>
    The graph below shows the number of subscriptions and unsubscriptions for the '<?php echo $results[0]->name ?>' newsletter over the past 31 days. 
    
    <div style="background-color:#FEFFE1; border: 1px solid #CCC; color: #006; padding: 5px; margin:5px;">Scroll the graph towards the right to see more of the graph.</div>

	<div style="width: 1000px; height: 420px; overflow:scroll; padding-top:-20px;">
	<img src="<?php echo get_option("home") ?>/wp-content/uploads/wpresponder/subscription_graph_<?php echo $nid ?>.png" style="padding-top:-20px;" />
	</div>
	<?php
}

?>