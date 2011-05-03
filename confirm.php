<?php

include "../../../wp-config.php";



$string = $_GET['p'];

$args = base64_decode($string);

$args = explode("%set%",$args);

$id = $args[0];

$hash = $args[1];

$query = "select * from ".$wpdb->prefix."wpr_subscribers where id=$id";

$subs = $wpdb->get_results($query);

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



$fid = $args[2];

$query = "SELECT * from ".$wpdb->prefix."wpr_subscription_form where id=$fid;";

$form = $wpdb->get_results($query);	

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