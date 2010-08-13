<?php
/*
Plugin Name: WP Responder
Plugin URI: http://www.wpresponder.com
Description: Add follow-up autoresponder and newsletter features to your wordpress blog. 
Version: 4.9.4.2
Author: Raj Sekharan
Author URI: http://www.expeditionpost.com/
*/

if (!defined("WPR_DEFS"))
{
     define("WPR_DEFS",1);
     function wpr_unsupported()
     {
		if (current_user_can('level_8'))
		{
	?>
<div class="error fade" style="background-color:red; line-height: 20px;">
  <p><strong>Your web server is running PHP 4. WP Responder is not programmed to work with PHP 4.x. To prevent damage to your website please deactivate WP Responder from the <a href="<?php bloginfo("home") ?>/wp-admin/plugins.php">Plugins</a> page. </strong></p>
</div>
<?php
		}
    }
	
	$phpVersion = phpversion();
     if (preg_match("@4\.[0-9\.]*@",$phpVersion))
     {
                add_action('admin_notice',"wpr_unsupported");
     }
     else
	 {
	    $plugindir =  str_replace(basename(__FILE__),"",__FILE__);
        $plugindir = str_replace("\\","/",$plugindir);
        $plugindir = rtrim($plugindir,"/");

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
		include "actions.php";
		include "runcronnow.php";
		include "errors.php";
		include "thecron.php";
		include $plugindir."/lib/swift_required.php";
		include "importexport.php";
		include "widget.php";
		define("WPR_VERSION","4.9.4.2");

	
	//-------------------------------------------DEBUG----------
	
	$currentDir = str_replace("wpresponder.php","",__FILE__);
	$plugindir = basename($currentDir);
	define("WPR_PLUGIN_DIR","$plugindir");
	
	function whetherToNag()
	{
		$address = get_option("wpr_address");		
		if (!$address && is_admin() && current_user_can('level_8'))  
		{
			add_action("admin_notices","no_address_error");	
		}
	}
	
	add_action("plugins_loaded","whetherToNag");
	
	function wpr_services_notice()
	{ //this images is used to announce new versions. DO NOT REMOVE THIS. 
	?>
<a href="http://www.expeditionpost.com/redirect.php"><img src="http://www.expeditionpost.com/wpresad-<?php echo WPR_VERSION; ?>.gif" /></a><br />
<?php
	}
	
	function no_address_error()
	{
	
		echo '<div class="error fade" style="background-color:red; line-height: 20px;"><p><strong>You must set your address in the  <a href="' . admin_url( 'admin.php?page=wpresponder/settings.php' ) . '">newsletter settings page</a> to avoid spam complaints and avoid the e-mails you send being flagged as spam. <br />
	
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
<a href="admin.php?page=wpresponder/newsletter.php&act=add" class="button">Create Newsletter</a>
<?php
	
			return true;
	
		}
	
		else
	
			return false;
	
	}
	
	
	function wpr_enqueue_post_page_scripts()
	{
		
		if (isset($_GET['post_type']) && $_GET['post_type'] == "page")
		{
			return;		
		}
		$directory = str_replace("wpresponder.php","",__FILE__);
		$containingdirectory = basename($directory);
		 wp_enqueue_style("wpresponder-tabber","/".PLUGINDIR."/".$containingdirectory."/tabber.css");
		 wp_enqueue_script("wpresponder-tabber");
		 wp_enqueue_script("wpresponder-ckeditor");
		 wp_enqueue_script("wpresponder-addedit");		 
		 wp_enqueue_script("jquery");
	}
	
	function wpr_enqueue_admin_scripts()
	{
		if (is_admin() && current_user_can('level_8'))
		{
			wp_enqueue_script('jquery');
			$directory = str_replace("wpresponder.php","",__FILE__);
			$containingdirectory = basename($directory);
			if (preg_match("@wp-responder-email-autoresponder-and-newsletter-plugin/[^.]*.php@",$_SERVER['REQUEST_URI']) || preg_match("@wpresponder/[^.]*.php@",$_SERVER['REQUEST_URI']))
			{
					wp_enqueue_script('wpresponder-uis',"/".PLUGINDIR."/".$containingdirectory."/jqueryui.js");
					wp_enqueue_style("wpresponder-ui-style","/".PLUGINDIR."/".$containingdirectory."/jqueryui.css");		
					wp_enqueue_style("wpresponder-style","/".PLUGINDIR."/".$containingdirectory."/style.css");
			}
			$url = $_SERVER['REQUEST_URI'];
			if (preg_match("@newmail\.php@",$url) || preg_match("@autoresponder\.php@",$url)|| preg_match("@allmailouts\.php\&action=edit@",$url))
			{
				wp_enqueue_script("wpresponder-ckeditor");
				wp_enqueue_script("jquery");
			}
			//if the current request is for exporting a csv then run the export!				
			//add the add post area.
		}
	}
	
	function wpresponder_init_method() 
	{
		//load the scripts only for the administrator.
		global $current_user;
		session_start();		
		
		/*     Attaching the functions to the Crons     */
		add_action('admin_menu', 'wpr_admin_menu');
		//the cron that processes emails
		add_action('wpr_cronjob','wpr_processEmails');
		//the cron that delivers email. 
		add_action('wpr_cronjob','wpr_processqueue');
		//the tutorial series
		add_action('wpr_tutorial_cron','wpr_process_tutorial');
		//the cron that delivers plugin updates
		add_action('wpr_updates_cron','wpr_process_updates');

                
	
		//whats the point you ask? 
		@ini_set( 'upload_max_size' , '100M' );
		@ini_set( 'post_max_size', '105M');
		@ini_set( 'max_execution_time', '300' );


                if (isset($_GET['page']) && preg_match("@^wpresponder/.*@",$_GET['page']))
                {
                    _wpr_dispatch_call();
                }
			
			$option = get_option("timezone_string");
			
			//a visitor is trying to subscribe.
			if (isset($_GET['wpr-optin']) && $_GET['wpr-optin'] == 1)
			{
				require "optin.php";			
				exit;
			}
			
			if (isset($_GET['wpr-optin']) && $_GET['wpr-optin'] == 2)
			{
				require "verify.php";	
				exit;
			}
			
			//a subscriber is trying to confirm their subscription. 
			if (isset($_GET['wpr-confirm']) && $_GET['wpr-confirm']!=2)
			{
				include "confirm.php";			
				exit;
			}
			$directory = str_replace("wpresponder.php","",__FILE__);
			$containingdirectory = basename($directory);
			wp_register_script( "wpresponder-tabber", "/".PLUGINDIR."/".$containingdirectory."/tabber.js");
			wp_register_script( "wpresponder-ckeditor", "/".PLUGINDIR."/".$containingdirectory."/ckeditor/ckeditor.js");
			wp_register_script( "wpresponder-addedit", "/".PLUGINDIR."/".$containingdirectory."/script.js");
			if (isset($_GET['wpr-confirm']) && $_GET['wpr-confirm']==2)
			{
				include "confirmed.php";
				exit;
			}
			
			if (isset($_GET['wpr-manage']))
			{
				include "manage.php";
				exit;
			}
			
			if (isset($_GET['wpr-admin-action']) )
			{
				switch ($_GET['wpr-admin-action'])
				{
					case 'preview_email':
						include "preview_email.php";
						exit;
					break;
					case 'view_recipients':
						include("view_recipients.php");
						exit;
					break;
					case 'filter':
						include("filter.php");
						exit;
					break;
					case 'delete_mailout':
					
					include "delmailout.php";
					exit;
					
					break;
					case '':
					
					break;
					
				}
				
			}
			
			
			if (isset($_GET['wpr-template']))
			{
				include "templateproxy.php";
				exit;
				
			}
		
		 add_action('admin_init','wpr_enqueue_admin_scripts');
		 add_action('admin_menu', 'wpresponder_meta_box_add');
		 add_action('edit_post', "wpr_edit_post_save");
		 add_action('load-post-new.php','wpr_enqueue_post_page_scripts');
		 add_action('admin_action_edit','wpr_enqueue_post_page_scripts');
		 add_action('publish_post', "wpr_add_post_save");
		
		if (isset($_GET['page']) && $_GET['page'] == "wpresponder/importexport.php" && $_GET['action'] == "download")
		{
			export_csv();
			exit;
		}
	
	}    
	add_action('widgets_init','wpr_widgets_init');
	add_action('init', "wpresponder_init_method");
	register_activation_hook(__FILE__,"wpresponder_install");
	register_deactivation_hook(__FILE__,"wpresponder_deactivate");
        
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
                add_submenu_page(__FILE__,'Actions','Actions',8,"wpresponder/actions.php","wpr_actions");
		add_submenu_page(__FILE__,'Settings','Settings',8,"wpresponder/settings.php","wpr_settings");
		add_submenu_page(__FILE__,'Import/Export Subscribers','Import/Export Subscribers',8,"wpresponder/importexport.php","wpr_importexport");
		add_submenu_page(__FILE__,'Run CRON','Run WPR Cron',8,"wpresponder/runcronnow.php","wpr_runcronnow_start");
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

	        $mail['textbody'] = stripslashes($mail['textbody']);
			if ($mail['htmlenabled']==1 && !empty($mail['htmlbody']))
			{
				
	            $mail['htmlbody'] = stripslashes($mail['htmlbody']);
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
	
	
	
	
	function wpr_cronschedules()
	{
			$schedules['every_five_minutes'] = array(
				 'interval'=> 300,
				 'display'=>  __('Every 5 Minutes')
				  );
			$schedules ['every_half_hour'] = array(
												   'interval'=>1800,
												   'display'=>__('Every Half an Hour')
												   );
			 return  $schedules;
	}
        
        add_filter('cron_schedules','wpr_cronschedules');
	function wpr_get_unsubscription_url($sid)
	{
			$baseURL = get_bloginfo("home");
			$subscriber = _wpr_subscriber_get($sid);
			$newsletter = _wpr_newsletter_get($subscriber->nid);
			$nid = $newsletter->id;
			$string = $sid."%$%".$nid."%$%".$subscriber->hash;
			$codedString = base64_encode($string);
			$unsubscriptionUrl = $baseURL."/?wpr-manage=$codedString";
			return $unsubscriptionUrl;
	}

	function sendConfirmedEmail($id)
	{
		global $wpdb;
		$query = "select * from ".$wpdb->prefix."wpr_subscribers where id=$id";
		$sub = $wpdb->get_results($query);
		$sub  = $sub[0];
		//get the confirmation email and subject from newsletter

		$newsletter = _wpr_newsletter_get($sub->nid);

		$confirmed_subject = $newsletter->confirmed_subject;

		$confirmed_body = $newsletter->confirmed_body;

		//if a registered form was used to subscribe, then override the newsletter's confirmed email.

		$sid = $sub->id; //the susbcriber id
		$unsubscriptionURL = wpr_get_unsubscription_url($sid);

		$unsubscriptionInformation = "\n\nTo manage your email subscriptions or to unsubscribe click on the URL below:\n$unsubscriptionURL\n\nIf the above URL is not a clickable link simply copy it and paste it in your web browser.";


		$fid = $args[2];
		$query = "SELECT a.* from ".$wpdb->prefix."wpr_subscription_form a, ".$wpdb->prefix."wpr_subscribers b  where a.id=b.fid and b.id=$sid;";

		$form = $wpdb->get_results($query);
		if (count($form))
		{
			 $confirmed_subject = $form[0]->confirmed_subject;
			 $confirmed_body = $form[0]->confirmed_body;
		}



		$confirmed_body .= $unsubscriptionInformation;

		$params = array($confirmed_subject,$confirmed_body);

		wpr_place_tags($sub->id,$params);

		$fromname = $newsletter->fromname;
		if (!$fromname)
		{
			$fromname = get_bloginfo('name');
		}

		$fromemail = $newsletter->fromemail;
		if (!$fromemail)
		{
			$fromemail = get_bloginfo('admin_email');
		}

		$email = $sub->email;
		$emailBody = $params[1];
		$emailSubject = $params[0];




		$mailToSend = array(
								'to'=>$email,
								'fromname'=>  $fromname,
								'from'=> $fromemail,
								'textbody' => $emailBody,
								'subject'=> $emailSubject,
							);



		dispatchEmail($mailToSend);
	}

	function wpr_enable_tutorial()
	{


		$isItEnabled = get_option("wpr_tutorial_active");
		if (empty($isItEnabled))
		{
			//enabling the tutorial for the first time.
			add_option('wpr_tutorial_active','on');
			add_option('wpr_tutorial_activation_date',time());
			add_option('wpr_tutorial_current_index',"0");//set the index to zero.

			//schedule the cron to run once every day.
		}
		else
		{
			delete_option('wpr_tutorial_active');
			add_option('wpr_tutorial_active','on');
		}

		wp_schedule_event(time()+86400, 'daily' ,  "wpr_tutorial_cron");
	}

	function wpr_disable_tutorial()
	{
		$currentStatus = get_option("wpr_tutorial_active");
		//if the tutorial series is already off then do nothing.
		if ($currentStatus == 'off')
		{
			 return false;
		}
		//if the tutorial serise is on. then turn it off
		if ($currentStatus == "on")
		{
			delete_option('wpr_tutorial_active');
			add_option('wpr_tutorial_active','off');
		}
		//if for some reason the option is missing, then create it and then set it to off.
		if (empty($currentStatus))
		{
			add_option('wpr_tutorial_active','off');
		}
		wp_clear_scheduled_hook('wpr_tutorial_cron');
		return true;
	}

	/*
	Dispatch tutorial series to the user.
	*/

	function wpr_process_tutorial()
	{
		$isTutorialSeriesActive = get_option('wpr_tutorial_active');
		//double check before starting to check for a new post.
		if ($isTutorialSeriesActive == "on")
		{
			$theTutorialArticles = fetch_feed("http://www.wpresponder.com/tutorial/feed/");
			if (is_wp_error($theTutorialArticles)) //no feed? do nothing. leave it.
			{
				return false;
			}
			else
			{
				//get the index of the last email that was sent:
				$indexOfEmailLastSent = (int) get_option('wpr_tutorial_current_index');
				$numberOfTutorialArticles = $theTutorialArticles->get_item_quantity();

				if ($indexOfEmailLastSent < $numberOfTutorialArticles) //we have a new post to send.
				{
					$indexOfPostToSend = $indexOfEmailLastSent + 1;
					$items = $theTutorialArticles->get_items();
					$theArticle = $items[$indexOfPostToSend-1];
					$theTitle = $theArticle->get_title();
					$theContent = $theArticle->get_content();
					$theURL = $theArticle->get_link();
					$theEmailAddress = getNotificationEmailAddress();


					$mail = array(   'to'=> $theEmailAddress,
									 'from'=> get_bloginfo('admin_email'),
									 'fromname'=> 'WP Responder Tutorial',
									 'subject'=> $theTitle,
									 'htmlbody'=> $theContent,
									 'htmlenabled'=>1,
									 'attachimages'=>1
									 );
					dispatchEmail($mail);
					delete_option('wpr_tutorial_current_index');
					add_option('wpr_tutorial_current_index',$indexOfPostToSend);
					return true;
				}
			}
		}
		else
		{
			return false;
		}
	}

	function createNotificationEmail()
	{

		$not_email = get_option('wpr_notification_custom_email');
		if (empty($not_email))
			add_option('wpr_notification_custom_email','admin_email');
		else
			return false;
	}

	function wpr_enable_updates()
	{

		$updatesOption = get_option('wpr_updates_active');

		if (empty($updatesOption))
		{
			add_option('wpr_updates_active','on');
		}
		//set the date to current date.
		delete_option('wpr_updates_lastdate');
		add_option('wpr_updates_lastdate',time());
		//schedule the cron to run daily.
		wp_schedule_event(time()+86400,'daily','wpr_updates_cron');
	}

	function wpr_disable_updates()
	{
		delete_option('wpr_updates_active');
		add_option('wpr_updates_active','off');
		wp_clear_scheduled_hook('wpr_updates_cron');
	}

	function wpr_process_updates()
	{

		//double check
		$updatesEnabled = get_option('wpr_updates_active');
		if ($updatesEnabled == 'on')
		{
			//fetch the updates feed
			$updatesfeed = fetch_feed('http://www.wpresponder.com/updates/feed/');

			if (is_wp_error($updatesfeed))
			{
				return false;
			}
			else
			{

				//loop through the list of items and then deliver only the last update that is new.
				$numberOfItems = $updatesfeed->get_item_quantity();
				$items = $updatesfeed->get_items();

				$lastDate = get_option('wpr_updates_lastdate');
				$dateOfLatestPost = $lastDate;


				$postToDeliver = false;

				//this loop loops through all the items in the feed and then delivers the latest possible post.


				foreach ($items as $item)
				{
					$itemDate = $item->get_date();
					$itemDateStamp = strtotime($itemDate);
					if ($dateOfLatestPost < $itemDateStamp) //
					{
						$dateOfLatestPost = $itemDateStamp;
						$postToDeliver = $item;
					}
					$debug .= "\nNope..";
				}//end for loop to loop through the feed items.

				if ($postToDeliver != false)
				{
					//deliver the latest post.
					$title = $postToDeliver->get_title();
					$theBody = $postToDeliver->get_content();
					$notificationEmail = getNotificationEmailAddress();

					$mail = array(   'to' => $notificationEmail,
								  	 'from'=> get_bloginfo('admin_email'),
									 'fromname'=> 'WP Responder Updates',
									 'subject'=>$title,
									 'htmlbody'=>$theBody,
									 'htmlenabled'=>1,
									 'attachimages'=>1
								);
					//ob_start();
					dispatchEmail($mail);
					delete_option('wpr_updates_lastdate');
					add_option('wpr_updates_lastdate',$dateOfLatestPost);
					return true;
				}//end - if the post is to be delivered.

			}//end - if the field is available


		}//end if updates are on.
		else
		{
			return false;
		}
	}//end defintion of wpr_process_updates

	function getNotificationEmailAddress()
	{
		$emailAddress = get_option('wpr_notification_custom_email');
		if (empty($emailAddress))
		{
				add_option('wpr_notification_custom_email','admin_email');
		}
		if ($emailAddress != 'admin_email')
			return $emailAddress;
		else
			return get_bloginfo('admin_email');
	}

        function wpr_widgets_init()
        {
            return register_widget("WP_Subscription_Form_Widget");
        }



        function _wpr_dispatch_call()
        {

            if (count($_POST)>0 && isset($_POST['wpr_form']))
            {
                $formName = $_POST['wpr_form'];
                $actionName = "_wpr_".$formName."_post";
                do_action($actionName);
                
            }
        }


    }
} 