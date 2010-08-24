<?php

/*
TODO:

1. After the user uplaods the csv file and then identifies the columns in the csv file, the custom field values in the csv file should be validated. Forexample, it the newsletter to which we are importing has an enumared custom field called gender(male,female), and we are trying to import some column in the csv - then the plugin should verify if all the values for that column is either 'male' or 'female'. .  Importing should not be allowed if it isn't.

2. During the import process, if the user already exists, the import for that user will fail and an empty field will be inserted to teh table. 

3. When creating the blog/followup subscription for the email subscriber just inserted, the procedure currently doesnt check if such a subscription already exists. This is necessary in the case that the subscriber with that email address already existed. 

*/


add_action("_wpr_importexport_handle","_wpr_importexport_handler");

function _wpr_importexport_handler($parameters)
{
	switch ($parameters[1])
	{
		case 'import':
   		switch ($parameters[2])
		{
			case 'step2':
			_wpr_import_second_step();
			break;
                        case 'step3':
                        _wpr_import_third_step();
                        break;
                        case 'step4':
                            _wpr_import_fourth_step();
                        break;
                        case 'step5':

                            _wpr_import_fifth_step();

                            break;
						case 'finished':
						_wpr_import_finished();
						
						break;
		}
		
		break;
		
		default:
		_wpr_import_export_home();
		
	}
    //get a list of newsletter   
}

add_action("_wpr_wpr_subscriber_export_post","_wpr_export");

function _wpr_export()
{
	$nid = $_POST['newsletter'];
	export_csv($nid);
	exit;
}


function _wpr_import_finished()
{
	$newsletter = _wpr_newsletter_get($_SESSION['wpr_import_newsletter']);
	_wpr_set("newsletter",$newsletter);
	_wpr_set("_wpr_view","import.finished");
}


function _wpr_import_second_step()
{
	if (isset($_SESSION['wpr_import_newsletter']))
	{
                $nid = $_SESSION['wpr_import_newsletter'];
                
		$autoresponders = _wpr_autoresponders_get($nid);
                
		_wpr_set("autoresponderList",$autoresponders);
                $postSeries = _wpr_postseries_get_all();

                _wpr_set("postseriesList",$postSeries);
		_wpr_set("_wpr_view","import.secondstep");

	}
	else
	{
            echo "here!fa";
		wp_redirect("admin.php?page=wpresponder/importexport");
	}
}

function _wpr_import_third_step()
{
      $args = array(
                                            'type'                     => 'post',
                                            'child_of'                 => 0,
                                            'orderby'                  => 'name',
                                            'order'                    => 'ASC',
                                            'hide_empty'               => false,
                                            'hierarchical'             => 0);

    $categories = get_categories($args);

    _wpr_set("categoryList",$categories);
    _wpr_set("_wpr_view","import.thirdstep");
}

function _wpr_import_export_home()
{
    $newsletters = _wpr_newsletters_get();
    _wpr_set("newslettersList",$newsletters);
}

add_action("_wpr_wpr_import_first_post","_wpr_import_first_post");
add_action("_wpr_wpr_import_followup_post","_wpr_import_followup_post");
function _wpr_import_first_post()
{
	@session_start();		
	$newsletter= trim($_POST['newsletter']);
	$_SESSION['wpr_import_newsletter']=$newsletter;
	wp_redirect("admin.php?page=wpresponder/importexport/import/step2");
}

function _wpr_import_followup_post()
{
    @session_start();
    $_SESSION['wpr_import_followup'] = $_POST['followup'];
    wp_redirect("admin.php?page=wpresponder/importexport/import/step3");
}
add_action("_wpr_wpr_import_blogsub_post","_wpr_import_blogsub_post");
add_action("_wpr_wpr_import_upload_post","_wpr_import_upload");
function _wpr_import_blogsub_post()
{
    @session_start();
    $_SESSION['_wpr_import_blogsub'] = $_POST['blogsubscription'];
    wp_redirect("admin.php?page=wpresponder/importexport/import/step4");
}



function _wpr_import_fourth_step()
{
    _wpr_set("_wpr_view","import.fourthstep");
}



function _wpr_import_upload()
{
    @session_start();
    if ($_FILES['csv']['error']==UPLOAD_ERR_OK)
    {
        $_SESSION['_wpr_csv_file'] = file($_FILES['csv']['tmp_name']);
        wp_redirect('admin.php?page=wpresponder/importexport/import/step5');
    }
    else
    {        
        $_SESSION['_wpr_import_error']="File upload failed";
        _wpr_set('_wpr_view','import.fourthstep');
    }
}

function _wpr_import_fifth_step()
{

    $csv = $_SESSION['_wpr_csv_file'];

    $count=0;
	
	$sample = array_slice($csv,0,100);
	
	$csv = splitToArray($sample);


    $customFields = _wpr_newsletter_all_custom_fields_get($_SESSION['wpr_import_newsletter']);



    $columnsRequired = array('name'=>'Name',
        'email'=>'E-Mail Address');

    foreach ($customFields as $field)
    {
        $columnsRequired[$field->name] = $field->label;
    }
    _wpr_set("list",$csv);
    _wpr_set("columns",$columnsRequired);
    _wpr_set("_wpr_view","import.fifthstep");
}


