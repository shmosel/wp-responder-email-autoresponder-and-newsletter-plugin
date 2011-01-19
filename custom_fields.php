<?php
include "custom_fields.lib.php";
function _wpr_newsletter_custom_fields_edit()
{
	global $wpdb;
	$id = $_GET['cid'];
	if (isset($_POST['name']))
	{
		$params['nid'] = $nid = $_GET['nid'];
		$params['id'] = $cid = $_POST['id'];
		$params['name'] = $name = $_POST['name'];
		$params['type'] = $type = $_POST['type'];
		$params['label'] = $label = $_POST['label'];
		$params['enum'] = $enum = $_POST['enum'];
		if ($name && $type)
		{
			if ($type == "enum")
			{
				if (count(explode(",",$enum)) <= 1)
				{
					$error = "Not enough options given for multiple choice field or invalid format";
				}
			}
			else
			{
				$enum='';
			}
			if (!$error)
			{
				$query = "UPDATE `".$wpdb->prefix."wpr_custom_fields` SET `type`='$type',`label`='$label',`enum`='$enum' where id='$cid';" ;
				 $wpdb->query($query);
				?>
<script>window.location='admin.php?page=wpresponder/newsletter.php&act=custom_field&nid=<?php echo $nid ?>';</script>
<?php  
				exit;
			}
		}
		else
		{
			$error = "The name and type fields are required";
		}
		$params = (object) $params;
	}
	
	if (!$params)
		$params = _wpr_newsletter_custom_fields_get($id);
	_wpr_newsletter_custom_field_form($params,$error,"Edit Custom Field","Save",true);					 
	
}
function _wpr_newsletter_custom_fields_create()
{
	global $wpdb;
	if (isset($_POST['name']))
	{

		$nid = $_GET['nid'];
		$name = $_POST['name'];
		$type = $_POST['type'];
		$label = $_POST['label'];
		$enum = $_POST['enum'];
		if ($name && $type && $label)
		{
			if ($type == "enum")
			{
				if (!count(explode(",",$enum)) > 1)
				{
					$error = "Not enough options given for multiple choice field or invalid format";
				}
			}
			else
			{
				$enum='';
			}
			 preg_match_all("@[^a-z0-9_]@",$name,$match);


			if (count($match[0]) > 0)
			{
				$error = "Only lowercase characters and underscore is allowed in name";
			}						   
								 
			if (!$error)
			{
				$query = "INSERT INTO `".$wpdb->prefix."wpr_custom_fields` (`nid`,`type`,`name`,`label`,`enum`) values ('$nid','$type','$name','$label','$enum');" ;
				$wpdb->query($query);
					
				//get the id of this field
				$query = "SELECT id from ".$wpdb->prefix."wpr_custom_fields where nid=$nid and name='$name'";
				$cf = $wpdb->get_results($query);
				$cid = $cf[0]->id;
				
				$query = "SELECT * FROM ".$wpdb->prefix."wpr_subscribers where nid=$nid";
				$subscribers = $wpdb->get_results($query);
				if (count($subscribers) > 0)
				{
					$qTemplate = " ( '$nid','$cid','%%sid%%','') ";
					$theQuery = "";
					foreach ($subscribers as $subscriber)
					{
						$theQuery[] = str_replace("%%sid%%",$subscriber->id,$qTemplate);
					}
					$theQuery = implode(", ",$theQuery);
					$theQuery = "INSERT INTO ".$wpdb->prefix."wpr_custom_fields_values (nid, cid, sid, value) VALUES ".$theQuery;
					$wpdb->query($theQuery);
				}
			
?>
	<script>window.location='admin.php?page=wpresponder/newsletter.php&act=custom_field&nid=<?php echo $nid ?>';</script>
<?php  
			exit;
			}
			$parameters->name = $name;
			$parameters->label = $label;
			$parameters->type = $type;
			$parameters->enum = $enum;
		}
		else
		{
			$error = "The name, label and type fields are required fields";
		}
	}
	
	_wpr_newsletter_custom_field_form($parameters,$error,"Create Custom Field","Create");					 
	
}


function _wpr_newsletter_custom_fields_delete()
{
	global $wpdb;
	$cid = $_GET['cid'];
	$nid = $_GET['nid'];
	if ($_GET['confirm'] == 'true')
	{

		$query = "DELETE FROM ".$wpdb->prefix."wpr_custom_fields WHERE id='$cid'";
		$wpdb->query($query);
		
		
		?>
	<script>window.location='admin.php?page=wpresponder/newsletter.php&act=custom_field&nid=<?php echo $nid ?>';</script>
	<?php
	exit;
		
    }
   $field = _wpr_newsletter_custom_fields_get($cid);
   ?>
<div class="wrap">
  <h1>Delete Custom Field </h1></div>
  This will also delete:
  <ol>
    <li>The data for the field for all subscribers</li>
    <li>The form fields in all subscription forms that are connected to this field</li>
  </ol>
  Are you sure you want to delete '<?php echo $field->name ?>' field?<br /><br />

  <a href="<?php echo $_SERVER['REQUEST_URI'] ?>&confirm=true" class="button"> Delete </a> &nbsp;&nbsp;&nbsp;<a href="javascript:window.history.go(-1);" class="button">Cancel</a><br />

<?php
}
function _wpr_newsletter_custom_fields()
{
	$cfact = $_GET['cfact'];
	
	switch ($cfact)
	{
		case 'create':
		  _wpr_newsletter_custom_fields_create();
		break;
		case 'edit':
			_wpr_newsletter_custom_fields_edit();
		break;
		case 'delete':
			_wpr_newsletter_custom_fields_delete();
		break;
		default:
		  _wpr_newsletter_custom_fields_list();
		  break;
	}
	
}

