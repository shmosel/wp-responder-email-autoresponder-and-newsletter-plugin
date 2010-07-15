<?php
ignore_user_abort(true);
include "blogseries.lib.php";
include "lib.php";


function wpr_get_mailouts()
{

 	global $wpdb;
	
	$zone = date_default_timezone_get();  //whats the point?
	$timeStamp = time();
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletter_mailouts where status = 0 and time <= $timeStamp;";
	$mailouts = $wpdb->get_results($query);
	date_default_timezone_set($zone);
	return $mailouts;

}

/*

 *

 * $sid   - Id of the subscriber who should get the email

 * $params - The parameter array for the email that is to be sent

 *

 * $params  = array(  "subject" => String - The subject of the email.

 *                    "htmlbody"=> String - The HTML body of the email.

 *                    "textbody"=> String - The text body of the email.

 *                    "attachimages" => Boolean - Whether the images in the HTML body should be attached with the email.

 *                    "fromname"  => String - The name of the email sender
 
 		      "htmlenabled" => boolean ( 1 or 0 ) - Whether the html body of this message is enabled.

 *                    "fromemail" => String - The email address of the sender

 *

 *

 * $footerMessage - The optional footer message that is to be appended

 *                  at the bottom of the email after the email body and

 *                  before the Sender's address.

 *

 */



function sendmail($sid,$params,$footerMessage="")

{

	global $hash;

	global $wpdb;

	$subscriber = _wpr_subscriber_get($sid);
	$email = $subscriber->email;
	$sitename = get_bloginfo('name');
	$siteurl = get_bloginfo('home');
	$url = get_bloginfo('siteurl');
	$newsletter = _wpr_newsletter_get($subscriber->nid);

        //if the fromname field is set in the newsletter, then use that value otherwise use the blog name.

	$fromname = (!empty($params['fromname']))?$params['fromname']:(!empty($newsletter->fromname))?$newsletter->fromname:get_bloginfo("name");
	$fromemail = get_bloginfo("admin_email");
	
	if ($newsletter->reply_to)
		$replytoHeader = "Reply-To: ".$newsletter->reply_to." \r\n";
		
	$header = "$fromHeader $replytoHeader";	
	$unsuburl = wpr_get_unsubscription_url($sid);
	$subject = $params['subject'];

	$to = $email;
	$address = get_option("wpr_address");



	$textUnSubMessage = "\n\n$address\n\nTo unsubscribe or change subscriber options visit:\r\n\r\n$unsuburl";	

	$reply_to = $newsletter->reply_to;



	$htmlbody = trim($params['htmlbody']);
	//append the address and the unsub link to the email
	$address = "<br>
<br>
".get_option("wpr_address")."<br>
<br>
";
	$htmlUnSubscribeMessage = "<br><br>".$address."<br><br>To unsubscribe or change subscriber options visit:<br />
\r\n <a href=\"$unsuburl\">$unsuburl</a>";
	$htmlenabled = ($params['htmlenabled'])?1:0;
	if (!empty($htmlbody))
	{
		if ($footerMessage && (!empty($htmlbody)) )
		{
			$htmlbody .= nl2br($footerMessage)."<br>
<br>
";
		}
	   $htmlbody .= $htmlUnSubscribeMessage;
	}	

	if ($footerMessage)
		$params['textbody'] .= $footerMessage."\n\n";
	$textbody .= $params['textbody'].$textUnSubMessage;	
	$textbody = addslashes($textbody);
	$htmlbody = addslashes($htmlbody);
	$subject = addslashes($subject);

        $attachImages = ($params['attachimages'])?1:0;



        //if the from email is set in the params, use that, if not take the newsletter's value, if that too isnt set

        //use the administrator's email address

	$from = (!empty($params['fromemail']))?$params['fromemail']:(!empty($newsletter->fromemail))?$newsletter->fromemail:get_bloginfo('admin_email');

	$tableName = $wpdb->prefix."wpr_queue";

	$query = "INSERT INTO $tableName (`from`,`fromname`, `to`, `subject`, `htmlbody`, `textbody`, `headers`,`attachimages`,`htmlenabled`) values ('$from','$fromname','$email','$subject','$htmlbody','$textbody','$headers','$attachImages','$htmlenabled');";

	$wpdb->query($query);

}



