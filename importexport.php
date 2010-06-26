<?php
function path_to_this_page()
{
	return "admin.php?page=wp-responder-email-autoresponder-and-newsletter-plugin/importexport.php";
}
function wpr_importexport()
{
	global $wpdb;
	$action = @$_GET['action'];
	switch ($action)
	{
		case 'wizard':
			wizard();
		break;
		case 'download':
		export_csv();
		break;
		default:
		display_newsletter_list();
		show_link_to_wizard();		
	}
}

function export_csv()
{
	global $wpdb;
	$prefix = $wpdb->prefix;
	//is there a 
	$nid =(int) $_GET['nid'];
	if ($nid == 0)
		return;
	//does the newsletter exist?
	$query = "SELECT COUNT(*) FROM ".$prefix."wpr_newsletters where id=$nid";
	$results = $wpdb->get_results($query);
	if (count($results) == 0)
	{
		return;
	}
	//if none of these error conditions occur, then start exporting:
	
	
	//fetch all custom fields associates with this newsletter
	$query = "select * from ".$prefix."wpr_custom_fields where nid=$nid";
	$results = $wpdb->get_results($query);
	$fieldHeaders = array();
	if (count ($results))
	{
		$customfields= array();
		foreach ($results as $field)
		{
			$fieldName = $field->label."__".base64_encode($field->name."%sep%".$field->type."%sep%".$field->enum);
			$fieldHeaders[] = $fieldName;
			$customfields[] = $field->name;
			
		}
	}
	//add the name and email address fields to the beginning of the field list
	array_unshift($fieldHeaders,"email");	
	array_unshift($fieldHeaders,"name");
	$customFieldsList .= implode(",",$fieldHeaders);
	if (count($customfields))
	{
		$SqlQueryColumnList = ",".implode(",",$customfields); //this will be appended to the column list in the  fetchAllSubscriberDataQuery sql query
	}

	//the query that returns all the custom fields
	
	//these two lines create a temporary table that has all the fields of the subscriber table
	//joined with the values of the custom fields for each of the subscribers in that table.
	//the custom fields' values for each subscriber are created as a column in the wpr_subscriber_$nid table.
	wpr_create_temporary_tables($nid);
	wpr_make_subscriber_temptable($nid);
	
	
	//fetch the subscriber data for all subscribers
	$fetchAllSubscriberDataQuery = "SELECT name,email $SqlQueryColumnList FROM ".$prefix."wpr_subscribers_$nid";
	$listOfSubscribersAndTheirInfo = $wpdb->get_results($fetchAllSubscriberDataQuery);
	//the field headings are first attached
	$output = $customFieldsList."\n";
	$fieldname = "";

	//now the data for each row is written
	foreach ($listOfSubscribersAndTheirInfo as $subscriber)
	{
		$subsarray = (array) $subscriber;
		//array walk doesnt work for some reason.
		foreach ($subsarray as $name=>$value )
		{
			$subsarray[$name] = trim($subsarray[$name]);
		}
		$row = implode(",",$subsarray);
		$output .= $row."\n";
	}
	
	header ("Content-disposition: attachment; filename=export_$nid.csv");
	echo $output;
	exit;
	
}

function redirect($url)
{
	if (empty($url))
		return;
	echo "<script> 
	window.location='".$url."';
    </script>";
}

function step1()
{
	global $wpdb;
        
	
	$output = '<form action="admin.php?page=wp-responder-email-autoresponder-and-newsletter-plugin/importexport.php&action=wizard&step=1" method="post">';
	$nonce = wp_create_nonce('firststep_wpnonce');
	$output .= '<input type="hidden" name="firststep_wpnonce" value="'.$nonce.'"/>';
	$output .= "<h2>Step 1: Select the newsletter</h2>Please select the newsletter to which you want to import the subscribers";
	$query = "select * from ".$wpdb->prefix."wpr_newsletters";
	$newsletters = $wpdb->get_results($query);
	$output .= '<p><select name="nid">';
	foreach ($newsletters as $name=>$newsletter)
	{
		$output .='<option value="'.$newsletter->id.'">'.$newsletter->name."</option>";
	}
	$output .= "</select></p>";
		$output .= '
<p><input type="submit" name="Submit" value="Next &raquo;"/></p>';
	$output .= "</form>";
	echo $output;	
}

function step2()
{
	global $wpdb;
	$rootpath = realpath("..");        
	$output = include($rootpath."/".PLUGINDIR."/wp-responder-email-autoresponder-and-newsletter-plugin/import.php");
	echo $output;
}

