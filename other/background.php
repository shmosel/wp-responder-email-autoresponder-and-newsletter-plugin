<?php
function getNumberOfEmailsToDeliver()
{
	$currentTime = time();	
	$hourly_limit = get_option("wpr_hourlylimit");

	if ($hourly_limit == 0)
		return WPR_MAX_QUEUE_EMAILS_SENT_PER_MINUTE;
		
	$last_reset_time = get_option("_wpr_dq_last_reset_time");
	if (empty($last_reset_time)) // first run
	{
                $first_reset_time = mktime(date("H"),0,0);
		update_option("_wpr_dq_last_reset_time",$first_reset_time);
		$emails_sent_this_hour = 0;
		update_option("_wpr_dq_emails_sent_this_hour",$emails_sent_this_hour);
	}

	//reseting emails sent this hour value
	if ($currentTime > ($last_reset_time+3600))
	{
		update_option("_wpr_dq_last_reset_time",$currentTime);
		update_option("_wpr_dq_emails_sent_this_hour",0);
	}
	
	$number_of_emails_delivered_this_hour = get_option("_wpr_dq_emails_sent_this_hour");

        if ($number_of_emails_delivered_this_hour >= $hourly_limit)
            return 0;

	$emails_to_deliver_per_minute = $hourly_limit/60;
	$minute_since_last_start_of_hour = intval(date("i"));
	$number_of_emails_should_have_delivered = floor($minute_since_last_start_of_hour*$emails_to_deliver_per_minute);

        if ($number_of_emails_delivered_this_hour > $number_of_emails_should_have_delivered)
                return 0;

        //the number of emails to deliver ideally....
	$numberOfEmailsToDeliverThisMinute = $number_of_emails_should_have_delivered - $number_of_emails_delivered_this_hour;

        //in case we are closer than usual to the houly limit..
        if ($numberOfEmailsToDeliverThisMinute+$number_of_emails_delivered_this_hour > $hourly_limit)
        {
            $numberOfEmailsToDeliverThisMinute = $hourly_limit-$number_of_emails_delivered_this_hour;
        }
	
	if ($numberOfEmailsToDeliverThisMinute > WPR_MAX_QUEUE_EMAILS_SENT_PER_MINUTE)
		return WPR_MAX_QUEUE_EMAILS_SENT_PER_MINUTE;
	
	return $numberOfEmailsToDeliverThisMinute;
}

