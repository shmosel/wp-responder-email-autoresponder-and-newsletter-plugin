<?php
include("subscriber.lib.php");
function wpr_subscribers()
{
	$action = $_GET['action'];
	
	switch ($action)
	{
		case 'profile':
		$id = $_GET['sid'];
		$subscriber = _wpr_subscriber_get($id);
		_wpr_subscriber_profile($subscriber);
		break;
		case 'nmanage':
		_wpr_subscriber_nmanage();
		break;
		default:
		_wpr_subscriber_home();
	}
}

function _wpr_subscriber_profile($subscriber)
{
	global $wpdb;
	if (isset($_POST['action']))
	{
		switch ($_POST['action'])
		{
			case 'delete':
			
			break;
			
			case 'unsubscribe':
			$newsletters = $_POST['newsletters'];
			foreach ($newsletters as $newsletter)
			{
				$query = "update ".$wpdb->prefix."wpr_subscribers set active=0 where nid=".$newsletter." and email='".$subscriber->email."'";
				
				$wpdb->query($query);
				
			}
			?>
            <script>window.history.go(-2);</script>
            <?php
			return;
			
			break;
		}
	}
	?>
<div class="wrap"><h2>Profile</h2></div>

<table>
  <tr>
    <td width="300">Name: </td>
    <td><?php 
				   $query = "select DISTINCT name from ".$wpdb->prefix."wpr_subscribers where email='".$subscriber->email."' order by active desc";
				   $results = $wpdb->get_results($query);
				   $names = array();
				   foreach ($results as $name)
				   {
					   array_push($names,$name->name);
				   }
				   $theName = implode(", ",$names);
				   echo $theName;
				   ?>
                  </td>
                  </tr>
                  <tr>
                    <td>E-Mail Address: </td>
                    <td><?php echo $subscriber->email ?>
                    </td>
                    </tr>
                    </table>
<form action="<?php print $_SERVER['REQUEST_URI'] ?>" method="post">
<h3>Remove Subscription To:</h3>
<?php

$query = "select distinct a.id id, a.name name from ".$wpdb->prefix."wpr_newsletters a, ".$wpdb->prefix."wpr_subscribers b where a.id=b.nid and b.email='".$subscriber->email."' and b.active=1";

$subscribedNewsletters = $wpdb->get_results($query);
foreach ($subscribedNewsletters as $newsletter)
{
	?>
    <input type="checkbox" name="newsletters[]" value="<?php echo $newsletter->id ?>" id="news_<?php echo $newsletter->id ?>" /> <label for="news_<?php echo $newsletter->id ?>"> <?php echo $newsletter->name ?></label><br />
    <?php

	
}
?> 
<input type="hidden" value="unsubscribe" name="action" />
<input type="submit" value="Unsubscribe" class="button" />
</form><br />

<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
<a href="javascript:window.history.go(-1);" class="button">&laquo; Back</a>
<input type="hidden" name="action" value="delete" />
<input type="submit" value="Delete This Subscriber"  class="button" />
</form>

    <?php
}


function _wpr_subscriber_nmanage()
{
	$nmact = $_GET['nmact'];
	switch ($nmact)
	{		
	
		
		default:
		_wpr_subscriber_nmanage_home();
	}
}

