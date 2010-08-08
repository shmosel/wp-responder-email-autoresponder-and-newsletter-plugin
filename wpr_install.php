<?php

function wpresponder_install()
{
	global $wpdb;
	
	$phpVersion = phpversion();
	if (version_compare(PHP_VERSION, '5.0.0', '<'))
        {
               deactivate_plugins(basename(__FILE__)); // Deactivate ourself
               wp_die("Sorry, but you can't run this plugin, it requires PHP 5 or higher.");
	}

	$prefix = $wpdb->prefix;
	
	/*todo: 
	
	The alter table statements below will always generate an error in 
	a new installation.
	
	This is because we are checking if the table exists immediately after
	creating it. Not an elegant solution. But I got stuff to do man!
	
	*/
	
        
	$queries[] = "CREATE TABLE IF NOT EXISTS `".$prefix."wpr_autoresponders` (
	  `nid` int(11) NOT NULL,
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `name` varchar(50) NOT NULL,
	  PRIMARY KEY (`id`)
	);";
	
	$queries[] = "ALTER TABLE  `".$prefix."wpr_autoresponders` ADD UNIQUE KEY `nid` (`nid`,`name`);";
	
	$queries[] = "CREATE TABLE IF NOT EXISTS `".$prefix."wpr_autoresponder_messages` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `aid` int(11) NOT NULL,
	  `subject` text NOT NULL,
	  `htmlenabled` tinyint(1) NOT NULL,
	  `textbody` text NOT NULL,
	  `htmlbody` text NOT NULL,
	  `sequence` int(11) NOT NULL,
	  `attachimages` int(11) NOT NULL,
	  PRIMARY KEY (`id`)
	);";
	
	$queries[] = "CREATE TABLE IF NOT EXISTS `".$prefix."wpr_blog_series` (
	  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
	  `name` varchar(100) NOT NULL,
	  `catid` varchar(100) NOT NULL,
	  `frequency` tinyint(4) NOT NULL,
	  PRIMARY KEY (`id`)
	  
	) ";
	
	$queries[] = "ALTER TABLE ".$wpdb->prefix."wpr_blog_series ADD UNIQUE KEY `name` (`name`)";
	
	$queries[] = "CREATE TABLE IF NOT EXISTS `".$prefix."wpr_blog_subscription` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `sid` int(11) NOT NULL,
            `type` enum('all','cat') NOT NULL,
            `catid` int(11) NOT NULL,
            PRIMARY KEY (`id`)
        ) ";
	
	$queries[] = "ALTER TABLE  `".$prefix."wpr_blog_subscription` ADD UNIQUE KEY `sid` (`sid`,`type`,`catid`);";
        
	$queries[] = "CREATE TABLE IF NOT EXISTS `".$prefix."wpr_custom_fields` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `nid` int(11) NOT NULL,
	  `type` enum('enum','text','hidden') NOT NULL,
	  `name` varchar(50) NOT NULL,
	  `label` varchar(50) NOT NULL,
	  `enum` varchar(100) NOT NULL,
	  PRIMARY KEY (`id`)
	  
	)";
	
	$queries[] = "ALTER TABLE  `".$prefix."wpr_custom_fields` ADD UNIQUE KEY `nid` (`nid`,`name`);";
	
	$queries[] = "CREATE TABLE IF NOT EXISTS `".$prefix."wpr_custom_fields_values` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `nid` int(11) NOT NULL,
	  `sid` int(11) NOT NULL,
	  `cid` int(11) NOT NULL,
	  `value` text NOT NULL,
	  PRIMARY KEY (`id`)
	);";
	
	$queries[] = "ALTER TABLE  `".$prefix."wpr_custom_fields_values` ADD UNIQUE KEY `nid` (`nid`,`sid`,`cid`);";
	
	$queries[] = "CREATE TABLE IF NOT EXISTS `".$prefix."wpr_followup_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL,
  `type` enum('autoresponder','postseries') NOT NULL,
  `eid` int(4) NOT NULL,
  `sequence` smallint(6) NOT NULL,
  `last_date` int(11) NOT NULL,
  `doc` varchar(25) NOT NULL,
  PRIMARY KEY (`id`)
	)  ;";
	
	$queries[] = "ALTER TABLE  `".$prefix."wpr_followup_subscriptions` ADD UNIQUE KEY `sid` (`sid`,`type`,`eid`);";
	
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
	);";
	
	$queries[] = "ALTER TABLE  `".$prefix."wpr_newsletters` ADD UNIQUE KEY `name` (`name`);";
	
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
	) ;";
	
	$queries[] = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."wpr_subscribers` (
    `nid` int(11) NOT NULL,
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `date` varchar(30) NOT NULL,
    `active` tinyint(1) NOT NULL DEFAULT '1',
    `fid` int(11) DEFAULT NULL,
    `confirmed` tinyint(1) NOT NULL DEFAULT '0',
    `hash` varchar(50) NOT NULL,
    PRIMARY KEY (`id`)
    );";
	
	$queries[] = "ALTER TABLE  `".$prefix."wpr_subscribers` ADD UNIQUE KEY `nid_2` (`nid`,`email`);";

        
        $queries[] = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."wpr_subscriber_transfer` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `source` int(10) unsigned NOT NULL,
        `dest` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id`)
        );";
		
	$queries[] = "ALTER TABLE  `".$prefix."wpr_subscriber_transfer` ADD UNIQUE KEY `unsub_from_nid` (`source`,`dest`);";

	
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
	);";
	
	$queries[] = "ALTER TABLE  `".$prefix."wpr_subscription_form` ADD UNIQUE KEY `name` (`name`);";

	
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
	)";

	
	foreach ($queries as $query)
	{
		$wpdb->query($query);
	}
	//get the latest posted 
	//get the post. if it exists.";
	delete_option("wpr_last_post_date");
	
	$args = array('orderby'=> 'date','order'=>'DESC','numberposts'=>1,'post_type'=>'post');
	$posts = get_posts($args);
	if (count($posts) >0 ) //if there are any posts at all
	{
		$post = $posts[0];
		$last_post_date = $post->post_date_gmt;
	}
	else //if there are absolutely no posts in the blog then use the current time.
	{
		$last_post_date = date("Y-m-d H:i:s",time());	
	}
	
	add_option("wpr_last_post_date",$last_post_date);		
	
	$plugindirname = ABSPATH.PLUGINDIR.'/'.basename(str_replace(basename(__FILE__),"",__FILE__));
	$plugindirname = str_replace("\\","/",$plugindirname);

	
	$confirm_subject = file_get_contents("$plugindirname/templates/confirm_subject.txt");
	$confirm_body = file_get_contents("$plugindirname/templates/confirm_body.txt");
	$confirmed_subject = file_get_contents("$plugindirname/templates/confirmed_subject.txt");
	$confirmed_body = file_get_contents("$plugindirname/templates/confirmed_body.txt");
	

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
	 	add_option("wpr_next_cron",time()+300);
         else
            update_option("wpr_next_cron",$confirm_subject);
	if (!get_option("wpr_address"))
	 	add_option("wpr_address","");
  
         
	if (!get_option("wpr_hourlylimit"))
	 	add_option("wpr_hourlylimit","100");
         else
            update_option("wpr_hourlylimit","100");
	if (get_option("wpr_sent_posts"))
	 	add_option("wpr_sent_posts","off");
         else
            update_option("wpr_sent_posts","off");
	createNotificationEmail();
	wpr_enable_tutorial();
	wpr_enable_updates();
//configure the cron to run hourly.

	wp_schedule_event(time(), 'every_five_minutes', 'wpr_cronjob');
	wp_schedule_event(time()+6040000,'daily','wpr_send_errors'); //send weekly error reports.
}
