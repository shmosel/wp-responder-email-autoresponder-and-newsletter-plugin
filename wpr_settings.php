<?php
function wpr_settings()
{
	if (isset($_POST['address']))
	{
		update_option("wpr_address",$_POST['address']);
		update_option("wpr_hourlylimit",$_POST['hourly']);
        delete_option("wpr_smtpenabled");
		add_option("wpr_smtpenabled",(isset($_POST['enablesmtp']))?1:0);


		delete_option("wpr_smtphostname");
		add_option("wpr_smtphostname",$_POST['smtphostname']);
		delete_option("wpr_smtpport");
		add_option("wpr_smtpport",$_POST['smtpport']);


		delete_option("wpr_smtprequireauth");
		add_option("wpr_smtprequireauth",($_POST['smtprequireauth']==1)?1:0);

		delete_option("wpr_smtpusername");
		add_option("wpr_smtpusername",$_POST['smtpusername']);
		delete_option("wpr_smtppassword");
		add_option("wpr_smtppassword",$_POST['smtppassword']);

		delete_option("wpr_smtpsecure");
		if ($_POST['securesmtp']!='ssl')
			{

			$securesmtp = ($_POST['securesmtp']=='tls')?"tls":"none";

			}
		else
			$securesmtp = "ssl";
		
		add_option("wpr_smtpsecure",$securesmtp);

				
				//notification settings
				$currentNotificationValue = get_option("wpr_notification_custom_email");
				switch($_POST['notification_email'])
				{				
					case 'customemail':
						$theNotificationEmail = $_POST['notification_custom_email'];
						delete_option('wpr_notification_custom_email');
						add_option('wpr_notification_custom_email',$theNotificationEmail);
					break;
					
					case 'adminemail':
						delete_option('wpr_notification_custom_email');
						add_option('wpr_notification_custom_email','admin_email');						
						break;
				}
				
				

			
			
			if ($_POST['tutorialenable']=='enabled' && get_option('wpr_tutorial_active') == 'off')
			{
				wpr_enable_tutorial();
			}
			else if ($_POST['tutorialenable']=='disabled' && get_option('wpr_tutorial_active') == 'on')
			{
				wpr_disable_tutorial();
			}
			
			
			if ($_POST['updatesenable'] == 'enabled'&& get_option('wpr_updates_active') == 'off')
			{
				wpr_enable_updates();
			}
			else if ($_POST['updatesenable'] == 'disabled' && get_option('wpr_updates_active') == 'on')
			{
				wpr_disable_updates();
			}
	}


        
	?>

<div class="wrap">
  <h2>Global Newsletters Settings</h2>
</div>
<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" name="settingsform" method="post">
  <table width="900">
    <tr>
      <td colspan="2" valign="top" align="left" width="50"><strong>Address</strong>: <br>
        Attached to every e-mail in complaince with CAN-SPAM act in USA.</td>
    </tr>
    <tr>
      <td colspan="2"><textarea name="address" rows="6" cols="60"><?php echo get_option("wpr_address"); ?></textarea></td>
    </tr>
    <tr>
      <td><!--
         
         Please don't remove this code. This advertisement is the reason this plugin is free!
         
         -->
        <a href="http://www.krusible.com"><img src="http://www.wpresponder.com/settingspage.png" width="300" height="270" /></a></td>
    </tr>
    <tr>
      <td colspan="2"><table width="100%">
                <tr>
            <td colspan="2"><h2>Notifications</h2>
            <hr size="1" /></td>
            </tr>
            <tr>         
          <td valign="top" width="300"><strong>Notification e-mail Address:</strong><br />
            <br />
            All WP Responder tutorial articles, WP Responder plugin updates, and other notifications will be sent to this email address <br />
          
            <br /></td>
          <td valign="top" style="padding-left:20px;">
          <input type="radio" name="notification_email" value="adminemail" <?php if (get_option('wpr_notification_custom_email')=="admin_email") { echo "checked=\"checked\""; }?> id="adminemail" />
          <label for="adminemail">Send to the administrator account's email address(<?php echo get_bloginfo('admin_email') ; ?>)</label>
          <br />
          <br />
          <input type="radio" name="notification_email" <?php if (get_option('wpr_notification_custom_email') !="admin_email") { echo 'checked="checked"'; } ?> onclick="document.settingsform.notification_custom_email.focus();" value="customemail" id="customemail" />
          <label for="customemail">
          
          Send to this email address:
          <input type="text" name="notification_custom_email" size="30" value="<?php $notificationEmail = get_option("wpr_notification_custom_email");
		  if ($notificationEmail != "admin_email")
		  {
			  echo get_option("wpr_notification_custom_email");
		  }
		  ?>" />
          </td>
          </tr>
          <tr>
            <td valign="top"><strong>Send WP Responder Tutorial Articles to the notification email address</strong>:</td>
            <td valign="top">
            
            <input type="radio" value="enabled" <?php if (get_option('wpr_tutorial_active') == "on") { echo 'checked="checked"'; } ?> name="tutorialenable" id="tutorial_enable" /> 
              <label for="tutorial_enable"> Enable </label>
              <input type="radio" value="disabled" name="tutorialenable" <?php if (get_option('wpr_tutorial_active') == "off") { echo 'checked="checked"'; } ?> id="tutorial_disable"  /><label for="tutorial_disable"> Disable</label>
              <br />
              <br />
              <br /></td>
          </tr>
          <tr>
            <td valign="top"><strong>Send essential WP Responder plugin update news to the notification e-mail address:</strong></td>
            <td><input type="radio" value="enabled" <?php if (get_option('wpr_updates_active') == "on") { echo 'checked="checked"'; } ?>  name="updatesenable" id="updates_enable" /> 
              <label for="updates_enable"> Enable</label>
              <input type="radio" value="disabled" <?php if (get_option('wpr_updates_active') == "off") { echo 'checked="checked"'; } ?> name="updatesenable" id="updates_disable" /> 
              <label for="updates_disable">Disable</label>
              
              <br />
              <br />
              <br /></td>
          </tr>
        </table>
        
        
        </td>
    </tr>
    <tr>
     <td colspan="2"><h2>E-mail Limit</h2>
     <hr size="1" /></td>
     </tr>
    <tr>
      <td colspan="2"><strong>Hourly Email Limit:</strong> <br />
        <small>The maximum number of emails that can be sent by WP Responder in an hour. Enter 0 for no limit.</small><br />
        <input type="text" name="hourly" value="<?php echo get_option("wpr_hourlylimit"); ?>" />
        <br />
        <em>This sets the limit on the number of emails sent by WP Responder in an hour. E-mails that are sent includes email broadcasts, follow up autoresponder messages, blog subscriptions, blog category subscriptions and post series subscriptions. <strong>Does NOT include verification emails and subscription confirmed emails. </em>
        <br />
<br />

<strong>        Be sure to give a margin of atleast 50 emails for your personal e-mail and e-mail sent by other applications on your website. </strong>
        
        </td>
    </tr>
    <tr>
      <td><br />
        <br /></td>
    </tr>
    <tr>
      <td colspan="2"><hr size="1"  />
              <h2 style="font-family:Georgia, 'Times New Roman', Times, serif; font-weight:normal;">Optional SMTP Settings</h2>
              
 SMTP relay services are provided by websites like <a href="http://www.smtp.com/">SMTP.com</a>. These have a very high limit on the number of emails that can be sent in an hour. <p></p>
  
        <strong>Note: External SMTP server configuration may not always work. This feature is not thoroughly tested. It is not mandatory to setup a SMTP server. Most web hosting servers com with a mail server . </strong>
        </td>
    <tr>
      <td><input type="checkbox" <?php if (get_option("wpr_smtpenabled") == 1) { echo 'checked="checked"'; } ?> name="enablesmtp" id="enablesmtp" value="1">
        <label for="enablesmtp">Use External SMTP Server to send email.</label></td>
    </tr>
    <tr>
      <td>SMTP Server Hostname: </td>
      <td><input name="smtphostname" type="text" value="<?php echo get_option("wpr_smtphostname") ?>" size="50"></td>
    </tr>
    <tr>
      <td>SMTP Server Port: </td>
      <td><input name="smtpport" type="text" value="<?php echo get_option("wpr_smtpport"); ?>" size="50"></td>
    </tr>
    <tr>
      <td><input type="checkbox" id="smtpauth" name="smtprequireauth" <?php if (get_option("wpr_smtprequireauth")==1){ echo 'checked="checked"'; } ?> value="1" id="smtpauth">
        <label for="smtpauth">SMTP Server Requires Authentication</label></td>
    </tr>
    <tr>
      <td>SMTP Username: </td>
      <td><input name="smtpusername" type="text" value="<?php echo get_option("wpr_smtpusername") ?>" size="50"></td>
    </tr>
    <tr>
      <td>SMTP Password</td>
      <td><input name="smtppassword" type="text" value="<?php echo get_option("wpr_smtppassword"); ?>" size="50"></td>
    </tr>
    <tr>
      <td colspan="2">Use encryption:
        <input type="radio" id="ssl" name="securesmtp" value="ssl" <?php if (get_option("wpr_smtpsecure") == 'ssl' ) echo 'checked="checked"'; ?>>
        <label for="ssl">SSL</label>
        <input type="radio" value="tls" name="securesmtp"  <?php if (get_option("wpr_smtpsecure") == 'tls' ) echo 'checked="checked"'; ?> id="tls">
        <label for="tls">TLS</label>
        <input type="radio" value="none"  <?php if (get_option("wpr_smtpsecure") == 'none' ) echo 'checked="checked"'; ?> name="securesmtp" id="nones">
        <label for="nones">None</label>
        <br/>
        <small><strong>Important Note:</strong> Set the port in the field provided above appropriately. It is risky to not use any form of encryption. </td>
    </tr>
    <tr>
      <td colspan="2"><input type="submit" onclick="return validateSettingsForm();" class="button-primary" value="Save Settings" /></td>
    </tr>
  </table>
</form>

<script>
function trim(stringToTrim) {
	return stringToTrim.replace(/^\s+|\s+$/g,"");
}

function validateSettingsForm()
{
	var address = document.settingsform.address.value;
	//the address should have a value.
	if (trim(address).length==0)
	{
		alert('Please enter your address in the address field');
		document.settingsform.address.focus();
		return false;
	}
	//validate the SMTP settings
	
	var smtpSettingsEnabledField = document.settingsform.enablesmtp;
	
	if (smtpSettingsEnabledField.checked == true)
	{
		var smtpfield = document.settingsform.smtphostname
		var smtphostname = trim(smtpfield.value);
		if (smtphostname.length==0)
		{
			alert("You have enabled external SMTP settings. Please specify a SMTP hostname. ");
			smtpfield.focus();
		    return false;	
		}
		
		
		var smtpportfield = document.settingsform.smtpport;
		var smtpport = trim(smtpportfield.value);
		if (smtpport.length==0)
		{
			alert("You have enabled external SMTP settings. Please specify a SMTP port number. ");
			smtpportfield.focus();
			return false;
		}
		
		
		smtpServerAuthenticationField = document.settingsform.smtprequireauth;
		
		if (smtpServerAuthenticationField.checked == true)
		{
			var smtpUsernameField = document.settingsform.smtpusername;
			var smtpusername = trim(smtpUsernameField.value);
			
			if (smtpusername.length==0)
			{
				alert('You have specified that the SMTP server requires authentication. Please specify the username.');
				smtpUsernameField.focus();
				return false;	
			}
			
			var smtpPasswordField = document.settingsform.smtppassword;
			var smtppassword = trim(smtpPasswordField.value);
			
			if (smtppassword.length == 0)
			{
				alert('You have specified that the SMTP server requires authentication. Please specify the password');
				smtpPasswordField.focus();
				return false;
			}
			
		}
	}
	
	//if the smtp settings are enabled, then all the other fields should be set. 
	
	
	
	if (document.getElementById('customemail').checked==true)
	{
		var theemailfield = document.settingsform.notification_custom_email
		var theemailaddress = trim(theemailfield.value);
		var re = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;		
			
		if (theemailaddress.length==0 || !theemailaddress.match(re))
		{
			alert('Please specify a valid notification email address.');
			theemailfield.value='';
			theemailfield.focus();
			return false;
		}
		
	}
	
	
	
	
	
	
	
}
</script>
<?php
}