function _wpr_subscriber_nmanage_home()
{
	global $wpdb;
	$nid = $_GET['nid'];
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_subscribers where nid=$nid order by active desc";
	$subscribers = $wpdb->get_results($query);
	$newsletter = _wpr_newsletter_get($nid);
	if (isset($_POST['search_form']))
	{
		$keyword = $_POST['keyword'];
		$type = $_POST['type'];
		if ($type == "Name")
		{
			$query = "select * from ".$wpdb->prefix."wpr_subscribers where name like '%$keyword%' and nid=".$nid." order by active desc";
		}
		else
		{
			$query = "select * from ".$wpdb->prefix."wpr_subscribers where email like '%$keyword%' and nid=".$nid;;
		}

		$subscribers = $wpdb->get_results($query);
		?>
        <div class="wrap"><h2>Search for '<?php echo $_POST['keyword']; ?>'</h2></div>
        <?php
		_wpr_subscriber_search_form();
		$back = "page=wpresponder/subscribers.php&action=nmanage&nid=$id";
		_wpr_subscriber_list($subscribers,true,$back);
		return;
	}
	
	?>
    <div class="wrap"><h2>Manage Subscribers of <?php echo $newsletter->name ?></h2></div>  
   <?php _wpr_subscriber_search_form(); 
   $backUrl = "page=wpresponder/subscribers.php";
	_wpr_subscriber_list($subscribers,true,$backUrl);
}
function _wpr_subscriber_search_form()
{
	?><div style="float:right; border: 1px solid #ccc; padding:10px; background-color:#f0f0f0;">
     <form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
    Search: <input type="text" name="keyword" size="20" />
    <select name="type">
      <option>Name</option>
      <option>E-Mail</option>
    </select>
    <input type="hidden" name="search_form" value="1" />
    <input type="submit" value="Search" />
    </form>
    </div><br /><br />
<br />
    <?php
}
function _wpr_subscriber_home()
{
	global $wpdb;
	$query = "select * from ".$wpdb->prefix."wpr_newsletters";
	$newsletters = $wpdb->get_results($query);
	?>
    <div class="wrap"><h2>Manage Newsletter Subscribers</h2></div>
    <table class="widefat">
    <tr>
      <thead>
        <th>Name</th>
        <th>Manage</th>
        </thead>
     </tr>
    <?php
	if (count($newsletters))
	{
		foreach ($newsletters as $newsletter)
		{
			
		?>
	 <tr> 
		<td><?php echo $newsletter->name ?></td>
		<td><a href="admin.php?page=wpresponder/subscribers.php&action=nmanage&nid=<?php echo $newsletter->id ?>" class="button">Manage Subscribers</a>&nbsp;</td>
		</tr>
		
		<?php
		
		}
	}
	else
	{
		?>
        <tr>
         <td colspan="10" align="center">No Subscribers Found</td>
        </tr>
        <?php
	}
	?>
    </table>
     <div class="wrap"><h2>All Subscribers</h2></div>
    <?php
	$query = "SELECT DISTINCT `email` from ".$wpdb->prefix."wpr_subscribers ";
	$emails = $wpdb->get_results($query);
	$subscribers = array();
	foreach ($emails as $email)
	{
		$query = "select * from ".$wpdb->prefix."wpr_subscribers where email='".$email->email."' limit 1";
		$results = $wpdb->get_results($query);
		$row = $results[0];
		array_push($subscribers,$row);
		
	}
	$subscribers = array_reverse($subscribers);
	_wpr_subscriber_list($subscribers,true);
}

function _wpr_subscriber_list($subscribers,$allNewslettersMode=true,$backUrl="")
{
	global $wpdb;
	?>    <table class="widefat">
     <tr>
       <thead>
         <th>Name(s)</th>
         <th>E-Mail</th>
         <th>All Active Subscription(s)</th>
         <th>Active</th>
         <th>Actions</th>
       </thead>
      
      </tr>
      <?php
	  if (count($subscribers))
	  {
			foreach ($subscribers as $subscriber)
			{
				$prefix = $wpdb->prefix;
				?>
				<tr>
				   <td><?php 
				   $query = "select DISTINCT name from ".$wpdb->prefix."wpr_subscribers where email='".$subscriber->email."'";
				   $results = $wpdb->get_results($query);
				   $names = array();
				   foreach ($results as $name)
				   {
					   array_push($names,$name->name);
				   }
				   $theName = implode(", ",$names);

				   echo $theName;
				   ?></td>
				   <td><?php echo $subscriber->email ?></td>
			  <?php if ($allNewslettersMode) 
					{
						?>     <td><?php
						   
						   $query = "select distinct a.name from ".$prefix."wpr_newsletters a, ".$prefix."wpr_subscribers b where a.id=b.nid and b.email='".$subscriber->email."' and b.active=1";
						   $subscribedNewsletters = $wpdb->get_results($query);
						   $wpdb->print_error();
						   $list = array();
						   if (count($subscribedNewsletters))
						   {
							   foreach ($subscribedNewsletters as $newsletter)
							   {
								   
								   array_push($list,$newsletter->name);
								   
							   }
						   }
						   else
						   {
							   echo "--None--";
						   }
						   $newsletters = implode(", ",$list);
						   echo $newsletters;
						   ?>
						   </td><?php
				   }
				   ?>
                   <td><?php if ($subscriber->active==1) { echo "Active"; } else { echo "Inactive"; } ?></td>
                   <td>
				   <a href="admin.php?page=wpresponder/subscribers.php&action=profile&sid=<?php echo $subscriber->id ?>" class="button">Edit</a>&nbsp;
				   </td>
				   </tr>
				   <?php
			}
	  }
	  else
	  {
		  ?>
          <tr>
           <td colspan="10" align="center">-No Subscribers- </td>
           </tr>
           <?php
	  }
	?>
    </table>
<br />
<br />
<?php if ($backUrl) { ?>    <a href="admin.php?<?php echo $backUrl; ?>" class="button"> &laquo; Back </a> <?php } ?>
    <?php
}
?>