function get_postseries_posts($catid,$nid="")

{

	global $mailer;
        global $wpdb;

	$args = array(

					'post_type' => 'post',

					'numberposts' => -1,

					'category'=>$catid,

					'orderby' => 'date',

					'order' => 'ASC',

					'post_status' => 'publish'

				);
	$posts = get_posts($args);

        if ($nid !="")
            {
                    foreach ($posts as $num=>$post)
                        {
                        $pid = $post->ID;
                        $query = "SELECT meta_value from ".$wpdb->prefix."postmeta where post_id=$pid and meta_key='wpr-options';";
                        $results = $wpdb->get_results($query);
                        $option = $results[0]->meta_value;
                        $decodedoptions = base64_decode($option);
                        $options = unserialize($decodedoptions);

                        if ($options[$nid]['disable'] !=1)
                            {

                            $theRealPosts[] = $post;
                        }                        

                    }
            }
			
		
	return $theRealPosts;

}

function mailout_expire($id)

{

	global $wpdb;

	$query = "UPDATE ".$wpdb->prefix."wpr_newsletter_mailouts set status=1 where id=$id";

	$wpdb->query($query);

}



function get_rows($query)

{

	global $wpdb;

	return $wpdb->get_results($query);

}

function wpr_filter_query($nid, $thestring)

{	

	$sections = explode(" ",$thestring);

	$size = count($sections);

	$count=0;

	$comparisonOpers = array("equal","notequal","lessthan","greaterthan");

	$stringOperators = array("startswith","endswith","contains");

	

	$final ="";

	for ($count=0;$count<$size;)

	{

		$condition = "";

	

		if ($count != 0)

		{

			$conjunction = " ".$sections[$count]." ";

		}

		else

		{

		   $conjunction = "";

		  $count = -1; //to adjust for the indices i have used below below..

		}

		  

		$field = $sections[$count+1];

		$equality = $sections[$count+2];

		$value = $sections[$count+3];

		

	

		if (in_array($equality,$comparisonOpers))

		{

			

			switch ($equality)

			{

				case 'equal':

				  $condition = "`$field` = '$value'";

				  break;

				case 'notequal':

				   $condition= "`$field` <> '$value'";

				   break;

				case 'lessthan':

				   $condition = "`$field` < '$value'";

				   break;

				case 'greaterthan':

				   $condition = "`$field` > '$value'";

			}

		}

		else if ($equality == "notnull")

		{

			$condition = "`$field` IS NOT NULL";

		}

		else if (in_array($equality,$stringOperators))

		{

			switch ($equality)

			{

				case 'startswith':

					$condition = "`$field` like '$value%'";

					break;

				case 'contains':

					$condition = "`$field` like '%$value%'";

					break;

				case 'endswith':

					$condition = "`$field` like '%$value'";

					break;

			}

		}

		else if (in_array($equality,array("before","after")) && $field == "dateofsubscription")

		{

				$thetime = strtotime($value);

				

				switch ($equality)

				{

					case 'before':

						$condition = "date < $thetime";

						break;

					case 'after':

						$condition = "date > $thetime";

						break;

				}

		}

		else if ($equality == "rlike")

		{

			$condition = "`$field` rlike '$value'"; 

		}

	

		

		$final .= $conjunction." ".$condition;

						 

		if ($count == 0) //the first element is not a conjunction

		{

			$count+=3;

		}

		else

		{

			$count +=4;

		}

	}

	return $final;

}



function wpr_add_footer()

{

}


function wpr_processEmails()

