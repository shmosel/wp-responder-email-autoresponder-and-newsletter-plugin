<?php

function wpr_dashboard()
{
	global $wpdb;
	$action = @$_GET['action'];
	switch ($action)
	{		
		case 'graph':
		$nid = (int) $_GET['nid'];
		show_graph($nid);
		break;
		default:
	?>
<style>
#throw {
	font-family:Tahoma, Geneva, sans-serif;
	font-size:17px;
	background-color: #dae5d8;
	color:#666;
	padding: 10px;
}
#throw td {
	padding: 10px;
	font-weight: bold;
}
#statstable {
	background-color: #dae5d8;
	margin-bottom:10px;
}
.statrow {
	background-color:#FFF;
}

#news {
	
}
</style>
<div style="display:block"><img src="<?php echo get_option("home"); ?><?php echo "/".PLUGINDIR."/wpresponder/images/dash.jpg" ?>" /></div><br />
<div style="display:block;">


<table width="100%" >
  <tr>
    <td valign="top"><table width="100%">
        <tr>
          <td>
            <br />
            <div style="display:block;">
                <img src="<?php echo get_option("home"); ?><?php echo "/".PLUGINDIR."/wpresponder/images/subscount.jpg" ?>">
            <table cellpadding="10" id="statstable" width="100%">
                <br/>
              <tr id="throw">
                <td>Newsletter Name</td>
                <td>Confirmed Subscribers</td>
   				<td>Unconfirmed Subscribers</td>
   				<td>Unsubscribed Subscibers</td>
                
              </tr>
              <?php
			  
		$query = "select * from ".$wpdb->prefix."wpr_newsletters";
		$ns = $wpdb->get_results($query);
		foreach ($ns as $n)
		{
			?>
              <tr class="statrow">
                <td style="padding: 10px;"><?php echo $n->name ?></td>
                <td style="padding: 10px;"><?php
			   $nid = $n->id;
			   $query = "select count(*) num from ".$wpdb->prefix."wpr_subscribers where nid=$nid and active=1 and confirmed=1;";
			   $num = $wpdb->get_results($query);
			   $num = (int) $num[0]->num;
			   echo $num;
			   ?></td>
               <td style="padding: 10px;"><?php
			   $nid = $n->id;
			   $query = "select count(*) num from ".$wpdb->prefix."wpr_subscribers where nid=$nid and active=1 and confirmed=0;";
			   $num = $wpdb->get_results($query);
			   $num = (int) $num[0]->num;
			   echo $num;
			   ?></td>
               <td style="padding: 10px;"><?php
			   $nid = $n->id;
			   $query = "select count(*) num from ".$wpdb->prefix."wpr_subscribers where nid=$nid and active=0 and confirmed=0;";
			   $num = $wpdb->get_results($query);
			   $num = (int) $num[0]->num;
			   echo $num;
			   ?></td>
                
              </tr>
              <?php
		}
		
		?>
            </table>
            </div>
            <hr size="1" color="#CCCCCC"/>
<div align="center"><div style="clear:both;display:block"><a href="http://www.krusible.com"><img src="http://www.wpresponder.com/dashboard.png" width="800" height="120" /></a></div></div>

<hr size="1" color="#CCCCCC"/>
<h2>Subscribe to the WP Responder Email Newsletter</h2>

Enter your name and email address below to subscribe to the  WP Responder newsletter. <br />

When you subscribe to the newsletter you get:
<table>
  <tr>
    <td style="padding-right:20px;">
<ul style="list-style:disc; padding-left: 40px; padding-top:20px;">
    <li>Get Notified Of New Releases Including Critical Security Updates</li>
    <li>Get Video Tutorials On Using WP Responder.</li>
    <li>Strategies and Tactics on E-mail Marketing</li>
</ul> 
<div style="padding-top:15px; ">I will not sell or rent your email address. Nor will I overload you with sales pitches. I promise!</div>
   </td>
   <td style="padding-left:40px; border: 1px solid #ccc; background-color:#FFF; padding:20px;"><form action="http://www.expeditionpost.com/wp-content/plugins/wpresponder/optin.php" method="post">
  <input type="hidden" name="blogsubscription" value="none" />
    <input type="hidden" name="newsletter" value="4" />
      <input type="hidden" name="fid" value="9" />
      <table>
    <tr>
      <td>Name:</td>
      <td><input type="text" name="name" /></td>
    </tr>
    <tr>
      <td>E-Mail Address:</td>
      <td><input type="text" name="email" />
    </tr>
        <tr>
      <td colspan="2" align="center"><input class="button-primary" type="submit" value="Subscribe to WP Responder Newsletter" /></td>
    </tr>
  </table>
</form>
</td>
  </tr>
</table>

<hr size="1" color="#CCCCCC"/>
            <div id="news"> <a href="#"><img src="<?php echo get_option("home") ?>/<?php echo PLUGINDIR ?>/wpresponder/images/expostnews.jpg" /></a>
              <?php 
$rss = fetch_feed("http://feeds.feedburner.com/ExpeditionPost");
$type = get_class($rss);
if ($type != "WP_Error")
{
	$rss->handle_content_type();
?>
              <ul>
                <?php
$count=0;
foreach ($rss->get_items() as $item)
{
  if ($count > 4) 
  break;
?>
                <li><a href="<?php echo $item->get_link(0); ?>">
                  <?php $title = $item->get_title();
echo $title;
?>
                  </a><br />
                  <?php echo $item->get_description(); ?> </li>
                <br />
                <?php
  $count++;
}
?>
              </ul>
<?php
}
else
{
	?><br />
<br />
Unable to fetch the feed.<br />
<br />
<?php
}
     ?>       </div>

            <div id="reportbug"> <a href="#"><img src="<?php echo get_option("home") ?>/<?php echo PLUGINDIR ?>/wpresponder/images/bug.jpg" /></a>
              <form method="post" id="reportform" action="http://www.expeditionpost.com/wpr/sb.php">
                <table width="100%">
                  <tr>
                    <td>Name:</td>
                    <td><label>
                        <input type="text" name="name" id="name" />
                      </label></td>
                  </tr>
                  <tr>
                    <td>E-Mail Address:</td>
                    <td><label>
                        <input type="text" name="email" id="email" />
                      </label></td>
                  </tr>
                  <tr>
                    <td>Description:</td>
                    <td><label>
                        <textarea name="desc" id="desc" cols="60" rows="6"></textarea>
                      </label></td>
                  </tr>
                  <tr>
                    <td>Steps To Replicate:</td>
                    <td><label>
                        <textarea name="stepstoreplicate" id="stepstoreplicate" cols="60" rows="5"></textarea>
                      </label></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td><label>
                        <input name="button" class="button-primary" type="submit" id="button" onclick="MM_validateForm('name','','R','email','','RisEmail','title','','R','desc','','R','stepstoreplicate','','R');return document.MM_returnValue" value="Submit Bug" />
                      </label></td>
                  </tr>
                </table>
              </form>
            </div></td>
          <td></td>
        </tr>
      </table></td>
  </tr>
</table>
</div>
<?php
		break;
	}
}

?>
