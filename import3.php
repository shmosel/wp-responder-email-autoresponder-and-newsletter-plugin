<?php
ob_start();
?><h3>All Done!</h3>
The subscribers have been imported from your CSV file to the database. You may now start sending them emails.<br />
<br />

<a href="admin.phppage=wp-responder-email-autoresponder-and-newsletter-plugin/importexport.php" class="button-primary" style="margin:10px;"> Done &raquo; </a>
<?php
$content = ob_get_clean();
return $content;
?>