{

	global $wpdb;

	global $mailer;

	$prefix = $wpdb->prefix;

	//email mailouts
	
	date_default_timezone_set("UTC");
	
	
	$email_mailouts= wpr_get_mailouts();
	foreach ($email_mailouts as $broadcast)
	{

		$nid = $broadcast->nid;

		$subject = $broadcast->subject;

		$body = $broadcast->body;

		wpr_create_temporary_tables($nid);	  //this creates the tables based on which a bigger table will be created

		wpr_make_subscriber_temptable($nid);  //this table will be used for getting the user list.

		$customFieldsConditions = trim(wpr_filter_query($nid,$broadcast->recipients));

		$customFields = ($customFieldsConditions)?" AND ".$customFieldsConditions:"";

		$query = "SELECT * FROM ".$prefix."wpr_subscribers_".$nid." where active=1 and confirmed=1 $customFields;";

		$subscribersList = $wpdb->get_results($query);

		$subject = $broadcast->subject;

		$text_body = $broadcast->textbody;

		$html_body = $broadcast->htmlbody;

		$whetherToAttachImages = $broadcast->attachimages;

		$query = "SELECT fromname, fromemail from ".$wpdb->prefix."wpr_newsletters where id=".$nid;
		$results = $wpdb->get_results($query);
		$fromname = $results[0]->fromname;
		$fromemail = $results[0]->fromemail;
		
		
             
		if (count($subscribersList))

		{


			foreach ($subscribersList as $subscriber)

			{

				$sid = $subscriber->id;

				$email = $subscriber->email;

				$emailParameters = array( "subject" => $subject,
							  "from"=> $fromname,
							  "fromemail"=>$fromemail,
							  "textbody" => $text_body,
							  "htmlbody" => $html_body,
							  "htmlenabled"=> (empty($html_body))?0:1,
							  "attachimages"=> $whetherToAttachImages
							  );

				wpr_place_tags($sid,$emailParameters);

				$emailParameters["to"] = $subscriber->email;
				sendmail($sid,$emailParameters);

			}

		}

		mailout_expire($broadcast->id);

	}

	//autoresponder




	$getSubscribersQuery = "SELECT a.* FROM ".$prefix."wpr_followup_subscriptions a,".$prefix."wpr_subscribers b  where a.type='autoresponder' and a.sid=b.id and b.active=1 and b.confirmed=1;";

	$autoresponderSubscriptions = $wpdb->get_results($getSubscribersQuery);
	foreach ($autoresponderSubscriptions as $asubscription)

	{

		$subscriber = _wpr_subscriber_get($asubscription->sid);
		$aid = $asubscription->eid;
		$daysSinceSubscribing = floor((time()-$asubscription->doc)/86400);

		$lastSequence = $asubscription->sequence;

		if ($lastSequence == $daysSinceSubscribing) //we have alrady delivered this follow up series today.
			continue;
		$query = "SELECT * FROM ".$prefix."wpr_autoresponder_messages where aid=$aid and sequence=$daysSinceSubscribing limit 1;";
		$messages = get_rows($query);
		if (count($messages))
		{
			$messages = $messages[0];
			$emailParameters = array("subject" => $messages->subject, "textbody" => $messages->textbody , "htmlbody" => $messages->htmlbody, "htmlenabled"=> $messages->htmlenabled,"attachimages"=> $messages->attachimages);
			wpr_place_tags($subscriber->id,$emailParameters);
			sendmail($subscriber->id,$emailParameters);
			$updateSubscriptionStatusQuery = "UPDATE ".$prefix."wpr_followup_subscriptions set last_date='".time()."', sequence='$messages->sequence' WHERE sid=$subscriber->id";
			$wpdb->query($updateSubscriptionStatusQuery);
		}

	}



	//post series

	$query ="SELECT a.* FROM ".$prefix."wpr_followup_subscriptions a, ".$prefix."wpr_subscribers b where a.type='postseries' and a.sequence !='-2' and a.sid=b.id and b.active=1 and b.confirmed=1;";
	$postseriesSubscriptionList = $wpdb->get_results($query);

	foreach ($postseriesSubscriptionList as $psubscription)

	{



                if (!isPostSeriesSubscriptionActive($psubscription))
                    {

                    continue;
                    }

                

                $sid = $psubscription->sid;

                $query = "SELECT nid from ".$wpdb->prefix."wpr_subscribers where id=".$sid;

                $results = $wpdb->get_results($query);

                if (count($results) != 1) //where's the newsletter?!!
                    continue;
                $nid = $results[0]->nid;

		$subscriber = _wpr_subscriber_get($psubscription->sid);


		//how many days since subscribing?
		$daysSinceSubscribing = floor((time()-$psubscription->doc)/86400);
                //get the post series as an object
		$postseries = _wpr_postseries_get($psubscription->eid);
                //get the posts in the post series
                $posts = get_postseries_posts($postseries->catid,$nid);         
		$numberOfPosts = count($posts);
		$timeBetweenPosts = $postseries->frequency;
		$last_post = $psubscription->sequence;
		$currentIndex = floor($daysSinceSubscribing/$timeBetweenPosts);
		//if the post has already been sent, leave it, go to the next.
		if ($last_post > $currentIndex)//sometimes posts get deleted mid delivery
			continue;
			
		if ($last_post == $currentIndex) // if the posts for today have been delivered, skip further processing. 
			continue;
		
		//all posts have been sent. expire the post series subscription
		
		if ($last_post >= count($posts)-1)
		{
			
			//TODO: MAKE THIS AND THE SECTION MARKED BELOW AS A FUNCTION 
			$query = "UPDATE ".$prefix."wpr_followup_subscriptions set sequence='-2', last_date='".time()."' where id='".$psubscription->id."';";
			$wpdb->query($query);
			continue;
		}
		
		$indexToDeliver = $last_post+1;

		$category = $psubscription->eid;
		$postToSend = $posts[$indexToDeliver];

              
        //get teh newsletter to which this subscriber belongs.
		//check if the post is allowed to be sent.
		$theoptions = get_post_meta($postToSend->ID,'wpr-options',true);
        $decodedOptions = base64_decode($theoptions);
        $options = unserialize($decodedOptions);
        if ($options[$nid]['disable']==1)
        {
            $skip = "on";
        }
         else
        {
            $skip = "off";
        }
		if ($skip == "on") //skip this post. get the next post to send that doesn't have a 'don't send by email' option on.
		{
			$foundAPostToSend = false;                        
			//are there any more posts?
			if ($indexToDeliver+1 < count($posts)) //then find the next post that isn't skipped.
			{        
				$countOfPosts =count($posts);
				for ($curr = $indexToDeliver+1; $curr< $countOfPosts; $curr++)
				{
					$lastId = $posts[$curr]->ID;                        
                    if (!whetherToSkipThisPost($nid,$lastId))
					{
						$postToSend = $posts[$curr]; //then send the next post in this post series.
						$foundAPostToSend = true;
						$indexToDeliver = $curr;
						break;
					}
				}
				
				if (!$foundAPostToSend)
				{
                       $query = "UPDATE ".$prefix."wpr_followup_subscriptions set sequence='-2', last_date='".time()."' where id='".$psubscription->id."';";                        
					//mark this follow up sequence as finished.
					$wpdb->query($query);

					continue;

				}
			}
			else  //the last post in the post series has been skipped. mark the follow up as finished.
			{
				$theCountOfCurrent = $indexToDeliver+1;
				if ($theCountOfCurrent== count($posts))
				{
					$setSequenceTo = -2;
				}
				else
				{
					$setSequenceTo = $currentIndex;
				}
				$query = "UPDATE ".$prefix."wpr_followup_subscriptions set sequence='$setSequenceTo', last_date='".time()."' where id='".$psubscription->id."';";
				$wpdb->query($query);
				continue;
			}
			//if we didn't find a post to send.. forget it! move to the next subscription.
		}
		$sitename = get_bloginfo("name");
        deliverBlogPost($sid,$postToSend->ID,"You are receiving this blog post as a part of a post series at $name.",true,true);
		$query = "UPDATE ".$prefix."wpr_followup_subscriptions set sequence=$indexToDeliver , last_date='".time()."' where id='".$psubscription->id."';";
		$wpdb->query($query);
	}
	//now process the people who subscribe to the blog
	$lastPostDate = get_option("wpr_last_post_date");
	date_default_timezone_set("UTC");
    $timeNow = date("Y-m-d H:i:s",time());
	$query = "SELECT * FROM ".$prefix."posts where post_type='post' and  post_status='publish' and post_date_gmt > '$lastPostDate' and post_date_gmt < '$timeNow';";
	$posts = $wpdb->get_results($query);
	
	
	if (count($posts) > 0 ) // are there posts being delivered? 
	{
		foreach ($posts as $post)
		{
			$query = "SELECT a.* FROM ".$prefix."wpr_subscribers a, ".$prefix."wpr_blog_subscription b where b.type='all' and a.id=b.sid and a.active=1 and a.confirmed=1;";
			$subscribers = $wpdb->get_results($query);
					//deliver this post to all subscribers of the categories of
					// this post.
			$categories = wp_get_post_categories($post->ID);
			foreach ($categories as $category)
			{
				deliver_category_subscription($category,$post);
			}
	
			if (count($subscribers) > 0)
			{
				$blogName = get_bloginfo("name");
				$blogURL = get_bloginfo("home");
				$footerMessage = "You are receiving this email because you are subscribed to the latest articles on <a href=\"$blogURL\">$blogName</a>";
				foreach ($subscribers as $subscriber)
				{
							deliverBlogPost($subscriber->id,$post->ID,$footerMessage);
				}
			}
			
			delete_option("wpr_last_post_date");
			add_option("wpr_last_post_date",$post->post_date_gmt);
			
			
			$sentPosts = get_option("wpr_sent_posts");
			$sentPostsList = explode(",",$sentPosts);
			$sentPostsList[] = $post->ID;
			$sentPosts = implode(",",$sentPostsList);
	
			delete_option('wpr_sent_posts');
			add_option("wpr_sent_posts",$sentPosts);	
		}
	}

	$content = ob_get_clean();

	delete_option("wpr_next_cron");
	add_option("wpr_next_cron",time()+300);

}