function step4()
{
	global $wpdb;
	$rootpath = realpath("..");
	$output = include($rootpath."/".PLUGINDIR."/wp-responder-email-autoresponder-and-newsletter-plugin/import3.php");
	echo $output;
}

function handleStep2(&$moveToStep3)
{
	$followupSubscription = $_POST['followup'];
	$blogsubscription = $_POST['blogsubscription'];
	switch ($followupSubscription)
	{
		case 'autoresponder':
		$_SESSION['importwizard_followup'] = "followup";
		$_SESSION['importwizard_fid'] = $_POST['aid'];
		break;
		
		case 'postseries':
		$_SESSION['importwizard_followup'] = "postseries";
		$_SESSION['importwizard_fid'] = $_POST['postseries'];
		break;
		case 'none':
		$_SESSION['importwizard_followup'] = "none";
		break;
	}
	
	switch ($blogsubscription)
	{
		case 'all':
			$_SESSION['importwizard_blogsubscription'] = 'all';
			break;
		case 'cat':
			$_SESSION['importwizard_blogsubscription'] = 'cat';
			$_SESSION['importwizard_blogcats'] = $_POST['catlist'];
			break;
		default:
			$_SESSION['importwizard_blogsubscription'] = "no";
	}
	$moveToStep3 = true;
}
function handleStep1(&$moveToStep2)
{
	global $wpdb;
	$newsletter = (int) $_POST['nid'];
	//does a newsletter exist?
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters where id=$newsletter";
	$results = $wpdb->get_results($query);
	if (count($results) == 0)
	{
		$moveToStep2 = false;
	}
	else
	{
		$_SESSION['importwizard_newsletter']= $_POST['nid'];	
		$moveToStep2 = true;
	}
	
}

function step3($error="")
{
	global $wpdb;
	$rootpath = realpath("..");
	$output = include($rootpath."/".PLUGINDIR."/wp-responder-email-autoresponder-and-newsletter-plugin/import2.php");
	echo $output;
}

function handleStep3(&$moveToStep4,&$error)
{
	if (!isset($_POST['type'])) // if the csv file format isnt set.
	{
		$error = "You haven't selected a csv file format. Please select if you are importing from feedburner or from another WP Responder installation";
		$moveToStep4=false;
		return;
	}
	$errorNum = (int)$_FILES['feedimport']['error'];
	switch ($errorNum)
	{
		case 0:
		switch ($_POST['type'])
		{
			case 'feedburner':
			$moveToStep4 = importFromFeedBurner();
			break;
			case 'wpresponder':
			$moveToStep4 = importFromWpResponder();
			break;
		}
		break;
		case 4:
			$error = "You didn't upload a file. Please upload a file to import subscribers.";
		break;
		default:
		$error = "There was a file upload error. The file was too big for this server.";
		break;
	}
}

