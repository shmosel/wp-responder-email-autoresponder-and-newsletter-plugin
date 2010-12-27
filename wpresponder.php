<?php

/*

Plugin Name: WP Responder

Plugin URI: http://expeditionpost.com/wp-responder/

Description: Add a autopresponder to your blog with features like Aweber.

Version: 4.7

Author: Raj Sekharan   

Author URI: http://www.expeditionpost.com/

*/

include "wpr_install.php";

include "home.php" ;



include "newsletter.php";

include "autoresponder.php";



include "blog_series.php";

include "forms.php";

include "newmail.php";

include "customizeblogemail.php";



include "subscribers.php";



include "wpr_settings.php";

include "wpr_deactivate.php";

include "all_mailouts.php";



include "errors.php";

include "thecron.php";

include("lib/swift_required.php");

include "importexport.php";



error_reporting(0);


define("WPR_VERSION","11");

$address = get_option("wpr_address");

//-------------------------------------------DEBUG----------



if (!$address && is_admin())

{

	add_action("admin_notices","no_address_error");	

}

function wpr_services_notice()

{ //this images is used to announce new versions. DO NOT REMOVE THIS. 

	?>



<a href="http://www.expeditionpost.com/redirect.php"><img src="http://www.expeditionpost.com/wpresad-<?php echo WPR_VERSION; ?>.gif" /></a><br />

<?php

}



function no_address_error()

{

	echo '<div class="error fade" style="background-color:red; line-height: 20px;"><p><strong>You must set your address in the  <a href="' . admin_url( 'admin.phppage=wp-responder-email-autoresponder-and-newsletter-plugin/settings.php' ) . '">newsletter settings page</a> to avoid spam complaints and avoid the e-mails you send being flagged as spam. <br />

<br />

(and being sent to prison for breaking the law!) </strong></p></div>';



}

function _wpr_no_newsletters($message)

{

	global $wpdb;

	$query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters"; 

	$countOfNewsletters = $wpdb->get_results($query);

	$count = count($countOfNewsletters);

	unset($countOfNewsletters);

	if ($count ==0)

	{

		?>

<div class="wrap">

  <h2>No Newsletters Created Yet</h2>

</div>

<?php echo $message ?>, you must first create a newsletter. <br />

<br/>

<a href="admin.phppage=wp-responder-email-autoresponder-and-newsletter-plugin/newsletter.php&act=add" class="button">Create Newsletter</a>

<?php

		return true;

	}

	else

		return false;

}

function wpresponder_init_method() 
{
	//load the scripts only for the administrator.

	session_start();

	@ini_set( 'upload_max_size' , '100M' );

        @ini_set( 'post_max_size', '105M');

        @ini_set( 'max_execution_time', '300' );
		
		$option = get_option("timezone_string");
		
		date_default_timezone_set();

	if (is_admin())

	{

		wp_enqueue_script('jquery');

		wp_enqueue_script('wpresponder-uis',"/".PLUGINDIR."/wpresponder/jqueryui.js");

		wp_enqueue_style("wpresponder-ui-style","/".PLUGINDIR."/wpresponder/jqueryui.css");

		wp_enqueue_style("wpresponder-style","/".PLUGINDIR."/wpresponder/style.css");

                

                wp_register_script( "wpresponder-tabber", "/".PLUGINDIR."/wpresponder/tabber.js");

                wp_register_script( "wpresponder-ckeditor", "/".PLUGINDIR."/wpresponder/ckeditor/ckeditor.js");

                wp_register_script( "wpresponder-addedit", "/".PLUGINDIR."/wpresponder/script.js");

                add_action('admin_menu', 'wpresponder_meta_box_add');

        	add_action('edit_post', "wpr_edit_post_save");

        	add_action('publish_post', "wpr_add_post_save");

			



                $url = $_SERVER['REQUEST_URI'];

                if (preg_match("@post-new.php@",$url) || preg_match("@post.php@",$url))

                {

                    wp_enqueue_style("wpresponder-tabber","/".PLUGINDIR."/wpresponder/tabber.css");

                    wp_enqueue_script("wpresponder-tabber");

                    wp_enqueue_script("wpresponder-ckeditor");

                    wp_enqueue_script("wpresponder-addedit");

                    wp_enqueue_script("jquery");

                }

                if (preg_match("@newmail\.php@",$url) || preg_match("@autoresponder\.php@",$url))
                {

                    wp_enqueue_script("wpresponder-ckeditor");

                    wp_enqueue_script("jquery");

                }

		//if the current request is for exporting a csv then run the export!

		if (isset($_GET['page']) && $_GET['page'] == "wpresponder/importexport.php" && $_GET['action'] == "download")

		{

			export_csv();

			exit;

		}



                //add the add post area.

		

	}


}    