function isValidOptionsArray($options)
{
    if (is_array($options))
        {
        return true;
    }
    else
         return false;
}
/*
 * This function checks if the post $pid is to be skipped from being delivered to
 * subscribers of newsletter $nid.
 */

function whetherToSkipThisPost($nid,$pid)
        {
    $theoptions = get_post_meta($pid,'wpr-options',true);
    $options = unserialize($theoptions);
    if (!isset($options))
        return 0;
    //by default, the skip is disabled.
    if ($options[$nid]['disable']==1)
        {
           return 1;
    }
    else
        return 0;
}

/*

 * This function is used to generate a body for the blog post sent via email

 * when the user doesn't customize it or chooses to use the default layout

 *

 * This function is also used when the post doesn't have any WP Responder options

 * associated with it.

 * Returns string with the HTML to be used for the email

 *

 */






function getBlogContentInDefaultLayout($post_id)

{

    $post = get_post($post_id);

    $content = '<div style="background-color:  #dfdfdf;padding: 5px;"><span style="font-size: 9px; font-family: Arial; text-align:center;\">You are receiving this email because you are subscribed to new posts at ';
    $content .= "<a href=\"".get_bloginfo("home")."\">".get_bloginfo("name")."</a></span></div>";

    $content .= "
<h1><a href=\"".get_permalink($post_id)."\" style=\"font-size:22px; font-family: Arial, Verdana; text-decoration: none; color: #333399\">";

  $content .= $post->post_title;

  $content .= "</a></h1>
";

    $content .= '<p style="font-family: Arial; font-size: 10px;">Dated: '.date("d F,Y",strtotime($post->post_date));

    $content .= "
</p><p><span style=\"font-family: Arial, Verdana; font-size: 12px\">".wptexturize(wpautop(nl2br($post->post_content)))."</span>

";

    $content .= "<br><br><span style=\"font-size: 12px; font-family: Arial\"><a href=\"".get_permalink($post_id)."\">Click here</a> to read this post at <a href=\"".get_bloginfo("home")."\">".get_bloginfo("name")."</a></div>.";


    return $content;



}



