<?php
ob_start();
?><h1 style="font-family:Georgia, 'Times New Roman', Times, serif; font-weight:normal;">Upload your subscribers file</h1>
<?php if ($error) { ?><div class="error fade"><?php echo $error ?></div><?php } ?>
Upload the CSV file containing the list of subscribers.
<form enctype="multipart/form-data" action="admin.php?<?php echo $_SERVER['QUERY_STRING'] ?>" method="post">
<input type="hidden" name="thirdstep_wpnonce" value="<?php $nonce = wp_create_nonce('importwizard_wpnonce'); echo $nonce; ?>" />
Import File:  <input type="file" name="feedimport" />
<blockquote>This CSV file was exported from: <br>
<label for="feed"><input type="radio" id="feed" checked="checked" name="type" value="feedburner"> Feedburner</label><br>

<label for="wpresponder"><input type="radio" id="feed" name="type" value="wpresponder"> Another WP Responder plugin installation in another blog</label><br>
<label for="wpresponder"><input type="radio" id="feed" name="type" value="aweber" disabled="disabled">Aweber (will be available in future versions)</label><br>
<small>Note: CSV files in only the above formats are supported.</small>
</blockquote>
  <input type="submit" value="Finish" class="button-primary"/>
</form>
<?php
$content = ob_get_contents();
ob_clean();
return $content;
?>