add_action('init', "wpresponder_init_method");

add_action('admin_menu', 'wpr_admin_menu');

add_action('wpr_cronjob','wpr_processEmails');

add_action('wpr_cronjob','wpr_processqueue');

register_activation_hook(__FILE__,"wpresponder_install");

register_deactivation_hook(__FILE__,"wpresponder_deactivate");



//

add_action('edit_post', array($aiosp, 'post_meta_tags'));

add_action('publish_post', array($aiosp, 'post_meta_tags'));

add_action('save_post', array($aiosp, 'post_meta_tags'));

add_action('edit_page_form', array($aiosp, 'post_meta_tags'));











$url = $_SERVER['REQUEST_URI'];



/*if (preg_match("@wpresponder/.*@",$url))

{

	//please don't remove this

	add_action("admin_notices","wpr_services_notice");

}*/

function wpr_admin_menu()

{

	add_menu_page('Newsletters','Newsletters',8,__FILE__);

	add_submenu_page(__FILE__,"Dashboard","Dashboard",8,__FILE__,"wpr_dashboard");

	add_submenu_page(__FILE__,'New Broadcast','New Broadcast',8,"wpresponder/newmail.php","wpr_newmail");

	add_submenu_page(__FILE__,'All Broadcasts','All Broadcasts',8,"wpresponder/allmailouts.php","wpr_all_mailouts");

	add_submenu_page(__FILE__,'Newsletters','Newsletters',8,"wpresponder/newsletter.php","wpr_newsletter");

        add_submenu_page(__FILE__,'Custom Fields','Custom Fields',8,"wpresponder/custom_fields.php","wpr_customfields");

	add_submenu_page(__FILE__,'Subscription Forms','Subscription Forms',8,"wpresponder/subscriptionforms.php","wpr_subscriptionforms");

	add_submenu_page(__FILE__,'Post Series','Post Series',8,"wpresponder/blogseries.php","wpr_blogseries");

	add_submenu_page(__FILE__,'Autoresponders','Autoresponders',8,"wpresponder/autoresponder.php","wpr_autoresponder");

	add_submenu_page(__FILE__,'Subscribers','Subscribers',8,"wpresponder/subscribers.php","wpr_subscribers");

	add_submenu_page(__FILE__,'Settings','Settings',8,"wpresponder/settings.php","wpr_settings");

	add_submenu_page(__FILE__,'Subscription Errors','Subscription Errors',8,"wpresponder/errors.php","wpr_errorlist");

	add_submenu_page(__FILE__,'Import/Export Subscribers','Import/Export Subscribers',8,"wpresponder/importexport.php","wpr_importexport");

}



function wpr_send_errors()

{

	global $wpdb;

	$query = "select * from ".$wpdb->prefix."wpr_errors where notified=0";

	$errors = $wpdb->get_results($query);

	ob_start();

	?>

<table>

  <?php

	foreach ($errors as $error)

	{

		?>

  <tr>

    <td><?php echo $error->error ?></td>

    <td><?php echo date("g:i d F Y",$error->time); ?></td>

  </tr>

  <?php

	}

	?>

</table>

<?php

	$errors = ob_get_clean();

	$message = "This e-mail was sent by the WP Responder plugin in your blog ".get_bloginfo("name")." (".get_bloginfo("siteurl")."). The following subscription errors were encountered during the past week. Please view this e-mail using a email client or service that supports viewing HTML e-mail. <br><br>$errors";

	include "class.phpmailer.php";

	$mailer = new PHPMailer;

	$mailer->AddAddress(get_bloginfo("admin_email"));

	$mailer->IsHtml(true);

	$mailer->MsgHTML($message);

	$mailer->Send();	

}



function wp_credits()

{

	?>

<br />

<br />

<div style="border: 1px solid #ccc; text-align:center; background-color:#e0e0e0; padding: 10px; margin-left:auto; margin-right:auto; width:500px;">Powered by <a href="http://www.expeditionpost.com/wp-responder/">WP Responder</a></div>

<?php

}







function wpr_replace_tags($sid,&$subject,&$body,$additional = array())

