<?php
include "wp-config.php";
global $wpdb;

$string = $_GET['wpr-confirm'];
$args = base64_decode($string);

$args = explode("%%",$args);

$id = (int) $args[0];
$hash = trim(strip_tags($args[1]));

if (get_magic_quotes_gpc()==1)
{
    addslashes($hash);
}
global $wpdb;

$query = "select * from ".$wpdb->prefix."wpr_subscribers where id=$id and hash='$hash' and active=1 and confirmed=0";
$subs = $wpdb->get_results($query);
if (count($subs) == 0)
{
	?>
	<div align="center"><h2>Your subscription does not exist or you are already subscribed. </h2></div>
	<?php
	exit;
}
$subs = $subs[0];
$query = "UPDATE ".$wpdb->prefix."wpr_subscribers set confirmed=1,  active=1 where id=$id and hash='$hash';";
$wpdb->query($query);
$redirectionUrl = get_bloginfo("home")."/?wpr-confirm=2";

$subscriber = _wpr_subscriber_get($id);
_wpr_move_subscriber($subscriber->nid,$subscriber->email);


//This subscriber's follow up subscriptions' time of creation should be updated to the time of confirmation. 
$currentTime = time();
$query = "UPDATE ".$wpdb->prefix."wpr_followup_subscription set doc='$time', last_date='$time' where sid=$sid;";
$wpdb->query($query);

wp_schedule_single_event(time(), "wpr_cronjob");
spawn_cron();
sendConfirmedEmail($id);

?><script>
window.location='<?php echo $redirectionUrl ?>';
</script><?php
exit;

