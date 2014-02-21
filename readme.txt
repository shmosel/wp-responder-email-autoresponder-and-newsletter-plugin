=== Javelin Email Marketing ===  
Contributors: nodesman
Donate link: http://wpresponder.com/donate/  
Tags: email, newsletter, autoresponder, subscribers, mailing list, follow up email autoresponder, email marketing  
Requires at least: 2.8.2  
Tested up to: 3.8.1
Stable tag: 5.3.12

Gather subscribers, manage them in separate newsletters, send follow-up emails, send broadcasts, send blog post deliveries all in one plugin. 

== Description ==  

This is a email newsletter and autoresponder plugin. With this plugin you can create:

* Create unlimited number of newsletter lists
* Create followup email responses that can be schedueld to go out after a specific number of days since the subscriber subscribes.
* Add subcription forms to your sidebar using widgets.
* Provide email subscriptions to your blog without using third party services like Feedburner
* Generate subscription forms and then use them on your website anywhere. 
* Collect more information about your subscribers by generating custom fields for your subscription forms. 
* Schedule e-mail broadcasts to your email newsletters in text/html. You can even send the broadcast to specific sections of your newsletter by selecting them using the custom fields. 
* Provide email subscription to specific categories in your blog
* Import your subscribers from Feedburner and Aweber.
* Define rules to unsubscribe users from one newsletter if they subscribe to another newsletter. 

== Installation ==

To install WP Responder:

* Download the Latest version from :

http://wordpress.org/extend/plugins/wp-responder-email-autoresponder-and-newsletter-plugin/

* Extract the downloaded archive to a local directory. 
* Upload the wpresponder directory to the wp-content/plugins directory of your wordpress installation.
* Activate the plugin through the 'Plugins' menu in WordPress
* Enter your postal address to the Newsletter > Settings page to avoid spam complaints. 

== Frequently Asked Questions ==

**The confirmation link is missing in the confirmation e-mail. Why?**

If you are running version 4.7 or below, please upgrade it to 4.8 or above. This is because of the long URL that is generated for subscription confirmation gets stripped out by some e-mail providers. 

**I see a Fatal Error when running the installation.Why is this?**  

This is because WP Autoresponder is designed to work only with PHP5. You are most likely using PHP4. 

**My email broadcasts take forever to go out.Why does this happen?**
  
There may be two causes for this:  

* This happens because WP Autoresponder relies on wordpress's cron system. Wordpress's cron system relies on your webstie traffic to trigger the various scheduled activities. If your website has low traffic then this will not work. The solution is to **create a cron job on wp-cron.php found in the wordpress root to solve this probelm**.  
* Further the hourly limit on the number of emails to go out in a single hour is set to 100 by default. Increase it or set it to 0 if you want to remove the limit on the number of emails that go out in an hour. The hourly limit can be set at Wordpress Dashboard > Newsletters > Settings

**I need your help  to integrate WP Responder wwith some application/customize it/etc**

Email me at: raj@nodesman.com


== ChangeLog ==

**Javelin v5.3.12**

* Fixed inability to edit subscription form
* Fixed autoresponder processor using the email's offset date as days since last email instead of days since subscription
* Some more code cleanup

**WP Autoresponder v5.3.10**

* Fixed unable to create new newsletter
* Fixed unable to save settings to subscription form
* Complete rewrite of email enqueue API and newsletter broadcast delivery processor.

**WP Autoresponder v5.3.6**

* Fixed issue with rich text editor not loading in autoresponder screen
* Fixed wrong wording in german language for subscription status on subscribers management page. 
* Fixed wrong from name in broadcast emails. 

**WP Autoresponder v5.3.4** 

* Fixed issue with autoresponder process not functioning on servers running PHP 5 < 5.3
* Fixed paging element not included in the autorespodner messages management page. 
* Fixed inability to use [!name!] in autoresponder emails. 

**WP Autoresponder v5.3.3**

* Complete rewrite of autoresponder process and UI, added behaviror - autoresponder now resumes progress from whence it left off if the plugin is disabled for a certain period of time. The process supports processing atleast 100,000 subscribers in one run.
* Fixed issue with non-delivery of blog posts
* Fixed inability to save changes to a newsletter
* Fixed WPR interfering with other cron dependent plugins.
* Fixed non-functional Import functionality. 
* Fixed saving subscription form settings causing slashes getting added to confirmation emails.
* Removed unused files from application.
* Updated swiftmailer to v4.1.7
* Updated ckeditor to version v4.0
* Removed non-functional customization of recipeint feature in blog broadcast
* Removed unused provision to customize blog post email body in [Posts] > Add New/Edit screen.

**WP Autoresponder v5.2.7 **

Code Changes:

* Centrailzed delivery record insertion for blog posts: The delivery record for blog post is now inserted at the function that delivers blog post as against in the processes that perform blog post deliveries
* Properly escaped all queries in optin.php
* Prevented SQL errors from occuring during initial installation
* Prevented crons from being scheduled again when they are already scheduled
* Modified autoresponder subscribers query to not return duplicate rows
* Made the subscription form's follow-up selection interface extensible so that a custom follow-up type can be added by an external plugin
* Removed ad placeholder from settings page. Some other time. In some other form. 
* Fixed incorrect query arguments for fetching list of categories in blog category processing
* Fixed administration area slowed down by the plugin

New Features:

* Added mechanism to delete subscription forms.