{

	global $wpdb;

	$query = "SELECT * FROM ".$wpdb->prefix."wpr_subscribers WHERE id='$sid'";

	$subscriber = $wpdb->get_results($query);

	$subscriber = $subscriber[0];

	$nid = $subscriber->nid;

	$query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters wehre id='$nid'";

	$newsletter = $wpdb->get_results($query);

	$newsletter = $newsletter[0];

	$parameters = array();

	//newsletter name

	$newsletterName = $newsletter->name;

	$parameters['newslettername'] = $newsletterName;

	$query = "SELECT * FROM ".$wpdb->prefix."wpr_custom_fields where nid='$nid'";

	$custom_fields = $wpdb->get_results($query);

	

	//blog name 

	$parameters['sitename'] = get_bloginfo("name");;

	//blog url

	$parameters['homeurl'] = get_bloginfo("home");

	//subscriber name

	$parameters['name'] = $subscriber->name;

	//the address of the sender (as required by can spam)

	$parameters['address'] = get_option("wpr_admin_address");

	//the email address

	$parameters['email'] = $subscriber->email;

	//admin email

	$parameters['adminemail'] = get_option('admin_email');

	

	$query = "select * from ".$wpdb->prefix."wpr_subscribers_$nid where id=$id;";

	$subscriber = $wpdb->get_results($query);

	$subscriber = $subscriber[0];

	

	//custom fields defined by the administrator

	foreach ($custom_fields as $custom_field)

	{

		$name = $custom_field->name;

		$parameters[$custom_field->name] = $subscriber->{$name};

	}

	

	$parameters = array_merge($parameters,$additional);

	

	foreach ($parameters as $name=>$value)

	{

		$subject = str_replace("[!$name!]",$value,$subject);

		$body =str_replace("[!$name!]",$value,$body);		

	}

	

}

/*

This function creates temporary tables to simplify the process of fetching the

subscribers and their custom field values from the database table.

*/

function wpr_make_subscriber_temptable($nid)

{

	global $wpdb;

	//create the main table for the other purposes.

	//get a list of all custom fields and then form the 

	

	$query = "select * from ".$wpdb->prefix."wpr_custom_fields where nid=$nid";

	$cfields = $wpdb->get_results($query);

	

	//get the columns of the subscribers table.

	$query = "show columns from ".$wpdb->prefix."wpr_subscribers";

	$columns = $wpdb->get_results($query);

	$subsTableColumnList = array();

	foreach ($columns as $column)

	{

		$subsTableColumnList[] = $column->Field;

	}

	

	$count = count($cfields);

	$finaltable = $count;

	$size = strlen(sprintf("%b",$count));

	$formatSpec = "%'0".$size."b";

	//used to specify the alias for the table in the table join to make the view.

	$fields = array();

	$tables = array();

	$args = array();

	$finaltable = sprintf($formatSpec,$finaltable);

	$mainTableAlias = str_replace("1","b",str_replace("0","a",$finaltable));

	if (count($cfields) >0)

	{

		foreach ($cfields as $num=>$cfield)

		{

			$name = $cfield->name;

			$number = sprintf($formatSpec,$num);

			//name of field

			$tableAlias = str_replace("1","b",str_replace("0","a",$number)); //replace 0=a , 1=b

			$table[$name] = $tableAlias;

			$fields[] = $tableAlias.".$name $name";

			$args[] = $tableAlias.".id=".$mainTableAlias.".id";

			

		}

	}

	$lastIndex = count($table)-1;

	

	//now to add the wp_wpr_subscribers table's columns.. i may change the structure later on.. so i do this.

	foreach ($subsTableColumnList as $name)

	{

		$fields[] = $mainTableAlias.".$name $name";

	}

	//the list of fields in the view.

	$fieldlist = implode(", ",$fields);

	$prefix = $wpdb->prefix;

	//the table names and their aliases

	$tablenames = array();

	if (count($table) > 0)

	{

		foreach ($table as $name=>$alias)

		{

			$tablenames[]  = $prefix."wpr_subscribers_".$nid."_".$name." $alias";

		}

	}

	

	$tablenames[] = $prefix."wpr_subscribers ".$mainTableAlias;



	if (count($tablenames) > 1)

		$tablenames = implode(", ",$tablenames);

	else

		$tablenames = $tablenames[0];

	if (count($args) > 0)

	{

		$joinsList = implode(" AND ",$args);

		$joiningConj = " AND ";

	}

	else

	{

		$joinsList = "";

		$joiningConj = "";

	}

	

	$joinsList .= $joiningConj.$mainTableAlias.".nid=$nid";

	

	$select = "SELECT $fieldlist FROM $tablenames WHERE $joinsList";



	$query = "CREATE TEMPORARY TABLE IF NOT EXISTS ".$prefix."wpr_subscribers_$nid as $select;";



	$wpdb->query($query);

}