function splitToArray($data)
{
	$csvcontent = implode("\n",$data);
	

	$fp = tmpfile();
	fwrite($fp,$csvcontent);
	rewind($fp);
	$theoutput=array();

	while (!feof($fp))
	{
		$row = fgetcsv($fp);
		
		//damn the empty rows.
		if (!is_array($row) || empty($row) || strlen(implode("",$row))==0)
			continue;
		else
		{
			array_push($theoutput,$row);
		}
	}
	return $theoutput;
}

function _wpr_wpr_import_finish_post()
{
	//start importing.
	global $wpdb;
	$prefix = $wpdb->prefix;
	$arrayIndexes = array();
	
	$subscribers = &$_SESSION['_wpr_csv_file'];
	
	$subscribers = splitToArray($subscribers);
	

	foreach ($_POST as $name=>$value)
	{
		if (!empty($value))
			$arrayIndexes[$value] = str_replace("column_","",$name);
	}
	$nid = $_SESSION['wpr_import_newsletter'];

	$indexOfId = count($subcribers[0]);
	
	foreach ($subscribers as $index=>$subscriber)
	{

	
		$name = addslashes(trim($subscriber[$arrayIndexes['name']]));
		$email = trim($subscriber[$arrayIndexes['email']]);
		
		$currentSid = _wpr_subsciber_add_confirmed(array('nid'=>$nid,'name'=>$name,'email'=>$email));
		$subscribers[$index][$indexOfId]= $currentSid;		
		//add all of the subscriber's followup subscriptions														 
	}
	
	if ($_SESSION['wpr_import_followup'] !="none")
	{
		$time = time();
		if (preg_match("@^autoresponder_[0-9]+@",$_SESSION['wpr_import_followup']))
		{
			$followuptype = "autoresponder";
			$eid = str_replace("autoresponder_","",$_SESSION['wpr_import_followup']);	
		}
		elseif (preg_match("@^postseries_[0-9]+@",$_SESSION['wpr_import_followup']))
		{
			$followuptype = "postseries";
			$eid = str_replace("postseries_","",$_SESSION['wpr_import_followup']);	
		}
		
		if (count($subscribers)>0)
		{
			foreach ($subscribers as $subscriber)
			{
				$currentSid = $subscriber[$indexOfId];
			
				$query = "INSERT INTO ".$prefix."wpr_followup_subscriptions (sid, type, eid,  sequence, last_date,doc) values ('$currentSid','$followuptype','$eid',-1,0,'$time');";
				$wpdb->query($query);
			}
		}
		
	}
	
	
	if ($_SESSION['_wpr_import_blogsub']!="none")
	{
		$subtype = $_SESSION['_wpr_import_blogsub'];
		$cat = 0;

		if (preg_match("@category_[0-9]+@",$subtype ))
		{
			$cat = str_replace("category_","",$subtype);
			$subtype = "cat";
		}
		else
		{
					$subtype="all";
					$cat=0;
		}
		
		foreach ($subscribers as $subscriber)
		{
			$currentSid=$subscriber[$indexOfId];
			$subscriptionQuery = "INSERT INTO ".$prefix."wpr_blog_subscription (sid, type, catid) values ('$currentSid','$subtype','$cat');";
			$wpdb->query($subscriptionQuery);
		}
	}
	
	//custom fields..
	
	//fetch all of this newsletter's custom fields
	
	$query = "SELECT id,name from ".$prefix."wpr_custom_fields where nid=$nid";
	$customFieldsOfNewsletter = $wpdb->get_results($query);
	
	
	
	
	//create an array that we can use easily.
	foreach ($customFieldsOfNewsletter as $cust)
	{
		$customFields[$cust->name] = $cust->id;
	}
	
	$customFieldsAvailable=array();
	$customFieldsToNull=array();
	if (count($customFields) >0)
	{
		foreach ($customFields as $fieldName=>$fieldId)
		{
			if (array_key_exists($fieldName,$arrayIndexes))
			{
				$customFieldsAvailable[$fieldName] = $fieldId;
			}
			else
			{
				$customFieldsToNull[$fieldName] = $fieldId;
			}
		}
	
		foreach ($subscribers as $index=>$subscriber)
		{
			//first gather the relevant subscriber information
			$sid = $subscriber[$indexOfId];//we inserted the subscriber's id in the end. 
			foreach ($customFieldsAvailable as $fieldName=>$fieldId)
			{
				$customFieldIndex = $arrayIndexes[$fieldName];
				$value = trim($subscriber[$customFieldIndex],'"');
				$customFieldValueInsertQuery = "INSERT INTO ".$prefix."wpr_custom_fields_values (nid,sid,cid,value) values ('$nid','$sid','$fieldId','$value');";
				echo $customFieldValueInsertQuery."<br>";
				$wpdb->query($customFieldValueInsertQuery);
			}
			
			foreach ($customFieldsToNull as $fieldName=>$fieldId)
			{
				$value="";
				$customFieldNullInsertQuery = "INSERT INTO ".$prefix."wpr_custom_fields_values (nid,sid,cid,value) values ('$nid','$sid','$fieldId','$value');";
				$wpdb->query($customFieldNullInsertQuery);
			}
		}
	}
	
	
	foreach ($_SESSION as $name=>$value)
	{
		if (preg_match("@wpr_@",$name))
			unset($_SESSION[$name]);
	}
	
	wp_redirect("admin.php?page=wpresponder/importexport/import/finished");
	//fetch the ids of the custom fields we are going to insert.
}


add_action("_wpr_wpr_import_finish_post","_wpr_wpr_import_finish_post");