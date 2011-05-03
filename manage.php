<?php
include "../../../wp-config.php";
$request = $_SERVER['QUERY_STRING'];
$plainstring = base64_decode($request);
$parts = explode("%$%",$plainstring);
$sid = $parts[0];
$nid = $parts[1];
$hash = $parts[2];
function show_unsubscribed()
{
	require "templates/unsubscribed.html";
}

function confirm_unsubscription($nid,$sid,$hash)
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_subscribers where id='$sid' and hash='$hash' and active=1 and confirmed=1;";
	$subscriber = $wpdb->get_results($query);	
	if (count($subscriber) > 0)
	{
		$newsletter = _wpr_newsletter_get($nid);
		$subscriber = _wpr_subscriber_get($sid);
		$query = "select b.* from ".$wpdb->prefix."wpr_subscribers a, ".$wpdb->prefix."wpr_newsletters b where b.id=a.nid and a.email='".$subscriber->email."' and a.active=1 and a.confirmed=1;";
		$newsletters = $wpdb->get_results($query);
		?>
	<div style="font-family:Verdana, Geneva, sans-serif; font-size:12px; padding:20px; margin-left: auto; margin-right: auto; width:300px; background-color:#f0f0f0; border: 1px solid #c0c0c0;"><form action="<?php print $_SERVER['REQUEST_URI'] ?>" method="post">
	<input type="hidden" name="confirmed" value="true">
	You are about to unsubscribe from:<br><br />
	
	<input type="hidden" name="email" value="<?php  echo $subscriber->email ?>" />
	<?php 
	foreach ($newsletters as $newsletter)
	{
		?>  
	<input type="checkbox" name="newsletter[]" checked="checked" value="<?php echo $newsletter->id ?>" id="nl_<?php echo $newsletter->id ?>" /> <label for="nl_<?php echo $newsletter->id ?>"><?php echo $newsletter->name ?> Newsletter<br />
<blockquote>
<?php
//get blog subscriptions
$query = "select * from ".$wpdb->prefix."wpr_blog_subscriptions where type='cat' sid=$sid";
$bsubs = $wpdb->get_results($query);
foreach ($bsubs as $sub)
{
	$cat = get_category($sub->eid);
?>
You will stop receiving posts from the <?php echo $cat->name ?> category.<br />
<?php
}

$query = "select * from ".$wpdb->prefix."wpr_blog_subscriptions where type='all' sid='$sid'";
$bsubs = $wpdb->get_results($query);
if (count($bsubs) >0)
{
	?>
New articles posted on the blog will not be delivered.<br />
	<?php
}
//get post series
$query = "SELECT b.* FROM ".$wpdb->prefix."wpr_followup_subscriptions a, wpr_post_series b where type='postseries' and sid='$sid' and b.id=a.eid;";
$pssubs = $wpdb->get_results($query);
if (count($pssubs) >0)
{
?>
<?php
}
foreach ($pssubs as $sub)
{
	"You will stop receiving ".$sub->name." post series<br>";
}

?>  
</blockquote>
    </label><br>
	<?php echo $newsletter->description ?><br />
	<?php
	}
	?>
	Are you sure you want to unsubscribe from the above newsletter(s)?
	<br />
	<br />
	<div align="center">
	<input type="submit" value="Unsubscribe"> <input type="button" onclick="window.location='/'" value="Cancel"></div>
	</form></div>
		<?php
	}
	else // who what? 
	{
		header("HTTP/1.0 404 Not Found");
		exit;
	}
}

if (isset($_POST['confirmed']) && $_POST['confirmed'] == "true")
{
	//delete autoresponders 
	$email = $_POST['email'];
	$wpdb->show_errors();
	foreach ($_POST['newsletter'] as $nid)
	{
		$query = "SELECT id from ".$wpdb->prefix."wpr_subscribers where nid=$nid and email='$email'";
		$sub = $wpdb->get_results($query);
		$sid = $sub[0]->id;
		//delete follow ups.
		$query = "DELETE FROM ".$wpdb->prefix."wpr_followup_subscriptions where sid='$sid'";
		$wpdb->query($query);
		//delete blog subscriptions
		$query = "DELETE FROM ".$wpdb->prefix."wpr_blog_subscription where sid='$sid'";
		$wpdb->query($query);
	
		$query = "DELETE FROM ".$wpdb->prefix."wpr_custom_fields_values where sid='$sid'";
		$wpdb->query($query);
		
		//unsubscribe
		$query = "UPDATE ".$wpdb->prefix."wpr_subscribers set active=0 WHERE id='$sid'";
		$wpdb->query($query);		
	}
	show_unsubscribed();
}
else
{
	confirm_unsubscription($nid,$sid,$hash);
}

?>