function wpr_create_temporary_tables($nid)

{

	global $wpdb;

	$wpdb->show_errors();

	$customFieldListQuery = "SELECT * FROM ".$wpdb->prefix."wpr_custom_fields where nid=$nid";

	$customFields = $wpdb->get_results($customFieldListQuery);

	if (count($customFields ) >0 )

	{

		foreach ($customFields as $field)

		{

			$name = $field->name;

			$query = "CREATE TEMPORARY TABLE IF NOT EXISTS ".$wpdb->prefix."wpr_subscribers_".$nid."_".$name." as SELECT a.sid id, a.value $name from ".$wpdb->prefix."wpr_custom_fields_values a, ".$wpdb->prefix."wpr_custom_fields b where a.nid=$nid and a.cid=b.id and b.name='$name';";

			$wpdb->query($query);

		}

		

		return true;

	}

	else

	{

		return false;

	}

}



function wpr_error($error)

{

	global $wpdb;

	$query = "insert into ".$wpdb->prefix."wpr_errors (time,error,notified) values (".time().",'$error',0);";

	$wpdb->query($query);

}

function wpr_place_tags($sid,&$strings,$additional=array())

{

	global $wpdb;

	$query = "SELECT * FROM ".$wpdb->prefix."wpr_subscribers WHERE id='$sid'";

	$subscriber = $wpdb->get_results($query);

	$subscriber = $subscriber[0];

	$nid = $subscriber->nid;

	$id = $subscriber->id;

	

	$query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters where id='$nid'";

	$newsletter = $wpdb->get_results($query);

	$newsletter = $newsletter[0];

	$parameters = array();

	//newsletter name

	$newsletterName = $newsletter->name;

	$parameters['newslettername'] = $newsletterName;

	$query = "SELECT * FROM ".$wpdb->prefix."wpr_custom_fields where nid='$nid'";

	$custom_fields = $wpdb->get_results($query);

	

	//blog name 

	$parameters['sitename'] = get_bloginfo("name");d;

	//blog url

	$parameters['homeurl'] = get_bloginfo("home");

	//subscriber name

	$parameters['name'] = $subscriber->name;

	//the address of the sender (as required by can spam)

	$parameters['address'] = get_option("wpr_admin_address");

	//the email address

	$parameters['email'] = $subscriber->email;

	//admin email

	$parameters['adminemail'] = get_option('admin_email');

	//custom fields defined by the administrator

	$query = "select * from ".$wpdb->prefix."wpr_subscribers_$nid where id=$id;";

	$subscriber = $wpdb->get_results($query);

	$subscriber = $subscriber[0];

	

	foreach ($custom_fields as $custom_field)

	{

		$name = $custom_field->name;

		$parameters[$custom_field->name] = $subscriber->{$name};

	}

	$parameters = array_merge($parameters,$additional);

	

	foreach ($parameters as $tag=>$value)

	{

		foreach ($strings as $index=>$string)

		{

			$strings[$index] = str_replace("[!$tag!]",$value,$string);

		}

	}

}



function getMailTransport()

{

     $isSmtpOn = (get_option("wpr_smtpenabled")==1)?true:false;

        //get the proper email transport to use.

     if ($isSmtpOn)

            {



            $smtphostname = get_option("wpr_smtphostname");

            $smtpport = get_option("wpr_smtpport");



            $doesSmtpRequireAuth = (get_option("wpr_smtprequireauth")==1)?true:false;

            $isSecureSMTP = (in_array(get_option("wpr_smtpsecure"),array("ssl","tls")))?true:false;

            $smtpsecure = get_option("wpr_smtpsecure");



            $transport = Swift_SmtpTransport::newInstance();



            $transport->setHost($smtphostname);

            $transport->setPort($smtpport);

            if ($doesSmtpRequireAuth)

                {



                $smtpusername = get_option("wpr_smtpusername");

                $smtppassword = get_option("wpr_smtppassword");

                $transport->setUsername($smtpusername);

                $transport->setPassword($smtppassword);



                }

                if ($isSecureSMTP)

                    {

                    $transport->setEncryption(get_option('wpr_smtpsecure'));

                }

        }

        else

            {



                $transport = Swift_MailTransport::newInstance();

            }





            return $transport;

}