/*

 * This function is used to see if the subscriber with sid $sid

 * is currently receiving any follow up emails from autoresponders

 * or post series subscriptions.

 */

/*
 * This function sends the blog post with post id $post_id via email to subscriber with subscriber id $sid
 * if the the subscriber doesnt belong to a newsletter with newsletter id
 * that is in the list of newsletters that are configured to not receive this blog post.
 *
 *
 *
 */
function deliverBlogPost($sid,$post_id,$footerMessage="",$checkCondition=false,$whetherPostSeries=false)
{
    global $wpdb;

    //get the post meta

    $sid = (int) $sid;

    $post_id = (int) $post_id;

    if ($sid == 0 || $post_id==0) // neither of these can be zero or empty.
        return;

    

    $post = get_post($post_id);
    //if plugin was activated after some posts were created

    //the options array will not exist. in that case, we just

    //deliver the blog post

    $optionsList = get_post_meta($post_id,"wpr-options",true);    
    if (!empty($optionsList))
    {
            $decoded = base64_decode($optionsList);
            $options = unserialize($decoded);
            $checkCondition = true; //if we have a valid options array, then we should
            //check the conditions of delivery.
    }
    else
    {
            $checkCondition=false;
    }
    
    $query = "SELECT nid from ".$wpdb->prefix."wpr_subscribers where id=".$sid;
    $results = $wpdb->get_results($query);
    $nid = $results[0]->nid;
    if (count($results) == 0) //if there is no subscriber by that sid
        return;

    
    $deliverFlag = true; // this flag is used to trigger the delivery
    if ($checkCondition == true)
    {
       //get the subscriber's newsletter id
        if (isset($options[$nid]))
        {
            if ($options[$nid]['disable']==1)
                {
                    $deliverFlag = false;
                }
        }
        else
            $deliverFlag=true;
   }
   else
       {
       $deliverFlag = true;
   }

   //deliver the email.
   if ($deliverFlag)
       {
        //are customizations disabled? then get the html body for the blog post
        //from the default layout format.
       //check if the subscriber is currently receiving any follow up series emails
       if (isset($options) && $options[$nid]['skipactivesubscribers']==1 && isReceivingFollowupPosts($sid))
           return;



       /*
        * The conditions where the default layout is used are:
        * the customization has been disabled,
        * the customization has been disabled for post series
        * there is no customization information - the post was created when
        * wp responder was not installed/deactivated.
        */
       if ($options[$nid]['nocustomization']==1 || !isValidOptionsArray($options) || ($whetherPostSeries == true && $options[$nid]['nopostseries']==1))
           {
            $htmlbody = getBlogContentInDefaultLayout($post_id);
            $post = get_post($post_id);
            $subject = $post->post_title;
            $params = array("subject"=>$subject,
                            "htmlbody"=>$htmlbody,
                            "textbody"=>"",
                            "htmlenabled"=>1,
                            "attachimages"=>true);
       }
       else
       {
		     $htmlBody = $options[$nid]['htmlbody'].nl2br($footerMessage);
			 
			 $htmlEnabled = ($options[$nid]['htmlenable']==1)?1:0;
			 if (!$htmlEnabled)
			 	$htmlBody="";
				
             $params = array("subject"=>$options[$nid]['subject'],
                            "htmlbody"=>$htmlBody,
                            "textbody"=>$options[$nid]['textbody'].strip_tags("$footerMessage"),
                            "attachimages"=>($options[$nid]['attachimages'])?1:0,
                            "htmlenabled"=> $htmlEnabled
                 );

       }

       $params['subject'] = substitutePostRelatedShortcodes($params['subject'],$post_id);
       $params['htmlbody'] = substitutePostRelatedShortcodes($params['htmlbody'],$post_id);
       $params['textbody'] = substitutePostRelatedShortcodes($params['textbody'],$post_id);
	   
	   //substitute newsletter related parameters.
	   
	   wpr_place_tags($sid,$params);
       sendmail($sid,$params);
   }

}

