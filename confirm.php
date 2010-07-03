<?php
ini_set("display_errors","on");
error_reporting(E_ALL);

include "../../../wp-config.php";

$string = $_GET['p'];

$args = base64_decode($string);

$args = explode("%%",$args);

$id = trim(strip_tags($args[0]));

$hash = trim(strip_tags($args[1]));

if (get_magic_quotes_gpc()==1)
{
    addslashes($hash);
}
$id = (int) $id;

$query = "select * from ".$wpdb->prefix."wpr_subscribers where id=$id and active=1 and confirmed=0";
$subs = $wpdb->get_results($query);
if (count($subs) == 0)
    {
?>
<div align="center">Your subscription does not exist or your confirmation url has expired</div>
<?php
exit;
}
$subs = $subs[0];


$query = "UPDATE ".$wpdb->prefix."wpr_subscribers set confirmed=1,  active=1 where id=$id and hash='$hash';";

$wpdb->query($query);

$query = "select * from ".$wpdb->prefix."wpr_subscribers where id=$id";

$sub = $wpdb->get_results($query);

$sub  = $sub[0];



//get the confirmation email and subject from newsletter

$newsletter = _wpr_newsletter_get($sub->nid);

$confirmed_subject = $newsletter->confirmed_subject;

$confirmed_body = $newsletter->confirmed_body;





//if a registered form was used to subscribe, then override the newsletter's confirmed email.

$sid = $sub->id; //the susbcriber id

$fid = $args[2];

$query = "SELECT a.* from ".$wpdb->prefix."wpr_subscription_form a, ".$wpdb->prefix."wpr_subscribers b  where a.id=b.fid and b.id=$sid;";

$form = $wpdb->get_results($query);
if (count($form))
{
     $confirmed_subject = $form[0]->confirmed_subject;
     $confirmed_body = $form[0]->confirmed_body;   
}

$redirectionUrl = "confirmed.php";
$params = array($confirmed_subject,$confirmed_body);

wpr_place_tags($sub->id,$params);

$email = $sub->email;



$from_email = get_bloginfo("admin_email");

$from_name = get_bloginfo("name");

$fromheader = "From: $from_name <$from_email>";
wp_mail($email,$params[0],$params[1],$fromheader);
header("HTTP/1.1 301 Moved Permanently");
header("Location: $redirectionUrl");

exit;

?>
