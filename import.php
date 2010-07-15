<?php
global $wpdb;
ob_start();
?><h2>Subscription Settings For Imported Subscribers</h2>
<form action="admin.php?<?php echo $_SERVER['QUERY_STRING']; ?>" method="post">
  <h3>Followup Subscription</h3>
  Automatically add the following subscription options to these subscribers.<br/>
    <?php
$nid = $_SESSION['importwizard_newsletter'];
$query = "select * from ".$wpdb->prefix."wpr_autoresponders where nid=$nid";
$results = $wpdb->get_results($query);
if (count($results) > 0)
          {
?>
  <label for="autoresponder">
  <input type="radio" id="autoresponder" name="followup" value="autoresponder" />
  Subscribe to the
  <select name="aid">
      <?php

foreach ($results as $autoresponder)
{
	?>
    <option value="<?php echo $autoresponder->id ?>"><?php echo $autoresponder->name ?></option>
    <?php
}
?>
  </select></label>
  autoresponder
      <?php

          }
          ?>
  <br/><?php
$query = "select * from ".$wpdb->prefix."wpr_blog_series";
$results = $wpdb->get_results($query);
$disabled = (count($results) == 0);
?>
<label for="postseries">
<input type="hidden" name="secondstep_wpnonce" value="<?php $nonce = wp_create_nonce('importwizard_wpnonce'); echo $nonce; ?>" />
    <input type="radio" name="followup" <?php echo ($disabled)?'disabled="disabled"':""; ?> value="postseries" id="postseries"/>
    Subscribe to the post series
    <select name="postseries" <?php echo ($disabled)?'disabled="disabled"':""; ?>>
    <?php
foreach ($results as $result)
{
?>
      <option value="<?php echo $result->id ?>"><?php echo $result->name ?></option>
      <?php
}
?>
    </select>
    post series. </label><br />

<label for="none"><input type="radio" checked="checked" name="followup" value="none" id="none" /> None</label

><h3>Blog subscription</h3>

<label for="allsub">
<input type="radio"  name="blogsubscription" value="all" id="allsub" /> Subscribe these subscribers to all posts on the blog.</label><br />
<label for="catsubs"><input type="radio" name="blogsubscription" value="cat" id="blogsubs" /> Subscribe these subscribers to the following blog categories. </label><br />

<?php
$listOfCategories = get_all_category_ids();
foreach ($listOfCategories as $category)
{
	?>
    <input type="checkbox" name="catlist[]" value="<?php echo $category ?>" /> <?php echo get_cat_name($category); ?><br />
    <?php
}
?>
<br />
<label for="nosub"><input type="radio" checked="checked" name="blogsubscription" value="none" id="nosub"/> No blog subscription</label><br />
<br />
<br />

<input type="reset" value="Reset" class="button"/><input type="submit" value="Next: Upload CSV &raquo;" class="button" /></form>
<?php
$cotnent = ob_get_contents();
return $content;
