<?php
function wpr_all_mailouts()
{
	switch ($_GET['action'])
	{
		case 'edit':
		
		_wpr_edit_mailout();
		break;
		default:
		?>
        <?php
		_wpr_pending_mailouts();	
		?>
	<br />
		<input type="button" class="button" value="Create Broadcast" onclick="window.location='admin.phppage=wp-responder-email-autoresponder-and-newsletter-plugin/newmail.php';"/>
		<br />
<br />
	
       

		<?php
		_wpr_finished_mailouts();
	}

}
function _wpr_edit_mailout()
{
	global $wpdb;
	$id = $_GET['id'];
	$query = "select * from ".$wpdb->prefix."wpr_newsletter_mailouts where id=$id and status=0";
	$mailouts = $wpdb->get_results($query);
	if (count($mailouts) ==0)
	{
		?>
        This newsletter has been sent. It cannot be edited.<br />
<br />

        <a href="admin.php?page=allbroadcasts" class="button">&laquo; Back </a>
        <?php	
		return;
	}
	$param = $mailouts[0];
	
	if (isset($_POST['subject']))
	{
	    $subject = $_POST['subject'];
		$nid = $_POST['newsletter'];
		$textbody = trim($_POST['body']);
		$htmlbody = trim($_POST['htmlbody']);
		$whentosend = $_POST['whentosend'];	
		$date = $_POST['date'];
		$htmlenabled  = ($_POST['htmlenabled'] == "on");
		$recipients = $_POST['recipients'];
		$hour = $_POST['hour'];
		$min = $_POST['minute'];
		$id = $_POST['mid'];
		if ($whentosend == "now")
			$timeToSend = time();
		else
		{
			if (empty($date))
			{
				$error = "The date field is required";
				echo "Date is required";
			}
			else
			{
				$sections = explode("/",$date);
				$timeToSend =mktime($hour,$min,0,$sections[0],$sections[1],$sections[2]); 
			}
		}
		if (!(trim($subject) && trim($textbody)))
		{
			$error = "Subject and the Text Body are mandatory.";
		}
		if ($timeToSend < time()  && !$error)
		{
			$error = "The time mentioned is in the past. Please enter a time in the future.";
		}
		if ($htmlenabled && !$error)
		{
			if (empty($htmlbody))
			{
				$error = "HTML Body is empty. Enter the HTML body of this email";
			}
		}
		//if html body is present, it will be sent.
		if (!$htmlenabled)
		{
			$htmlbody = "";
		}
		
		if (!$error)
		{
			$query = "UPDATE ".$wpdb->prefix."wpr_newsletter_mailouts set subject='$subject', textbody='$textbody', htmlbody='$htmlbody',time='$timeToSend',recipients='$recipients',nid='$nid' where id=$id;";
			$wpdb->query($query);
			_wpr_mail_sending();
			return;
		}
			$param = (object)  array("nid"=>$nid,"textbody"=>$textbody,"subject"=>$subject,"htmlbody"=>$htmlbody,"htmlenabled"=>$htmlenabled,"whentosend"=>$whentosend,"time"=>$timeToSend,"title"=>"New Mail");

	}

	wpr_mail_form($param,"new",$error);
	
}

function _wpr_pending_mailouts()
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletter_mailouts where status=0;";
	$mailouts = $wpdb->get_results($query);
	?>
    <script>
	var delurl = '<?php bloginfo("siteurl") ?>/<?php echo PLUGINDIR ?>/wpresponder/delmailout.php';
	var viewurl = '<?php bloginfo("siteurl") ?>/<?php echo PLUGINDIR ?>/wpresponder/viewbroadcast.php';
	var currentDeletion;
	function deleteMailout(id)
	{
		currentDeletion = id;
		if (window.confirm("Are you sure you want to cancel this broadcast? "))
		{
			jQuery.ajax({
							type: "GET",
							url:  delurl+'?mid='+id,
							cache: false,
							success: removeRow
						});
		}
	}
	function removeRow()
	{
		var row = document.getElementById('mailout_'+currentDeletion);
		par = row.parentNode;
		par.removeChild(row);
	}
	function showBroadcast(html)
	{
		var diag = document.createElement("div");
		diag.innerHTML = html;
		jQuery(diag).dialog({
								bgiframe: true,
								modal: true,
								title: "Broadcast Information",
								width: 700,
								height: 500,
								buttons: {
											Ok: function () 
												{
														jQuery(this).dialog('close')
												}
										}
							});
	}
	
	function viewBroadcast(id)
	{
		jQuery.ajax({
						type: "GET",
						url: viewurl+"?mid="+id,
						success:  function(html)
						{
							showBroadcast(html);
						}
					});
	}
	</script>
    <div class="wrap"><h2>Pending Broadcasts</h2></div>
    <table class="widefat">
    <tr>
      <thead>
        <th>Subject</th>
        <th>Newsletter</th>
        <th>To Be Sent at*</th>
        <th>Recipients</th>
        <th>Actions</th>
      </thead>
     </tr>
     <?php
	foreach ($mailouts as $mailout)
	{
		?>
        <tr id="mailout_<?php echo $mailout->id ?>">
           <td><?php echo $mailout->subject ?></td>
           <td><?php $newsletter = _wpr_newsletter_get($mailout->nid);
		   echo $newsletter->name ?></td>
           <td><?php echo date("g:ia d F Y",$mailout->time); ?></td>
           <td><?php $recipients = implode("<br>",explode("%set%",$mailout->recipients));
		   echo ($recipients)?$recipients:"All Subscribers";?></td>
           <td><input type="button" value="Edit" class="button" onclick="window.location='admin.phppage=wp-responder-email-autoresponder-and-newsletter-plugin/allmailouts.php&action=edit&id=<?php echo $mailout->id ?>';" /><input type="button" value="Cancel" class="button" onclick="deleteMailout(<?php echo $mailout->id ?>)" /></td>
        </tr>
        <?php
	}
	?>
    </table>
    <?php
}

function _wpr_finished_mailouts()
{
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletter_mailouts where status=1;";
	$mailouts = $wpdb->get_results($query);
	?>
    <div class="wrap"><h2>Sent Broadcasts</h2></div>
    <table class="widefat">
    <tr>
      <thead>
        <th>Subject</th>
        <th>Newsletter</th>
        <th>Sent at*</th>
        <th>Recipients</th>
        <th>Actions</th>
      </thead>
     </tr>
     <?php
	foreach ($mailouts as $mailout)
	{
		?>
        <tr>
           <td><?php echo $mailout->subject ?></td>
           <td><?php $newsletter = _wpr_newsletter_get($mailout->nid);
		   echo $newsletter->name ?></td>
           <td><?php echo date("g:ia d F Y",$mailout->time); ?></td>
           <td><?php $recipients = implode("<br>",explode("%set%",$mailout->recipients));
		   echo ($recipients)?$recipients:"All Subscribers";
		   ?></td>
           <td><input type="button" value="View" onclick="viewBroadcast(<?php echo $mailout->id ?>);" class="button" /></td>
        </tr>
        <?php
	}
	?>
    </table>
    
<br />
    * Time is approximate. Actual send time depends on the frequency you set for the wordpress cron job or amount of traffic you get.
    <?php
}
?>