function _wpr_process_queue()
{
	global $wpdb;

	/*ENSURING THERE IS ONLY ONE INSTANCE THAT RUNS FOR A MAXIMUM OF ONE HOUR START HERE*/
 	set_time_limit(3600);
	$last_cron_status = get_option("_wpr_queue_delivery_status");

    /*
	When the cron is running the _wpr_queue_delivery_status
	is set to the timestamp at which the cron processing was started.
	
	Before shutting down the _wpr_queue_delivery_status is
	set to 'stopped'.
	
	This cron will run only if the _wpr_queue_delivery_status option
	is set to "stopped" or is empty.
	*/

	$timeOfStart = time();
	$timeMaximumExecutionTimeAgo = $timeOfStart - WPR_MAX_QUEUE_DELIVERY_EXECUTION_TIME;
	if (!empty($last_cron_status) && $last_cron_status != "stopped")
	{
		$last_cron_status = intval($last_cron_status);
		if ($last_cron_status !=0 && ($last_cron_status > $timeMaximumExecutionTimeAgo))
		{
			return;
		}
	}

	update_option("_wpr_queue_delivery_status",$timeOfStart);
	
	$numberOfEmailsToDeliver = getNumberOfEmailsToDeliver();
	$queueBatchSize = WPR_MAX_QUEUE_EMAILS_SENT_PER_ITERATION;	
	$numberOfIterations = ceil($numberOfEmailsToDeliver/$queueBatchSize);
	
	//queue batch size of number of emails to deliver whichever is lesser should be fetched from db.
	if ($numberOfEmailsToDeliver < $queueBatchSize)
	  $queueBatchSize = $numberOfEmailsToDeliver;
	
	for ($iter=0;$iter<$numberOfIterations;$iter++)
	{
		$limitClause = sprintf(" LIMIT %d",$queueBatchSize);
		$query = sprintf("SELECT q.* FROM `%swpr_queue` q, %swpr_subscribers s WHERE s.id=q.sid AND q.`sent`=0 AND s.confirmed=1 AND s.active=1 %s ",$wpdb->prefix,$wpdb->prefix,$limitClause);
		$results = $wpdb->get_results($query);
		foreach ($results as $mail)  
		{
			$mail = (array) $mail;	
			
			
			//
			$checkWhetherUnsent = sprintf("SELECT COUNT(*) num FROM {$wpdb->prefix}wpr_queue WHERE `id`=%d AND `sent`=0",$mail['id']);
			$whetherUnsentRes = $wpdb->get_results($checkWhetherUnsent);
			$number = $whetherUnsentRes[0]->num;
			if ($number == 0)
			   continue;
			
			$setEmailAsSentQuery = sprintf("UPDATE `%swpr_queue` SET `sent`=1 WHERE `id`=%d",$wpdb->prefix,$mail['id']);
			$wpdb->query($setEmailAsSentQuery);


			try {
				dispatchEmail($mail);
			}
			catch (Swift_RfcComplianceException $exception) //invalidly formatted email.
			{
				//disable all subscribers with that email.
				//TODO: Move this to a separate function.
				$email = $mail['to'];

				$setTheEmailAsFailedQuery = $wpdb->prepare("UPDATE `{$wpdb->prefix}wpr_subscribers` SET `active`=3, `confirmed`=0 WHERE `email`=%s",$email);
				$wpdb->query($setTheEmailAsFailedQuery);
				
				//set aall other emails in queue which have this email to 3.
				//TODO: #DevDoc sent=3 means invalid email.
				$markAllEmailsOfThisEmailUnprocessable = $wpdb->prepare("UPDATE `{$wpdb->prefix}wpr_queue` SET `sent`=3 WHERE `sent`=0 AND `email`=%s",$email);
				$wpdb->query($markAllEmailsOfThisEmailUnprocessable);
			}
		        $timeThisInstant = time();
		        $timeSinceStart = $timeThisInstant-$timeOfStart;
		        if ($timeSinceStart > WPR_MAX_QUEUE_DELIVERY_EXECUTION_TIME)
		        {
		            update_option("_wpr_queue_delivery_status","stopped");
		            return;
		        }

		}
	}

	update_option("_wpr_queue_delivery_status","stopped");
}

function whetherTimedOut($startTime,$maxTime) 
{
	$currentTime = time();
	if (($currentTime-$startTime) > $maxTime){
	  return true;
	}	
	return false;
}


class WPRBackgroundProcessor {

