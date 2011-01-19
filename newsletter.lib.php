<?php





function _wpr_newsletter_get($id)
{
	global $wpdb;

	$query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters where id=$id";

	$result = $wpdb->get_results($query);

	return $result[0];

	

}

function _wpr_newsletters_get()
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters";
	$newslettersResult = $wpdb->get_results($query);
	return $newslettersResult;
}

function _wpr_newsletter_update($info)

{

	global $wpdb;

	$info = (object) $info;

	$query = "UPDATE  ".$wpdb->prefix."wpr_newsletters SET name='$info->name', reply_to='$info->reply_to', description='$info->description', confirm_subject='$info->confirm_subject', confirm_body='$info->confirm_body',confirmed_subject='$info->confirmed_subject',confirmed_body='$info->confirmed_body', `fromname`='$info->fromname', `fromemail`='$info->fromemail' where id='$info->id';";	

	$result = $wpdb->query($query);
}

function _wpr_newsletter_create($info)

{

	global $wpdb;

	$info = (object) $info;

	$query = "INSERT INTO ".$wpdb->prefix."wpr_newsletters (name,reply_to, description,confirm_subject,confirm_body,confirmed_subject,confirmed_body,fromname,fromemail) values ('$info->name','$info->reply_to','$info->description','$info->confirm_subject','$info->confirm_body','$info->confirmed_subject','$info->confirmed_body','$info->fromname','$info->fromemail');";
   
	$wpdb->query($query);
}



function _wpr_newsletter_form($parameters="",$title="Add List",$button="Create Newsletter",$error)

{
	?>

<div class="error fade" style="background-color:red; line-height: 20px;"><?php echo $error ?></div>
<div class="wrap">
  <h2><?php echo $title ?></h2>
</div>
<form action="<?php print $_SERVER['REQUEST_URI'] ?>" method="post">
  <table width="900" border="0" cellspacing="0" cellpadding="10">
    <tr>
      <td colspan="3"><h2>Basic Newsletter Information</h2>
        <hr size="1" color="#000"></td>
    </tr>
    <tr>
      <td><b>Name</b>:<br>
        <small>Enter a name for the newsletter. This will be shown to subscribers on when they unsubscribe and to you in this admin panel.</small></td>
      <td><label for="name"></label>
        <input type="text" size="45" name="name" id="name" value="<?php echo  $parameters->name ?>" /></td>
    </tr>
    <tr>
      <td><strong>From Name:</strong><br/>
        <small>When subscribers of this newsletter receive any email (follow up , broadcasts, blog emails), they will see what you set here in the From column in their mail client.</td>
      <td><input type="text" name="fromname" value="<?php echo $parameters->fromname ?>" size="40" maxlength="30"></td>
    </tr>
    <tr>
      <td><strong>From Email:</strong><br/>
        <small>The email address from which the email is marked as sent. If not set, the email address will be sent from <?php echo get_option("admin_email"); ?> (The email set at the administrator's <a href="profile.php">profile page</a>)</td>
      <td><input type="text" name="fromemail" value="<?php echo $parameters->fromemail ?>" size="40"></td>
    <tr>
      <td><strong>Reply To:</strong> <br>
        <small>When subscribers choose to reply to your email, they will be able to reply to this address.</small></td>
      <td><label for="name"></label>
        <input size="45" type="text" name="reply_to" id="reply_to" value="<?php echo  $parameters->reply_to ?>" /></td>
    </tr>
    <tr>
      <td><strong>Public Description: (optional)</strong>
        <p>This is a description that will be used in the unsubscription page to describe the newsletter when listing all the subscriptions of that subscriber.</p></td>
      <td><label for="description"></label>
        <textarea name="description" id="description" cols="45" rows="5"><?php echo $parameters->description ?></textarea></td>
    </tr>
    <tr>
      <td colspan="2"><h2>Response Emails</h2>
        <hr size="1" color="#000">
        <h3> Confirmation E-Mail:</h3>
        This email is sent immediately after the subscribers enters their name and email address to opt-in. The email address will have a confirmation link.
        <table>
          <tr>
            <td>Subject:</td>
            <td><input type="text" name="confirm_subject" size="80" value="<?php



   if (!$parameters->confirm_subject) 

   {

		$confirm_subject = get_option('wpr_confirm_subject');

		echo $confirm_subject;

   }

   else

   {

	      echo $parameters->confirm_subject;

   }?>" /></td>
          </tr>
          <tr>
            <td colspan="2"> Message Body:<br />
              <textarea name="confirm_body" rows="10" cols="70" wrap="hard"><?php 

if (!$parameters->confirm_body) 

{

	$confirm_email = get_option('wpr_confirm_body');

	echo $confirm_email;

}

else

{

	echo $parameters->confirm_body;

}

	?></textarea>
              <div style="font-size: small; padding: 10px; background-color: #eee; width:700px">
                <h3>Place Holders</h3>
                <table cellspacing="10">
                  <tr>
                    <td  valign="top" width="190">[!confirm!] </td>
                    <td>The confirmation link.</td>
                  </tr>
                  <tr>
                    <td valign="top">[!address!]</td>
                    <td>Your address as you configured it in the <a href="admin.php?page=wpresponder/settings.php">Newsletter > Settings</a> page. This is required to be in compliance with
                      
                      CANSPAM act.<br>
                      Currently your address is : <br>
                      <blockquote>
                        <blockquote><span style="background-color: #d9d9d9; padding:  10px;"> <?php echo get_option("wpr_address") ?> </span></blockquote>
                      </blockquote></td>
                  </tr>
                  <tr>
                    <td valign="top">[!ipaddress!]</td>
                    <td>The ip address from which the subscriber made the request to subscribe.</td>
                  </tr>
                  <tr>
                    <td valign="top">[!date!]</td>
                    <td>The date at which the subscriber tried to subscribe</td>
                  </tr>
                  <tr>
                    <td valign="top">[!url!] 
                    <td>The URL of your website (<?php echo get_option("home"); ?>)</td>
                  </tr>
                  <tr>
                    <td valign="top">[!sitename!] 
                    <td>The name of your blog (<?php echo bloginfo("name"); ?>)</td>
                  </tr>
                </table>
              </div></td>
          </tr>
        </table>
        <h3>Subscription Confirmed E-Mail:</h3>
        <p>This email is sent immediately after the subscriber clicks on the email address confirmation link.</p>
        <br>
        <br>
        <table>
          <tr>
            <td>Subject:</td>
            <td><input type="text" size="80" name="confirmed_subject" value="<?php echo (!$parameters->confirmed_subject)?get_option("wpr_confirmed_subject"):$parameters->confirmed_subject ?>" /></td>
          </tr>
          <tr>
            <td colspan="2"> Message Body:<br />
              <textarea name="confirmed_body" rows="10" cols="70"><?php echo (!$parameters->confirmed_body)?get_option("wpr_confirmed_body"):$parameters->confirmed_body ?></textarea></td>
          </tr>
        </table></td>
    </tr>
    <tr>
      <td><label for="button"></label>
        <input type="hidden" name="id" value="<?php echo $parameters->id ?>"  />
        <input class="button" type="submit" name="button" id="button" value="<?php echo $button ?>" />
        <input class="button" type="button" onclick="window.location='admin.php?page=wpresponder/newsletter.php'" name="button" id="button" value="Cancel" /></td>
      <td>&nbsp;</td>
    </tr>
  </table>
</form>
<?php

}

function _wpr_get_newsletters()
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters";
	$newsletters = $wpdb->get_results($query);
	return $newsletters;
}