function wpr_processqueue()
{

	global $wpdb;		

	$hourlyLimit = get_option("wpr_hourlylimit");

	$hourlyLimit = (int) $hourlyLimit;

	$limitClause = ($hourlyLimit ==0)?"":" limit ".$hourlyLimit;


	$query = "SELECT * FROM ".$wpdb->prefix."wpr_queue where sent=0 $limitClause ";

	$results = $wpdb->get_results($query);

	foreach ($results as $mail)  

	{

		$mail = (array) $mail;
		
        dispatchEmail($mail);

		$query = "UPDATE ".$wpdb->prefix."wpr_queue set sent=1 where id=".$mail['id'];

		$wpdb->query($query);

	}

}

/*

 * The function that actually sends the email

 *

 * Arguments : $mail = array(

 *                             to = The recipient's email address

 *                             from = The from email address

 *                             fromname = The nice name from which the email is sent

 *                             htmlbody = The html body of the email

 *                             textbody = The text body of the email

 *                             htmlenabled = Whether the html body of the email is enabled

 *                                           1 = Yes, the html body is enabled

 *                                           0 = No, the html body is disabled.

 *                             attachimages = Whether the images are to be attached to the email

 *                                           1 = Yes, attach the images

 *                                           0 = No,  don't attach the images

 *

 */

function dispatchEmail($mail)

{


                $transport = getMailTransport();

                $mailer = Swift_Mailer::newInstance($transport);

                

                $message = Swift_Message::newInstance($mail['subject']);

                

		$message->setFrom(array($mail['from']=>$mail['fromname']));

		$message->setTo(array($mail['to']));

                //add a html body only if the 

           
		
                

		if ($mail['htmlenabled']==1 && !empty($mail['htmlbody']))

		{
			


		 	if ($mail['attachimages'] == 1)
		 	{
		 	 	attachImagesToMessageAndSetBody($message,$mail['htmlbody']);

		 	}
		 	else
		 	{
		 	 	$message->setBody($mail['htmlbody'],'text/html');

		 	}

		 	$message->addPart($mail['textbody'],'text/plain');

		}

		else

		{

		 	$message->setBody($mail['textbody'],'text/plain');

		}

		$mailer->batchSend($message);

}





function attachImagesToMessageAndSetBody(&$message,$body)

{

	$imagesInMessage = getImagesInMessage($body);



	foreach ($imagesInMessage as $imageUrl)

	{

		$cid = $message->embed(Swift_Image::fromPath($imageUrl));

		$body = str_replace($imageUrl,$cid,$body);

	}

	$message->setBody($body,'text/html');

}



function getImagesInMessage($message)

{

	$startPos = 0;

	$list = array();
        $message = " $message"; //if the image tag is at position 0, the loop will not even start. 




	while (strpos($message,"<img",$startPos))
	{
                

		$start = strpos($message,"<img",$startPos);

		$end = strpos($message,">",$start+4);

		$startPos = $end;
             

			//find the src="

		if ($end)

		{

			$begin = strpos($message,"src=\"",$start);

			$end = strpos($message,"\"",$begin+5);

			$theURL = substr($message,$begin+5,$end-$begin-5);

			if ($theURL[0] == "/") //then we have a relative path. attach the blog's hostname in the beginning.

			{

				$url = str_replace("http://","",get_option("siteurl"));

				$url = explode("/",$url);

				

				$theURL = "http://".$url[0].$theURL;

			}

			else if (strpos($theURL,"http://") > 0) //probably a relative path to the blog root.

			{

				$theURL = get_option("siteurl")."/".$theURL;

			}

			$list[] = $theURL;

		}

		else

		{

			$startPos = $start+4; // an opening image tag without a closing '>' ? then we skip that image.

			continue;

		}

	}

        ob_start();

        print_r($list);

        $contents = ob_get_contents();
        ob_end_clean();

        


	return array_unique($list);

}



function email($to,$subject,$body)

{

	$transport = getEmailTransport();

	$message = Swift_message::newInstance($subject);

	$message->setFrom(array(get_option("admin_email")=>get_option("blogname")));

	$message->setTo($to);

	$message->setBody($body);

	$message->batchSend();

}