function substitutePostRelatedShortcodes($text,$post_id)
        {

    //the post's url
	
	
    $postUrl = get_permalink($post_id);
    $text = str_replace("[!post_url!]",$postUrl,$text);
	
    //teh post's delivery date
    //which is time right now.
    $time = date("g:iA d F Y ",time());
    $time .= date_default_timezone_get();
    $text = str_replace("[!delivery_date!]",$time,$text);
	
    //post publishing date
    $post = get_post($post_id);
    $postDate = $post->post_date;
    $postEpoch = strtotime($postDate);
    $postDate = date("dS, F Y",$postEpoch);
    $text = str_replace("[!post_date!] ",$postDate,$text);
   
    return $text;
    
}

function deliver_category_subscription($catid,$post)
{

	global $wpdb;

	$prefix = $wpdb->prefix;
	$query = "SELECT a.* FROM  ".$prefix."wpr_subscribers a,".$prefix."wpr_blog_subscription b where b.type='cat' and b.catid='$catid' and a.id=b.sid and a.active=1 and a.confirmed=1";
	$subscribers = $wpdb->get_results($query);
	$theCategory = get_category($catid);
	$categoryName = $categoryname->name;
	$blogName = get_bloginfo("name");
	$blogURL = get_bloginfo("siteurl");
	$footerMessage = "You are receiving this e-mail because you have subscribed to the $categoryName category of $blogName

$blogUrl";
	foreach ($subscribers as $subscriber)
	{
               deliverBlogPost($subscriber->id,$post->ID);
	}

}
