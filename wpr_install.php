<?php

function wpresponder_install()
{
	global $wpdb;

	add_filter('cron_schedules','wpr_cronshedules');

	$prefix = $wpdb->prefix;
	$queries[] = "CREATE TABLE IF NOT EXISTS `".$prefix."wpr_autoresponders` (
	  `nid` int(11) NOT NULL,
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `name` varchar(50) NOT NULL,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `nid` (`nid`,`name`)
	) TYPE=InnoDB ;";
	
	$queries[] = "CREATE TABLE IF NOT EXISTS `".$prefix."wpr_autoresponder_messages` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `aid` int(11) NOT NULL,
	  `subject` text NOT NULL,
	  `htmlenabled` tinyint(1) NOT NULL,
	  `textbody` text NOT NULL,
	  `htmlbody` text NOT NULL,
	  `sequence` int(11) NOT NULL,
	  `attachimages` int(11) NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `id` (`id`)
	) TYPE=InnoDB;
	";
	
	$queries[] = "CREATE TABLE IF NOT EXISTS `".$prefix."wpr_blog_series` (
	  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
	  `name` varchar(100) NOT NULL,
	  `catid` varchar(100) NOT NULL,
	  `frequency` tinyint(4) NOT NULL,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `name` (`name`)
	) TYPE=InnoDB;";
	$queries[] = "CREATE TABLE IF NOT EXISTS `".$prefix."wpr_blog_subscription` (
	  `id` int(11) NOT NULL auto_increment,
	  `sid` int(11) NOT NULL,
	  `type` enum('all','cat') NOT NULL,
	  `catid` int(11) NOT NULL,
	  PRIMARY KEY  (`id`)
	);";

        
	$queries[] = "CREATE TABLE IF NOT EXISTS `".$prefix."wpr_custom_fields` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `nid` int(11) NOT NULL,
	  `type` enum('enum','text','hidden') NOT NULL,
	  `name` varchar(50) NOT NULL,
	  `label` varchar(50) NOT NULL,
	  `enum` varchar(100) NOT NULL,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `nid` (`nid`,`name`)
	) TYPE=InnoDB ;";
	

	$queries[] = "CREATE TABLE IF NOT EXISTS `".$prefix."wpr_custom_fields_values` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `nid` int(11) NOT NULL,
	  `sid` int(11) NOT NULL,
	  `cid` int(11) NOT NULL,
	  `value` text NOT NULL,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `nid` (`nid`,`sid`,`cid`)
	) TYPE=InnoDB ;";
	
	$queries[] = "CREATE TABLE IF NOT EXISTS `".$prefix."wpr_followup_subscriptions` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `sid` int(11) NOT NULL,
	  `type` enum('autoresponder','postseries') NOT NULL,
	  `eid` int(4) NOT NULL,
	  `sequence` smallint(6) NOT NULL,
	  `last_date` int(11) NOT NULL,
	  `doc` varchar(25) NOT NULL,
	  PRIMARY KEY (`id`)
	) TYPE=InnoDB ;";
	
	$queries[] = "CREATE TABLE IF NOT EXISTS `".$prefix."wpr_newsletters` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `name` varchar(50) NOT NULL,
	  `reply_to` varchar(50) NOT NULL,
	  `description` text NOT NULL,
	  `confirm_subject` varchar(100) NOT NULL,
	  `confirm_body` text NOT NULL,
	  `confirmed_subject` varchar(100) NOT NULL,
	  `confirmed_body` text NOT NULL,
	  `fromname` varchar(50) NOT NULL,
	  `fromemail` varchar(100) NOT NULL,
	  PRIMARY KEY (`id`)
	) TYPE=InnoDB ;";
	
	$queries[] = "CREATE TABLE IF NOT EXISTS `".$prefix."wpr_newsletter_mailouts` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `nid` int(11) NOT NULL,
	  `subject` varchar(100) NOT NULL,
	  `textbody` text NOT NULL,
	  `htmlbody` text NOT NULL,
	  `time` varchar(25) NOT NULL,
	  `status` tinyint(1) NOT NULL,
	  `recipients` text NOT NULL,
	  `attachimages` tinyint(1) NOT NULL,
	  PRIMARY KEY (`id`)
	) TYPE=InnoDB ;";
	
	$queries[] = "CREATE TABLE IF NOT EXISTS `".$prefix."wpr_subscribers` (
	  `nid` int(11) NOT NULL,
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `name` varchar(100) NOT NULL,
	  `email` varchar(100) NOT NULL,
	  `date` varchar(30) NOT NULL,
	  `active` tinyint(1) NOT NULL DEFAULT '1',
	  `fid` int(11) DEFAULT NULL,
	  `confirmed` tinyint(1) NOT NULL DEFAULT '0',
	  `hash` varchar(50) NOT NULL,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `nid_2` (`nid`,`email`),
	  KEY `nid` (`nid`)
	) TYPE=InnoDB ;";

	
	$queries[] = "CREATE TABLE IF NOT EXISTS `".$prefix."wpr_subscription_form` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `name` varchar(50) NOT NULL,
	  `return_url` varchar(150) NOT NULL,
	  `followup_type` enum('postseries','autoresponder','none') NOT NULL,
	  `followup_id` int(11) NOT NULL,
	  `blogsubscription_type` enum('all','cat','none') NOT NULL,
	  `blogsubscription_id` int(11) NOT NULL,
	  `nid` int(11) NOT NULL,
	  `custom_fields` varchar(100) NOT NULL,
	  `confirm_subject` text NOT NULL,
	  `confirm_body` text NOT NULL,
	  `confirmed_subject` text NOT NULL,
	  `confirmed_body` text NOT NULL,
	  `confirm_url` varchar(100) NOT NULL,
	  PRIMARY KEY (`id`)
	) TYPE=InnoDB ;";
	
	$queries[] = "CREATE TABLE IF NOT EXISTS `".$prefix."wpr_queue` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `from` varchar(200) NOT NULL,
	  `fromname` varchar(50) NOT NULL,
	  `to` varchar(200) NOT NULL,
	  `subject` text NOT NULL,
	  `htmlbody` text NOT NULL,
	  `textbody` text NOT NULL,
	  `headers` text NOT NULL,
	  `sent` int(11) NOT NULL,
	  `htmlenabled` tinyint(1) NOT NULL,
	  `attachimages` tinyint(1) NOT NULL,
	  PRIMARY KEY (`id`)
	) TYPE=MyISAM;";

	
	foreach ($queries as $query)
	{
		$wpdb->query($query);
	}
	//get the latest posted 
	//get the post. if it exists.";
	delete_option("wpr_last_post_date");
	$args = array('orderby'=> 'date','order'=>'DESC','numberposts'=>1,'post_type'=>'post');
	$posts = get_posts($args);
	$post = $posts[0];
	$last_post_date = $post->post_date;
	add_option("wpr_last_post_date",$last_post_date);
		
		
	$confirm_subject = file_get_contents(ABSPATH. PLUGINDIR. "/wpresponder/templates/confirm_subject.txt");
	$confirm_body = file_get_contents(ABSPATH. PLUGINDIR. "/wpresponder/templates/confirm_body.txt");
	$confirmed_subject = file_get_contents(ABSPATH. PLUGINDIR. "/wpresponder/templates/confirmed_subject.txt");
	$confirmed_body = file_get_contents(ABSPATH. PLUGINDIR. "/wpresponder/templates/confirmed_body.txt");


        file_put_contents("./installog","These are the values from the files: '$confirm_body', '$confirm_subject'");

	if (!get_option("wpr_confirm_subject"))
		add_option("wpr_confirm_subject",$confirm_subject);
        else
            update_option("wpr_confirm_subject",$confirm_subject);
        
	if (!get_option("wpr_confirm_body"))
		add_option("wpr_confirm_body",$confirm_body);
        else
            update_option("wpr_confirm_body",$confirm_body);

	if (!get_option("wpr_confirmed_subject"))
		add_option("wpr_confirmed_subject",$confirmed_subject);
        else
            update_option("wpr_confirmed_subject",$confirmed_subject);
        
	if (!get_option("wpr_confirmed_body"))
		add_option("wpr_confirmed_body",$confirmed_body);
        else
            update_option("wpr_confirmed_body",$confirmed_body);
		//the cron variable.
	if (!get_option("wpr_next_cron"))
	 	add_option("wpr_next_cron",time()+600);
         else
            update_option("wpr_next_cron",$confirm_subject);
	if (!get_option("wpr_address"))
	 	add_option("wpr_address","");
         else
            update_option("wpr_address","");

         
	if (!get_option("wpr_hourlylimit"))
	 	add_option("wpr_hourlylimit","100");
         else
            update_option("wpr_hourlylimit","100");
	if (!get_option("wpr_attachimages"))
	 	add_option("wpr_attachimages","off");
         else
            update_option("wpr_attachimages","off");
	if (get_option("wpr_sent_posts"))
	 	add_option("wpr_sent_posts","off");
         else
            update_option("wpr_sent_posts","off");

//configure the cron to run hourly.
	wp_schedule_event(time(), 'every_five_minutes', 'wpr_cronjob');
	wp_schedule_event(time()+6040000,'daily','wpr_send_errors'); //send weekly error reports.
     
}

?>