    public static function autoresponder_process_subscriber($subscriber_id) {

        $getAutoresponderSubscriptionQuery = sprintf("SELECT * FROM {$wpdb->prefix}wpr_followup_subscriptions subscription, {$wpdb->prefix}wpr_subscribers subscribers WHERE
                                                                subscription.sid=%d AND
                                                                subscribers.id=subscription.sid AND
                                                                subscribers.active=1 AND subscribers.confirmed=1;", $subscriber_id);

        $subscriptionResults = $wpdb->get_results($getAutoresponderSubscriptionQuery);

        if (0 == count ($subscriptionResults)) {
            return;
        }



    }

    public static function process_autoresponders() {

        $processor = AutoresponderProcessor::getProcessor();
        set_time_limit(WPR_MAX_AUTORESPONDER_PROCESS_EXECUTION_TIME);

        $timeOfStart = time();
        $currentTime = new DateTime(sprintf("@%d", $timeOfStart));

        $timeWhenAutoresponderProcessLastDidAHeartBeat = get_option("_wpr_autoresponder_process_status");
        if (self::whetherAnotherInstanceIsAlreadyRunning($timeOfStart, $timeWhenAutoresponderProcessLastDidAHeartBeat)) {
            return;
        }

        //set the time of ping for the autoresponder process status variable
        update_option("_wpr_autoresponder_process_status",$timeOfStart);

        //set the start time of the autoresponder process
        do_action("_wpr_autoresponder_process_start", $currentTime);

        //run the autoresponder processor

        $processor->run_for_time($currentTime);

        //call the hooks that need notification for the
        do_action('_wpr_autoresponder_process_end', $currentTime);
        update_option("_wpr_autoresponder_process_status","stopped");


    }

    public static function whetherAnotherInstanceIsAlreadyRunning($timeOfStart, $timeWhenAutoresponderProcessLastDidAHeartBeat)
    {
        $timeMaximumExecutionTimeAgo = $timeOfStart - WPR_MAX_AUTORESPONDER_PROCESS_EXECUTION_TIME;
        if (!empty($timeWhenAutoresponderProcessLastDidAHeartBeat) && $timeWhenAutoresponderProcessLastDidAHeartBeat != "stopped") {
            $timeWhenAutoresponderProcessLastDidAHeartBeat = intval($timeWhenAutoresponderProcessLastDidAHeartBeat);
            if ($timeWhenAutoresponderProcessLastDidAHeartBeat != 0 && (self::whetherLastAutoresponderProcessHeartBeatValidityExpired($timeWhenAutoresponderProcessLastDidAHeartBeat, $timeMaximumExecutionTimeAgo))) {
                return true;
            }
        }

        return false;
    }

    private static function whetherLastAutoresponderProcessHeartBeatValidityExpired($timeWhenAutoresponderProcessLastDidAHeartBeat, $timeMaximumExecutionTimeAgo)
    {
        return !($timeWhenAutoresponderProcessLastDidAHeartBeat < $timeMaximumExecutionTimeAgo);
    }


}


function _wpr_postseries_process()
{
	global $wpdb;
	$last_cron_status = get_option("_wpr_postseries_process_status");
    $currentTime = time();

	set_time_limit(3600);

	/*
	When the cron is running the _wpr_postseries_process_status
	is set to the timestamp at which the cron processing was started.
	
	Before shutting down the _wpr_postseries_process_status is
	set to 'stopped'.
	
	This cron will run only if the _wpr_postseries_process_status option
	is set to "stopped" or is empty.
	*/
	$timeOfStart = time();
	$timeMaximumExecutionTimeAgo = $timeOfStart - WPR_MAX_POSTSERIES_PROCESS_EXECUTION_TIME;
	if (!empty($last_cron_status) && $last_cron_status != "stopped")
	{
		$last_cron_status = intval($last_cron_status);
		if ($last_cron_status !=0 && ($last_cron_status > $timeMaximumExecutionTimeAgo))
		{
			return;
		}
	}
	
	update_option("_wpr_postseries_process_status",$timeOfStart);	
	
	$prefix = $wpdb->prefix;	
	$getActiveFollowupSubscriptionsQuery = "SELECT a.*, b.id sid, FLOOR(($currentTime - a.doc)/86400) daysSinceSubscribing FROM `".$prefix."wpr_followup_subscriptions` a, `".$prefix."wpr_subscribers` b  WHERE a.type='postseries' AND  a.sequence < FLOOR(($currentTime - a.doc)/86400) AND a.sequence <> -2 AND  a.sid=b.id AND b.active=1 AND b.confirmed=1 LIMIT 1000;";
	$postseriesSubscriptionList = $wpdb->get_results($getActiveFollowupSubscriptionsQuery);
	foreach ($postseriesSubscriptionList as $psubscription)
	{
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
		if ($numberOfPosts == 0)
		{
			_wpr_expire_followup($psubscription->id);
			continue;
		}
		$timeBetweenPosts = $postseries->frequency;
		$last_post = $psubscription->sequence;
		$currentIndex = floor($daysSinceSubscribing/$timeBetweenPosts);

                if ($currentIndex == $last_post)
                    continue;
		
		//all posts have been sent. expire the post series subscription
		if ($last_post >= count($posts)-1)
		{			
			_wpr_expire_followup($psubscription->id);
			continue;
		}
		$indexToDeliver = $last_post+1;
		$category = $psubscription->eid;
		$postToSend = $posts[$indexToDeliver];
		$sitename = get_bloginfo("name");
		
		
		$meta_key = sprintf("PS-%s-%s-%s",$psubscription->eid,$psubscription->sid,$postToSend->ID);
		$additionalParams = array('meta_key'=>$meta_key);
		
                deliverBlogPost($sid,$postToSend->ID,"You are receiving this blog post as a part of a post series at $name.",true,true,$additionalParams);
		
		$query = "UPDATE ".$prefix."wpr_followup_subscriptions set sequence=$indexToDeliver , last_date='".time()."' where id='".$psubscription->id."';";
		$wpdb->query($query);
		
		$timeThisInstant = time();
		$timeSinceStart = $timeThisInstant-$timeOfStart;
		if ($timeSinceStart > WPR_MAX_POSTSERIES_PROCESS_EXECUTION_TIME)
			return;
	}
	update_option("_wpr_postseries_process_status","stopped");
	
}
function _wpr_expire_followup($id)
{
	global $wpdb;
        $prefix = $wpdb->prefix;
	$timeNow = time();
	$query = "UPDATE ".$prefix."wpr_followup_subscriptions set sequence='-2', last_date='$timeNow' where id='$id';";
	$wpdb->query($query);	
}



function _wpr_process_broadcasts()
{
	global $wpdb;
	$last_cron_status = get_option("_wpr_newsletter_process_status");
	set_time_limit(3600);
	
	
	/*
	When the cron is running the _wpr_newsletter_process_status
	is set to the timestamp at which the cron processing was started.
	
	Before shutting down the _wpr_newsletter_process_status is
	set to 'stopped'.
	
	This cron will run only if the _wpr_newsletter_process_status option
	is set to "stopped" or is empty.
	*/
	
	$timeOfStart = time();
	$timeMaximumExecutionTimeAgo = $timeOfStart - WPR_MAX_NEWSLETTER_PROCESS_EXECUTION_TIME;
	if (!empty($last_cron_status) && $last_cron_status != "stopped")
	{
		$last_cron_status = intval($last_cron_status);
		if ($last_cron_status !=0 && ($last_cron_status > $timeMaximumExecutionTimeAgo))
		{
			return;
		}
	}

    BroadcastProcessor::run();

	delete_option("_wpr_newsletter_process_status");
	add_option("_wpr_newsletter_process_status",$timeOfStart);


	delete_option("_wpr_newsletter_process_status");
	add_option("_wpr_newsletter_process_status","stopped");	
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

    if (!empty($nid))
    {
        foreach ($posts as $num=>$post)
        {
            $pid = $post->ID;
            $query = "SELECT meta_value from ".$wpdb->prefix."postmeta where post_id=$pid and meta_key='wpr-options';";
            $results = $wpdb->get_results($query);
            $option = $results[0]->meta_value;
            $decodedoptions = base64_decode($option);
            $options = unserialize($decodedoptions);
            $theRealPosts[] = $post;
        }
    }
			
		
	return $theRealPosts;

}

function get_rows($query)
{
	global $wpdb;
	return $wpdb->get_results($query);
}

function isValidOptionsArray($options)
{
    return (is_array($options));
}


function deliver_category_subscription($catid,$post)
{
	global $wpdb;
	$getAllBlogSubscribersQuery = sprintf("SELECT a.* FROM  {$wpdb->prefix}wpr_subscribers a,{$wpdb->prefix}wpr_blog_subscription b where b.type='cat' and b.catid=%d and a.id=b.sid and a.active=1 and a.confirmed=1", $catid);
	$subscribers = $wpdb->get_results($getAllBlogSubscribersQuery);

	foreach ($subscribers as $subscriber)
	{
               deliverBlogPost($subscriber->id,$post->ID);
	}

}
