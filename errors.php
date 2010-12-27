<?php
function wpr_errorlist()
{
	global $wpdb;
	$query = "select * from wpr_errors order by date desc";
	$errors = $wpdb->get_results($query);
	?>
<div class="wrap"><h2>Subscription Errors</h2></div>
Sometimes a subscriber may try to subscribe to a newsletter, autoresponder, post series or a blog category that doesn't exist because you removed it. Such errors are logged and listed below. 
<table class="widefat">
  <tr>
   <thead>
     <th>Error</th>
     <th>Time</th>
   </thead>
 </tr>
 <?php
 if (count($errors))
 {
		foreach ($errors as $error)
		{
		 ?>
		 <tr>
			<td><?php echo $error->error ?></td>
			<td><?php echo date("g:i d F Y",$error->time); ?></td>
		 </tr>
		 <?php
		}
 }
 else
 {
	 ?>
     <tr>
      <td colspan="20">
         <div align="center">-No Errors Encountered-</div>
      </td>
      </tr>
      <?php
 }
 ?>
</table>
    <?php
}

?>