function _wpr_newsletter_custom_fields_list()
{
	global $wpdb;
	$id = $_GET['nid'];
	$newsletter = _wpr_newsletter_get($id);
	$query = "SELECT * FROM ".$wpdb->prefix."wpr_custom_fields where nid=$id";
	$result = $wpdb->get_results($query);
	
	?>
<script>
</script>
<h2>Custom Fields For '<?php echo $newsletter->name ?>' Newsletter</h2>
<table class="widefat">
  <tr>
  <thead>
  <th scope="col">Name</th>
    <th width="100" scope="col">Type</th>
    <th scope="col">Label</th>
    <th scope="col">Actions</th>
    </thead>
  </tr>
  <?php
		  foreach ($result as $field)
		  {
			  ?>
  <tr>
    <td><?php echo $field->name ?></td>
    <td><?php echo _wpr_custom_field_name($field->type,$field->enum); ?></td>
    <td><?php echo $field->label; ?></td>
    <td><input type="button" value="Edit" onclick="window.location='admin.php?page=wpresponder/newsletter.php&act=custom_field&nid=<?php echo $newsletter->id ?>&cfact=edit&cid=<?php echo $field->id ?>'" class="button-primary" />
      <input type="button" value="Delete" onclick="window.location='admin.php?page=wpresponder/newsletter.php&act=custom_field&nid=<?php echo $newsletter->id ?>&cfact=delete&cid=<?php echo $field->id ?>';" class="button-primary" /></td>
  </tr>
  <?php
		  }
		  ?>
</table>
<input type="button" value="Add New Field" class="button" onclick="window.location='admin.php?page=wpresponder/newsletter.php&act=custom_field&nid=<?php echo $newsletter->id ?>&cfact=create';" /> <input type="button" value="&laquo; Back To Newsletters" style="float:left" onclick="window.location='admin.php?page=wpresponder/newsletter.php&act=list';" class="button" />
<?php
}


function _wpr_newsletter_custom_field_form($parameters,$error,$title="Create Custom Field",$buttontext="Create",$nameIsHidden=false)
{
	$parameters = (object) $parameters;
?>
<div align="center" style="color: red"><strong><?php echo $error ?></strong></div>
<div class="wrap">
  <h2><?php echo $title ?></h2>
</div>
<form name="custom_field_form" action="<?php print $_SERVER['REQUEST_URI'] ?>"	 method="post">
  <table>
    <tr>
      <td><strong>Name:</strong><br>
      <small>Should NOT contain spaces or special characters</small></td>
      <td><?php if (!$nameIsHidden) { ?>
        <input type="text" name="name" value="<?php echo $parameters->name ?>" />
        <?php } else { ?>
        <input type="hidden" name="name" value="<?php echo $parameters->name ?>" />
        <?php echo $parameters->name ?>
        <?php } ?></td>
    </tr>
    <tr>
      <td><strong>Label:</strong><br/>
      <small>The label that will be used in the subscription form for this field.</strong></td>
      <td><input type="text" name="label" value="<?php echo $parameters->label ?>" /></td>
    </tr>
    <tr>
      <td><strong>Type:</strong><br/>
<small>      Choose whether the user has to enter the value, or choose<br/> a value from a set of values in a drop down</small></td>
      <td><select name="type">
          <option value="text" <?php if ($parameters->type == "text") { echo "selected=\"selected\""; } ?>>One Line Text</option>
          <option value="enum" <?php if ($parameters->type == "enum") { echo "selected=\"selected\""; } ?>>Multiple Choice</option>
        </select></td>
    </tr>
    <tr>
    <td></td>
    </tr>
    <tr>
      <td><br/><br/><strong>The choices (if multiple choice):</strong><br />
      <small>If you chose multiple choice for type, then enter<br/> the choices the user can choose separated by<br/> commas. No spaces.
        <small>Comma separated. No spaces.<br />
        For example: male,female</small></td>
      <td><input type="hidden" name="id" value="<?php echo $parameters->id ?>" />
        <input type="text" id="enum" name="enum" value="<?php echo $parameters->enum ?>" /></td>
    </tr>
  </table>
  <input type="submit" value="<?php echo $buttontext ?>" class="button" />
</form>
<?php
}

function _wpr_custom_field_name($name,$options)
{
	switch ($name)
	{
		case 'text':
		  return 'One Line Text';
		  break;
		case 'enum':
		  return 'Multiple Choice'." ($options)";
		  break;
	}
	
}


function wpr_customfields()
{
	global $wpdb;
      ?>
      <h2>Custom Fields</h2>
      <?php
      $query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters";
      $newsletterList = $wpdb->get_results($query);
      ?>
      <span style="">
      Select the newsletter from the list below to manage its custom fields.
      </span>
      <?php
    
      if (count($newsletterList) >0)
      {
      ?>
       <table width="50%">
      <?php
      foreach ($newsletterList as $newsletter)
      {
 	      ?>
 	      <tr>
 	      <td style="height: 30px;">
 	     <?php echo $newsletter->name ?> </td><td><a class="button" href="admin.php?page=wpresponder/newsletter.php&act=custom_field&nid=<?php echo $newsletter->id ?>">Manage Custom Fields</a></td></tr>
 	      <?php
      }
      ?></table>
      <?php
      }
      else
      {
      ?>
	<p style="padding: 20px; display:block; text-align:center; width: 600px; background-color: #fefefe;border: 1px solid #000;">There are no newsletter to which custom fields can be associated.<a href="http://localhost/blog/wp-admin/admin.php?page=wpresponder/newsletter.php&act=add">Create a newsletter</a> to add custom fields.</p>
	<?php
      }
           

}