function importFromFeedBurner()
{
	global $wpdb;
	
	//how do i validate if the file is from wp
	
	$fp = fopen($_FILES['feedimport']['tmp_name'],"r");
	$nid = $_SESSION['importwizard_newsletter'];
	fgetcsv($fp); //read out the first line that has the headings.
	$date = time();
	while (!feof($fp))
	{
		$fields = fgetcsv($fp);
		$email = $fields[0];
		$status = $fields[2];
		$time = mktime($fields[1]);
		$tableName = $wpdb->prefix."wpr_subscribers";
		if ($status != "Active")
			continue;
		//check if the subscriber exists in database, if he does, activate him/her

		$query = "SELECT * FROM $tableName where email='$email' and nid='$nid'";
		$results = $wpdb->get_results($query);
		if (count($results))			
		{
			//don't insert into databse if the email address already exists in database. instead update the subscription status to subscribed
			$hash = microtime();
			$hash .= rand(rand(10221,26432),rand(26432,54223)); //some really really random number..
			$hash = md5($hash);
			$query = "UPDATE $tableName set active=1 , confirmed=1, hash = '$hash' where nid='$nid' and email='$email'";
			$wpdb->query($query);
			
		}
		else
		{
			//this is a new subscriber
			$hash = microtime();
			$hash .= rand(rand(10221,26432),rand(26432,54223)); //some really really random number..
			$hash = md5($hash);
			$query = "INSERT INTO $tableName (nid, name, email,date, active, hash, confirmed,fid) values ($nid,'$name','$email',$time,1,'$hash',1,-1);";
			$wpdb->query($query);
		}
		//enable the corresponding follow up subscription and blog subscription
		if ($_SESSION['importwizard_followup'] != "none" )
		{
				//get the subscriber id
			$query = "SELECT * FROM ".$wpdb->prefix."wpr_subscribers where email='$email' and nid='$nid';";
			$results = $wpdb->query($query);
			$id = $results[0]->id;
			
			//insert the blog subscription
			switch ($_SESSION['importwizard_blogsubscription'])
			{
				case 'all':
				addBlogSubscription($id);
				break;
				case 'cat':
				foreach ($_SESSION['importwizard_blogcats'] as $categoryId)
				{
					addBlogCategorySubscription($id,$categoryId);
				}
				
			}
			
			switch ($_SESSION['importwizard_followup'])
			{
				case 'followup': //subscribe to autoresponder
					$autoresponderId = $_SESSION['importwizard_fid'];
					addAutoresponderSubscription($id,$autoresponderId);
				break;
				case 'postseries':
					$postSeriesId = $_SESSION['importwizard_fid'];
					addPostSeriesSubscription($id,$postSeriesId);
				break;
			}
			
			
		}
	}
	clearImportSessionVariables();
	return true;
}
/*

The order of the custom fields is very important in this function. That is one of the assumptions,
very risky assumptions I made while writing this function.

*/
function importFromWpResponder()
{
	global $wpdb;
	$prefix = $wpdb->prefix;
	$content = file($_FILES['feedimport']['tmp_name']);
	$fields = array_shift($content); //the first line always has all the field names 
	$fields = explode(",",$fields);
	$customFieldList  = array();
	$nid = $_SESSION['importwizard_newsletter'];
	//the first field has the name
	array_shift($fields);
	//the second fileld nas the email
	array_shift($fields);
	
	
	if (count($fields))
	{
		foreach ($fields as $num=>$field)
		{
			$customFieldList[] = $field;
		}
		/*
			Go through each custom field
			see if it already exists in the newsletter
			if not, import it to the newsletter.
		*/

		$customFieldsBeingImported = array();
		
		foreach ($customFieldList as $cfield)
		{
			$fieldParts = explode("__",$cfield);
			$fieldEncoded = array_pop($fieldParts);
			$clabel = array_pop($fieldParts);
			$fieldInformation = base64_decode($fieldEncoded);
			$fieldInformation = explode("%sep%",$fieldInformation);
			$cname = $fieldInformation[0];
			$ctype = $fieldInformation[1];
			$cenum = $fieldInformation[2];
			$customFieldFinderQuery = "select * from ".$prefix."wpr_custom_fields where nid=$nid and name='$cname';";
			$existingFieldsOfSameName = $wpdb->get_results($customFieldFinderQuery);
			if (count($existingFieldsOfSameName)) //a custom field of same name exists
			{
				$customFieldsBeingImported[] = $existingFieldsOfSameName[0]->id;
				continue;
			}
			else
			{
				//import the custom field
				$importCustomFieldQuery = "INSERT INTO ".$prefix."wpr_custom_fields (nid, name,label, type, enum) values ($nid,'$cname','$clabel','$ctype','$cenum');";
				$wpdb->query($importCustomFieldQuery);
				$getTheCustomFieldId = "SELECT id from ".$prefix."wpr_custom_fields where name='$cname' and nid=$nid;";
				$results = $wpdb->get_results($getTheCustomFieldId);
				$customFieldsBeingImported[] = $results[0]->id;
			}
			
		}
	}
	//now get all the custom fields for which these subscribers get null values;
	$notInIdList = implode(",",$customFieldsBeingImported);
	$findNotInImportCustomFieldsIdsQuery = "select * from ".$prefix."wpr_custom_fields where nid=$nid and id not in($notInIdList);";
	$customFieldsToNull = $wpdb->get_results($findNotInImportCustomFieldsIdsQuery);
	unset($findNotInImportCustomFieldsIdsQuery);
	unset($notInIdList);
	$customFieldsToNullList = array();
	foreach ($customFieldsToNull as $cfield)
	{
		$customFieldsToNullList[] = $cfield->id;
	}
	unset($customFieldsToNull);	
	//list of custom fields.
	//the subscribers are to be inserted
	foreach ($content as $data)
	{
		// insert subscriber
		$subscriberInfo = explode(",",$data);

		//import name and email address to the newsletter's table
			$name = array_shift($subscriberInfo);
			$email = array_shift($subscriberInfo);
			//does this subscriber exist in this newsletter table?
			$subscriberExistsQuery = "SELECT * FROM ".$prefix."wpr_subscribers where nid=$nid and email='$email'";
			$results = $wpdb->get_results($query);
			//if not then insert the subscriber into the subscribers table
			if (count($results) == 0)
			{
				$time = time();
				$hash = microtime();
				$hash .= rand(rand(10221,26432),rand(26432,54223)); //some really really random number..
				$hash = md5($hash);
				$insertSubscriberQuery = "INSERT INTO ".$prefix."wpr_subscribers (nid, name, email, date, active, confirmed,hash,fid) values ($nid,'$name','$email','$time',1,1,'$hash',0);";
				$wpdb->query($insertSubscriberQuery);
				
				
				//get this subscriber's id
				$getSidQuery = "SELECT id from ".$prefix."wpr_subscribers where email='$email' and nid=$nid";
				$results = $wpdb->get_results($getSidQuery);
				$currentSid = $results[0]->id;
			}
			//else get this subscriber's id
			else
			{
				$currentSid = $results[0]->id;
			}
		
		//the rest are all custom fields. 
		if (count ($subscriberInfo)) //are there any custom fields at all? if there are then we start inserting the data.
		{
			//custom fields that are in the imported list
			foreach ($customFieldsBeingImported as $id)
			{
				$value = array_shift($subscriberInfo);
				$query = "INSERT INTO ".$prefix."wpr_custom_fields_values (nid, sid, cid, value) values ('$nid','$currentSid','$id','$value');";
				$wpdb->query($query);
			}
			//custom fields associated with this newsletter but are not in the 
			//imported list. for these null values should be imported. screw codd..
			foreach ($customFieldsToNullList as $cid)
			{
				$nid = (int) $nid;
				$currentSid = (int) $currentSid;
				$cid = (int) $cid;
				$insertNullQuery = "INSERT INTO ".$prefix."wpr_custom_fields_values (nid, sid, cid, value) values ($nid,'$currentSid',$cid,'');";
				$wpdb->query($insertNullQuery);
			}
			
		}		
		
	
		if ($_SESSION['importwizard_followup'] != "none" )
		{			
			//insert the blog subscription
			switch ($_SESSION['importwizard_blogsubscription'])
			{
				case 'all':
				addBlogSubscription($currentSid);
				break;
				case 'cat':
				foreach ($_SESSION['importwizard_blogcats'] as $categoryId)
				{

					addBlogCategorySubscription($currentSid,$categoryId);
				}
			}
			
			switch ($_SESSION['importwizard_followup'])
			{
				case 'followup': //subscribe to autoresponder
					$autoresponderId = $_SESSION['importwizard_fid'];
					addAutoresponderSubscription($currentSid,$autoresponderId);
				break;
				case 'postseries':
					$postSeriesId = $_SESSION['importwizard_fid'];


					addPostSeriesSubscription($currentSid,$postSeriesId);
				break;
			}
			
			
		}
	}	
	clearImportSessionVariables();
	return true;
}
function addAutoresponderSubscription($sid,$aid)
{
	global $wpdb;
	$prefix=$wpdb->prefix;
	$sid = (int) $sid;
	$aid = (int) $aid;
	
	if ($sid == 0)
		return false;
	if ($aid == 0 )
		return false;
	
	//does subscriber exist?
	$query = "SELECT count(*) num from ".$prefix."wpr_subscribers where id = $sid";
	$row = $wpdb->get_row($query);
	if ($row->num == 0)
		return false;
		
	//does autoresponder exist?
	$query = "SELECT count(*) num from ".$prefix."wpr_autoresponders where id='$aid'";
	$row = $wpdb->get_row($query);
	if ($row->num == 0)
		return false;
	
	if ($row->num == 0)
		return false;	
	
	//if all's well, subscribe to autoresponder
	$time = time();
	$query = "INSERT INTO ".$prefix."wpr_followup_subscriptions (sid,type,eid,sequence,doc) values ('$sid','autoresponder','$aid',-1,'$time');";
	$wpdb->query($query);
	return true;
}

