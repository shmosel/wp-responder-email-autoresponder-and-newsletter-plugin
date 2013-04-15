<div class="wrap">
<div id="wpr-chrome" class="autoresponder-manage">

    <div id="breadcrumb">
        <ul>
            <li><a href="admin.php?page=_wpr/autoresponders"><?php _e("Autoresponders"); ?></a></li>
            <li><a href="admin.php?page=_wpr/autoresponders&action=manage&id=<?php echo $autoresponder->getId(); ?>"><?php _e(sprintf("Manage '%s'", $autoresponder->getName())); ?></a></li>
        </ul>
    </div>
    
    <h2>Manage Autoresponder '<?php echo $autoresponder->getName(); ?>'</h2>

    <form method="post" action="admin.php?page=_wpr/autoresponders&action=add_message&id=<?php echo $autoresponder->getId(); ?>">
        <input type="submit" value="Add Message" id="wpr-add-message" title="Add Message" class="wpr-action-button"/>
    </form>

    <div class="autoresponder-manage">
        <div class="row head">
            <div class="day-index column" valign="middle">Day #</div>
            <div class="message-title column">Title</div>
        </div>

        <?php
foreach ($messages as $message) {
 ?>
    <div class="row">
        <div class="day-index column" valign="middle">Day <?php echo $message->getDayNumber(); ?></div>
        <div class="message-title column"><a href="admin.php?page=_wpr/autoresponders&action=edit_message&id=<?php echo $message->getId(); ?>"><?php echo $message->getSubject(); ?></a></div>
        <div class="edit-link column"><a href="admin.php?page=_wpr/autoresponders&action=edit_message&id=<?php echo $message->getId(); ?>" class="wpr-action-button">Edit</a></div>
        <div class="delete-link column"><a href="admin.php?page=_wpr/autoresponders&action=delete_message&id=<?php echo $message->getId(); ?>" class="wpr-action-button delete-autoresponder-message">Delete</a> </div>
    </div>
    <?php
}
?>
    </div>
    <script>
        jQuery(document).ready(function() {
            jQuery('.wpr-chrome').css("height",document.availHeight);
        });
    </script>

    <?php include_once __DIR__."/templates/paging.php"; ?>


</div>
