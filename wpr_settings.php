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
                


	}


        
	?>
    <div class="wrap"><h2>Global Newsletters Settings</h2></div>
    <form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
    <table width="600">
      <tr>
         <td colspan="2" valign="top" align="left" width="50"><strong>Address</strong>:
        <br>
Attached to every e-mail in complaince with CAN-SPAM act in USA.</td>
       </tr>
       <tr>  <td colspan="2"><textarea name="address" rows="6" cols="60"><?php echo get_option("wpr_address"); ?></textarea>
         </td>
        <tr>
         <td>        
         <!--
         
         Please don't remove this code. This advertisement is the reason this plugin is free!
         
         -->
         
         <a href="http://www.krusible.com"><img src="http://www.wpresponder.com/settingspage.png" width="300" height="270" /></a>
         
         </td>
       </tr>
       <tr>  <td><strong>Hourly Email Limit:</strong> <br />
       <small>The maximum number of emails that can be sent by WP Responder in an hour. Enter 0 for no limit.</small><br />

<input type="text" name="hourly" value="<?php echo get_option("wpr_hourlylimit"); ?>" /><br />
<em>This sets the limit on the number of emails sent by WP Responder. This includes email broadcasts, follow up autoresponder messages, blog subscriptions, blog category subscriptions and post series subscriptions.</em>
         </td>
       </tr>
       <tr>
       	<td><br />
<br />

        </td>
        
       </tr>
       <tr>
           <td colspan="2"><hr size="1" color="black">
               <h2>SMTP Settings</h2></td>
       <tr>
           <td><input type="checkbox" <?php if (get_option("wpr_smtpenabled") == 1) { echo 'checked="checked"'; } ?> name="enablesmtp" value="1"> <label for="enablesmtp">Use External SMTP Server to send email.</label></td></tr>
       <tr>
           <td>SMTP Server Hostname: </td><td><input name="smtphostname" type="text" value="<?php echo get_option("wpr_smtphostname") ?>" size="50"></td></tr>
       <tr><td>SMTP Server Port: </td><td><input name="smtpport" type="text" value="<?php echo get_option("wpr_smtpport"); ?>" size="50"></td></tr>
       <tr><td><input type="checkbox" name="smtprequireauth" <?php if (get_option("wpr_smtprequireauth")==1){ echo 'checked="checked"'; } ?> value="1" id="smtpauth"><label for="smtpauth">SMTP Server Requires Authentication</label></td></tr>
       <tr><td>SMTP Username: </td><td><input name="smtpusername" type="text" value="<?php echo get_option("wpr_smtpusername") ?>" size="50"></td></tr>
       <tr><td>SMTP Password</td><td><input name="smtppassword" type="text" value="<?php echo get_option("wpr_smtppassword"); ?>" size="50"></td></tr>
       <tr><td colspan="2">Use encryption: <input type="radio" id="ssl" name="securesmtp" value="ssl" <?php if (get_option("wpr_smtpsecure") == 'ssl' ) echo 'checked="checked"'; ?>><label for="ssl">SSL</label><input type="radio" value="tls" name="securesmtp"  <?php if (get_option("wpr_smtpsecure") == 'tls' ) echo 'checked="checked"'; ?> id="tls"><label for="tls">TLS</label><input type="radio" value="none"  <?php if (get_option("wpr_smtpsecure") == 'none' ) echo 'checked="checked"'; ?> name="securesmtp" id="nones"><label for="nones">None</label><br/><small><strong>Important Note:</strong> Sent the port in the field provided above appropriately. It is risky to not use any form of encryption. </td></tr>
       <tr>
         <td colspan="2"><input type="submit" class="button-primary" value="Save Settings" /></td>
         </tr>
     </table>
     </form>
    <?php
}
?>