function addPostSeriesSubscription($sid,$pid)
{
	global $wpdb;
	$prefix=$wpdb->prefix;
	$sid = (int) $sid;
	$pid = (int) $pid;
	
	if ($sid == 0)
		return false;
	if ($pid == 0 )
		return false;
	
	//does subscriber exist?
	$query = "SELECT count(*) num from ".$prefix."wpr_subscribers where id = $sid";
	$row = $wpdb->get_row($query);
	if ($row->num == 0)
		return false;
		
	//does postseries exist?
	$query = "SELECT count(*) num from ".$prefix."wpr_blog_series where id='$pid';";
	$row = $wpdb->get_row($query);
	if ($row->num == 0 )
		return false;
	
	//if all's well, subscribe this subscriber to the post series
	$time = time();
	$query = "INSERT INTO ".$prefix."wpr_followup_subscriptions (sid,type,eid,sequence,doc) values ('$sid','postseries','$pid',-1,'$time');";
	$wpdb->query($query);
	return true;
}
function addBlogSubscription($subscriberId)
{
	global $wpdb;
	$subscriberId = (int) $subscriberId;
	if ($subscriberId == 0)
		return false;
	else
	{
		$query = "INSERT INTO ".$wpdb->prefix."wpr_blog_subscription (sid,type,catid) values ('$subscriberId','all',0);";
		$wpdb->query($query);

	}
}

