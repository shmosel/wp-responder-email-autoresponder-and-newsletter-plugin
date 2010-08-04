=== WP Responder Email Newsletter and Autoresponder Plugin===  
Contributors: Raj Sekharan  
Donate link: http://wpresponder.com/donate/  
Tags: email, newsletter, autoresponder, subscribers, mailing list, follow up email autoresponder  
Requires at least: 2.8.2  
Tested up to: 3  
Stable tag: 4.9.2

With WP Responder you can create email newsletters, follow up autoresponders and provide subscription to blog via e-mail.  

== Description ==  

This is a email newsletter and autoresponder plugin. With this plugin you can create:

* Create unlimited number of newsletter lists
* Create followup email responses that can be schedueld to go out after a specific number of days since the subscriber subscribes.
* Provide email subscriptions to your blog without using third party services like Feedburner
* Generate subscription forms and then use them on your website anywhere. 
* Collect more information about your subscribers by generating custom fields for your subscription forms. 
* Schedule e-mail broadcasts to your email newsletters in text/html. You can even send the broadcast to specific sections of your newsletter by selecting them using the custom fields. 
* Provide email subscription to specific categories in your blog
* Import your subscribers from Feedburner and Aweber.

The newsletters work indepedent of the working of the blog. This plugin is aimed as an alternative to Aweber, Mailchimp and other paid services. You DO NOT need a third party service or license to use this plugin

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

This is because WP Responder is designed to work only with PHP5. You are most likely using PHP4. 

**My email broadcasts take forever to go out.Why does this happen?**
  
There may be two causes for this:  

* This happens because WP Responder relies on wordpress's cron system. Wordpress's cron system relies on your webstie traffic to trigger the various scheduled activities. If your website has low traffic then this will not work. The solution is to **create a cron job on wp-cron.php found in the wordpress root to solve this probelm**.  
* Further the hourly limit on the number of emails to go out in a single hour is set to 100 by default. Increase it or set it to 0 if you want to remove the limit on the number of emails that go out in an hour. The hourly limit can be set at Wordpress Dashboard > Newsletters > Settings

**I need your help  to integrate WP Responder wwith some application/customize it/etc**

Please visit the plugin website:   
  
http://www.expeditionpost.com/
  
And get in touch with me. 


== ChangeLog ==

**WP Responder 4.9.2**

Bug fixes:

Bug Fixes:

* In the autoresponder series page, the HTML body is disabled by default. 
* When preview email is sent there is an error when there is an image in the post. 
* The preview email doesn't replace the [!email!] field with the email address. 
* The  optin page has a spelling mistake in the title - "addres"
* The subscription form should show only the autoresponders that have been created for the currently selected newsletter. 
* There are some empty rows in the blog subscription table - Rows with null data. 
* The preview email takes get_bloginfo("adminemail") when the from email is not set in the preview email form in the autoresponder messages page.
* There is a field called [!!] in the autoresponder page in the list of custom fields in the new broadcast field.
*  Breaks are inserted in the confirm and confirmation e-mail templates when they are saved or displayed in the form.
* Scheduling/Rescheduling a broadcast threw an error saying you were trying to schedule a broadcast in the future.
* If the src of images specified in a e-mail's HTML body is a HTTP url, then the user will see warnings when previewing e-mail or running the WPR Cron.
* The new broadcast form did not validate the subject, textbody and HTML body fields. Now it does. 

New Features:

* Added mechanism to delete subscription forms.


