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
$query = "select * from ".$wpdb->prefix."wpr_newsletter_mailouts where id=$id";
$mailout = $wpdb->get_results($query);
$mailout = $mailout[0];
$output["Subject"] = $mailout->subject;
$output["Text Body"] = "<pre>".$mailout->textbody."</pre>";
$output["HTML Body"] = $mailout->htmlbody;
$output["Sent At"] = date("g:i d F Y",$mailout->time);
$newsletter = _wpr_newsletter_get($mailout->nid);
$output["Newsletter"] = $newsletter->name;
$output["Recipients"] = (!$mailout->recipients)?"All Subscribers":$mailout->recipients;
?>
<table border="1" style="border: 1px solid #ccc;" cellpadding="10">
  <tr>
    <td ><strong>Subject Of E-Mail:</strong></td>
    <td ><?php echo $output["Subject"] ?></td>
  </tr>
  <tr>
    <td><strong>Newsletter:</strong></td>
    <td><?php echo $output["Newsletter"] ?></td>
  </tr>
  <tr>
    <td  colspan="2"><strong>Text Body:</strong><br>
      <div style="width: 100%; height: 150px; border: 1px solid #eee;">
        <pre>
		<?php echo $output["Text Body"] ?>
        </pre>
      </div></td>
  </tr>
  <tr>
    <td colspan="2"> <strong>HTML Body:</strong>
      <div style="width:100%; height: 150px; border: 1px solid #eee" contentEditable="true"> <?php echo $output["HTML Body"] ?> </div></td>
  </tr>
  <tr >
    <td >Recipients:</td>
    <td ><?php echo $output["Recipients"]?></td>
  </tr>
  <tr>
    <td>Sent At:</td>
    <td><?php echo $output["Sent At"] ?></td>
  </tr>
</table>