function addBlogCategorySubscription($sid,$catid)
{
	global $wpdb;
	$sid = (int) $sid;
	$catid = (int) $catid;
	if ($sid != 0 && $catid != 0)
	{
		$query = "INSERT INTO ".$wpdb->prefix."wpr_blog_subscription (sid,type,catid) values ('$sid','cat','$catid');";
		$wpdb->query($query);
		return true;
	}
	else
		return false;
}

/*
   All variables that are used by the import wizard 
   start with importwizard_. So delete em all.   
*/
function clearImportSessionVariables()
{
		foreach ($_SESSION as $name=>$value)
		{	
			if (preg_match("@^importwizard_.*@",$name))
			{
				unset($_SESSION[$name]);
			}
		}

}
function wizard()
{
	session_start();
	switch ($_GET['step'])
	{
		default:
		clearImportSessionVariables();
		$moveToStep2 = false;
		$nonce = $_POST['firststep_wpnonce'];
		if (isset($_POST['firststep_wpnonce']) && check_admin_referer('firststep_wpnonce','firststep_wpnonce'))
		{
			handleStep1($moveToStep2);
			if ($moveToStep2)
			{
				redirect("admin.php?page=wp-responder-email-autoresponder-and-newsletter-plugin/importexport.php&action=wizard&step=2");	
			}
		}
		else
		{
					step1();
		}
		
		break;
		case 2:
		//select their subscription settings
		$moveToStep3 = false;
                
		if (isset($_POST['secondstep_wpnonce']) && check_admin_referer("importwizard_wpnonce",'secondstep_wpnonce'))
		{
			handleStep2($moveToStep3);
			if ($moveToStep3)
			{
				redirect("admin.php?page=wp-responder-email-autoresponder-and-newsletter-plugin/importexport.php&action=wizard&step=3");	
			}
			else
				step2();
		}
		else
                    {
                    
			step2();
                    }
		
		break;
		case 3:
		//upload the file.
		$moveToStep4 = false;
		if (isset($_POST['thirdstep_wpnonce']) && check_admin_referer("importwizard_wpnonce",'thirdstep_wpnonce'))
		{
			handleStep3($moveToStep4,$error);
			if ($moveToStep4)
			{
				redirect("admin.php?page=wp-responder-email-autoresponder-and-newsletter-plugin/importexport.php&action=wizard&step=4");	
			}
			else
			{
				step3($error);
			}
		}
		else
			step3($error);
		
		break;
		case 4:
			//that's it. we're done.
			step4();
		break;
	}
	
}

function display_newsletter_list()
{
	global $wpdb;
	$prefix = $wpdb->prefix;
	$query = "select * from ".$prefix."wpr_newsletters";
	$results = $wpdb->get_results($query);
	$output = "<h2>Export Subscribers </h2>";
	$output .= "To download a CSV file containing the list of confirmed and active subscribers in a newsletter, click on the download button next to the newsletter. <p></p> <p></p>";
	$output .= '<table width="400">';
	foreach ($results as $newsletter)
	{
		$output .= "<tr><td>".$newsletter->name.'</td><td><a href="'.path_to_this_page().'&action=download&nid='.$newsletter->id.'" class="button">Download Subscribers</a></td></tr>';
	}
	$output .="</table>";
	echo $output;
}

function show_link_to_wizard()
{
	$output = "<h2>Import Subscribers To A Newsletter</h2>";
	$output .= "To import subscribers from a third party service like Aweber, Feedblitz or Feedburner, you will need to export the subscribers from that service in a CSV file  (comma separated values file). If you have the CSV file for your subscribers, click on the button below to start importing subscribers to a newsletter. ";
	$output .='<p><a href="'.path_to_this_page().'&action=wizard" class="button">Import Subscribers &raquo;</a>';
	echo $output;
	
}
