<?php

include "forms.lib.php";

function wpr_subscriptionforms()

{

	if (_wpr_no_newsletters("To create subscription forms"))

		return;

	switch ($_GET['action'])

	{

		case 'create':

		_wpr_subscriptionforms_create();

		break;

		case 'form':

		$id = $_GET['fid'];

		$form = _wpr_subscriptionform_get($id);

		_wpr_subscriptionform_getcode($form,"'".$form->name."' Form HTML Code");

		return;

		break;

		case 'edit':

		$id = $_GET['fid'];

		$form = _wpr_subscriptionform_get($id);

		if (isset($_POST['fid']))

		{

			$checkList = array("name"=>"Name field is required","confirm_subject"=>"E-Mail Confirmation Subject Field is required","confirm_body"=>"E-Mail Confirmation Body field","confirmed_subject"=>"Confirmed Subscription subject field is required","confirmed_body"=>"Confirmed subscription body field is required");

			$errors = array();

			foreach ($checkList as $field=>$errorMessage)

			{
				$theValue = $_POST[$field];
				$theValue = trim($theValue);
				if (empty($theValue))
				{

					$errors[] = $checkList[$field];

				}

			}			

			if (count($errors) == 0)

			{		

				$info['id'] = $_POST['fid'];

				$info['name'] = $_POST['name'];

				$info['return_url'] = $_POST['return_url'];

				$info['followup_type'] = $_POST['followup'];

				$info['followup_id'] = ($_POST['followup'] == "autoresponder")?$_POST['autoresponder_id']:$_POST['post_series'];

				$info['blogsubscription_type'] = $_POST['blog'];

				$info['blogsubscription_id'] = $_POST['blog_cat'];

				$info['custom_fields'] = (isset($_POST['custom_fields']) && is_array($_POST['custom_fields']))?implode(",",$_POST['custom_fields']):"";

				$info['confirm_subject'] = $_POST['confirm_subject'];

				$info['confirm_body'] = $_POST['confirm_body'];

				$info['nid'] = $_POST['newsletter'];

				$info['confirmed_subject'] = $_POST['confirmed_subject'];

				$info['confirmed_body'] = $_POST['confirmed_body'];

				_wpr_subscriptionform_update($info);

				$form = _wpr_subscriptionform_get($info['id']);

				_wpr_subscriptionform_getcode($form,"Form Saved");

				return;

			}

			else 

			$form = (object) $_POST;

		}		

		

		_wpr_subscriptionform_form($form,$errors);		

		break;

		default:

		_wpr_subscriptionforms_list();

	}

}



function _wpr_subscriptionforms_list()

{

	global $wpdb;

	$query = "SELECT * FROM ".$wpdb->prefix."wpr_subscription_form";



	$forms = $wpdb->get_results($query);

	?>

<div class="wrap">
  <h2>Subscription Forms</h2>
</div>
Use the subscription forms below to gather subscribers for your newsletter.
<table class="widefat">
  <tr>
  <thead>
  <th scope="col">Name</th>
    <th scope="col">Actions</th>
    </thead>
  </tr>
  <?php

	foreach ($forms as $form)

	{

		?>
  <tr>
    <td><?php echo $form->name ?></td>
    <td><a href="admin.php?page=wp-responder-email-autoresponder-and-newsletter-plugin/subscriptionforms.php&action=edit&fid=<?php echo $form->id ?>" class="button">Edit</a>&nbsp;<a href="admin.php?page=wp-responder-email-autoresponder-and-newsletter-plugin/subscriptionforms.php&action=form&fid=<?php echo $form->id ?>" class="button">Get Form HTML</a></td>
  </tr>
  <?php

	}

?>
</table>
<a href="admin.php?page=wp-responder-email-autoresponder-and-newsletter-plugin/subscriptionforms.php&action=create" class="button">Create New Form</a>
<?php

}



function _wpr_subscriptionform_getcode($form,$title)

{

		?>
<div class="wrap">
  <h2><?php echo $title ?></h2>
</div>
The form has been saved. Copy and paste the code in the box below on the page where you want the subscription form to appear.
<h3>Form Code:</h3>
<?php $code = _wpr_subscriptionform_code($form); ?>
<textarea rows="20" cols="70" id="wpr_code"><?php echo $code ?></textarea>
<br />
<div style="display:none" id="preview"> <?php echo $code ?> </div>
<script>

var preview;

function preview()

{

	preview = window.open('about:blank','previewWindow','top=20,left=20,width=300,height=500');

	preview.document.write(document.getElementById('preview').innerHTML);

}

</script>
<a href="admin.php?page=wp-responder-email-autoresponder-and-newsletter-plugin/subscriptionforms.php" class="button">&laquo; Back To Forms</a>&nbsp;
<input type="button" value="Select All" onclick="document.getElementById('wpr_code').select();" class="button"/>
<input type="button" onclick="preview();" value="Preview" class="button" />
<?php

	

}



function _wpr_subscriptionform_code($form)

{

			

		$url = get_bloginfo('siteurl');			

		$pathto = PLUGINDIR."/wp-responder-email-autoresponder-and-newsletter-plugin/optin.php";			

					

		ob_start();

		?>
<form action="<?php echo $url?>/<?php echo $pathto ?>" method="post">
  <input type="hidden" name="blogsubscription" value="<?php echo $form->blogsubscription_type ?>" />
  <?php if ($form->blogsubscription_type == "cat") { ?>
  <input type="hidden" name="cat" value="<?php echo $form->blogsubscription_id ?>" />
  <?php

} 

if (!empty($form->followup_type) && $form->followup_type != "none")

{ 

?>
  <input type="hidden" name="followup" value="<?php echo $form->followup_type ?>" />
  <input type="hidden" name="responder" value="<?php echo $form->followup_id ?>" />
  <?php

} ?>
  <input type="hidden" name="newsletter" value="<?php echo $form->nid ?>" />
  <?php if (isset($form->id)) { ?>
    <input type="hidden" name="fid" value="<?php echo $form->id ?>" />
    <?php } ?>
  <table>
    <tr>
      <td>Name:</td>
      <td><input type="text" name="name" /></td>
    </tr>
    <tr>
      <td>E-Mail Address:</td>
      <td><input type="text" name="email" />
    </tr>
    <?php



	if (!empty($form->custom_fields))

	{

		$formItems = array();

		$formItems = explode(",",$form->custom_fields);

		foreach ($formItems as $field)

		{

			$theField = _wpr_newsletter_custom_fields_get($field);

			

			switch ($theField->type)

			{

				case 'enum':

				   $choices = explode(",",$theField->enum);

				   ?>
    <tr>
      <td><?php echo $theField->label ?></td>
      <td><select name="cus_<?php echo base64_encode($theField->name) ?>">
          <?php

				   foreach ($choices as $choice)

				   {

					   ?>
          <option><?php echo $choice ?></option>
          <?php

				   }

				   ?>
        </select></td>
    </tr>
    <?php

				 break;

				case 'text':

				?>
    <tr>
      <td><?php echo $theField->label ?></td>
      <td><input type="text" name="cus_<?php echo base64_encode($theField->name) ?>" />
    </tr>
    <?php

				

				break;

				case 'hidden':

				?>
    <input type="hidden" name="cus_<?php echo base64_encode($theField->name); ?>" value="<?php echo $_POST['field_'.$theField->id."_value"] ?>" />
    <?php

				break;

			}

			

		}

	}

	?>
    <tr>
      <td colspan="2" align="center"><input type="submit" value="Subscribe" /></td>
    </tr>
  </table>
</form>
<?php

    $form = ob_get_clean();

	return $form;

}



function _wpr_subscriptionforms_create()

{

	global $wpdb;

	$fieldsToSelect = array(); //just initializing the custom fields to be selected when the form loads..	

	if (isset($_POST['newsletter']))

	{

		

		$checkList = array("name"=>"Name field is required","confirm_subject"=>"E-Mail Confirmation Subject Field is required","confirm_body"=>"E-Mail Confirmation Body field","confirmed_subject"=>"Confirmed Subscription subject field is required","confirmed_body"=>"Confirmed subscription body field is required");

		$errors = array();

		foreach ($checkList as $field=>$errorMessage)

		{


			$theValue = trim($_POST[$field]);
			
			if (empty($theValue))
			{
				$errors[] = $checkList[$field];
			}

			

		}

		$info['name'] = $_POST['name'];

			$info['return_url'] = $_POST['return_url'];

			$info['followup_type'] = $_POST['followup'];

			$info['followup_id'] = ($_POST['followup'] == "autoresponder")?$_POST['autoresponder_id']:$_POST['post_series'];

			$info['blogsubscription_type'] = $_POST['blog'];

			$info['blogsubscription_id'] = $_POST['blog_cat'];

			$info['custom_fields'] = (is_array($_POST['custom_fields']))?implode(",",$_POST['custom_fields']):"";

			$info['confirm_subject'] = $_POST['confirm_subject'];

			$info['confirm_body'] = $_POST['confirm_body'];

			$info['nid'] = $_POST['newsletter'];

			$info['confirmed_subject'] = $_POST['confirmed_subject'];

			$info['confirmed_body'] = $_POST['confirmed_body'];

		if (count($errors) == 0)

		{

			_wpr_subscriptionform_create($info);

			$query = "SELECT * FROM ".$wpdb->prefix."wpr_subscription_form where name='".$info['name']."';";

			$form = $wpdb->get_results($query);

			$form = $form[0];

		     _wpr_subscriptionform_getcode($form,"Form Created");

			return;

		}

		$params = (object) $info;	

	}

	

	_wpr_subscriptionform_form($params,$errors);

}



function _wpr_subscriptionform_form($parameters=array(),$errors=array())

{

	$parameters = (object)$parameters;

        

        if (!empty($parameters->custom_fields))

            $fieldsToSelect = explode(",",$parameters->custom_fields);

	global $wpdb;



	?>
<div class="wrap">
  <h2>Create Subscription Form</h2>
</div>
<script>

function Field(id,name,type,label,choices)

{

	this.name = name;

	this.id = id;

	this.type = type;

	this.label = label;

	this.choices = choices;

}

var Fields = new Array();

<?php

$query ="SELECT * FROM ".$wpdb->prefix."wpr_custom_fields";

$customfields = $wpdb->get_results($query);

$count=0;



foreach ($customfields as $field)

{

	$newsletterlist[] = $field->nid;

}

$newsletterlist = array_unique($newsletterlist);

?>

var NewsletterFields = Array();

<?php foreach ($newsletterlist as $newsletter) { ?>

NewsletterFields['<?php echo $newsletter; ?>'] = new Array();

<?php 

} 





foreach ($customfields as $field)

{

	?>	

NewsletterFields['<?php echo $field->nid ?>'].push(new Field('<?php echo $field->id ?>','<?php echo addslashes($field->name) ?>','<?php echo addslashes($field->type); ?>','<?php echo addslashes($field->label); ?>','<?php echo addslashes($field->enum) ?>'));<?php

}

?>

var customFieldList = new Array();



function showFields(elements)

{

	var fieldsCode;

	if (elements && elements.length > 0)

		document.getElementById('customfields').innerHTML = '';			

	else

		return;

	for (element in elements)

	{

		field = elements[element];

		var element = document.createElement("div");

		customFieldList.push(element);

		element.setAttribute("style","border: 1px solid #ccc; padding: 10px;");



		var formelement;

		    var check = document.createElement("input");

			check.setAttribute("type","checkbox");

			check.setAttribute("name","custom_fields[]");

			check.setAttribute("value",field.id);

			check.setAttribute("id","custom_"+field.id);

			element.appendChild(check);

			element.innerHTML += " "+field.name+"<br />";

			preview = document.createElement("div");

			preview.innerHTML += field.label +":";		

			preview.setAttribute("style","background-color: #ddd; border: 1px solid #eee; padding: 10px;");

			if (field.type == "text")

			{

				element.innerHTML += "Type: One Line Text <br /><strong>Preview: <br />";

				formelement = document.createElement("input");

				formelement.setAttribute("type","text");

			}

			else

			{

				formelement = document.createElement("select");

				

				var choices = field.choices.split(",");

				element.innerHTML += "Type: Multiple Choice<br /><strong>Preview: <br />";

				for (option in choices)

				{

					optionElement = document.createElement("option");

					optionElement.text = choices[option];

					formelement.add(optionElement,null);

				}

			}

			preview.appendChild(formelement);

			element.appendChild(preview);			

			element.innerHTML += "<br>";



		document.getElementById('customfields').appendChild(element);			

	}



}



function load(id)

{

	document.getElementById('customfields').innerHTML="<div align=\"center\">--None--</div>\"";

	showFields(NewsletterFields[id]);

}

var toSelect = new Array(); //custom field ids to select.

<?php

print_r($fieldsToSelect);

if (count($fieldsToSelect) > 0)

{

	?>	<?php

	foreach ($fieldsToSelect as $num=>$field)

	{

?>

toSelect[<?php echo $num; ?>] = <?php echo $field; ?>;



<?php

	}

	

}

?>jQuery(document).ready(function() {

    

	var selectedNewsletter = document.getElementById('newsletterlist').options[document.getElementById('newsletterlist').selectedIndex].value;

	showFields(NewsletterFields[selectedNewsletter]);

	//if this form is being used to edit, then select the fields that were saved..

	for (var i in toSelect)

	{

		document.getElementById('custom_'+toSelect[i]).checked=true;

	}

	

	

});

</script>
<?php if (count($errors) >0)

{

	?>
<div class="updated fade">
  <ul>
    <?php 

	foreach ($errors as $error)

	{

		echo '<li>'.$error.'</li>';

	}

	?>
  </ul>
</div>
<?php

}

?>
<div style="display:none">
  <?php 

$query = "SELECT id from ".$prefix."wpr_newsletters";

$newsletters  = $wpdb->get_results($query);

foreach ($newsletters as $newsletter)

{

	$nid = $newsletter->id;

	?>
  <div id="fields-<?php echo $nid?>">
    <?php 

   $query = "SELECT * FROM ".$prefix."wpr_custom_fields where nid=$nid";

   $customFields = $wpdb->get_results($query);

   foreach ($customFields as $field)

   {

?>
    <div class="field"> Name Of Field: <?php echo $field->name ?><br />
      Field Label: <?php echo $field->label ?><br />
      <?php



	   switch ($field->type)

	   {

		   case 'text':

?>
      Type: One Line Text
      
      Preview:
      <input type="text" size="30" />
      <?php

		   break;

		   case 'enum':

		   $choices = $field->enum;

		   $choices = explode(",",$choices);	   

?>
      Type: Multiple Choice<br />
      Preview:
      <select>
        <?php

 foreach ($choices as $choice)

 {

	 ?>
        <option><?php echo $choice ?></option>
        <?php

 }

 ?>
      </select>
      <?php

		   break;

		   case 'hidden':

		   ?>
      Type: Hidden<br />
      Preview: Hidden fields aren't visible on the page.
      <?php

		   break;

	   }

  ?>
    </div>
    <?php

   }

   ?>
  </div>
  <?php

}

?>
</div>
<form action="<?php print $_SERVER['REQUEST_URI'] ?>" method="post">
  <input type="hidden" value="<?php echo $parameters->id  ?>"  name="fid"/>
  <table width="700">
    <tr>
      <td><strong>Name:</strong>
        <p><small>This form's settings will be saved. This name will be used to identify the settings.</small></p></td>
      <td><input type="text" name="name" size="60" value="<?php echo $parameters->name ?>" /></td>
    </tr>
    <tr>
      <td><strong>Newsletter:</strong>
        <p><small>Select the newsletter to which subscribers will be subscribed when filling this form.</small></p></td>
      <td><select name="newsletter" id="newsletterlist" onchange="load(this.options[this.selectedIndex].value);">
          <?php

		  $query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters";

		  $newsletters = $wpdb->get_results($query);

		  foreach ($newsletters as $newsletter)

		  {

			  ?>
          <option value="<?php echo $newsletter->id; ?>" <?php 

			  if ($parameters->nid == $newsletter->id) 

			  {

				  echo 'selected="selected"';

			  } ?>><?php echo $newsletter->name; ?></option>
          <?php

		  }

		  ?>
        </select>
    </tr>
    <tr>
      <td width="300"><strong>Return URL:</strong>
        <p><small> The subscriber is sent to this url after entering their name and email address in the subscription form. </small></p></td>
      <td><input type="text" name="return_url" size="60" value="<?php echo $parameters->return_url ?>" /></td>
    </tr>
    <tr>
      <td colspan="2">Other Fields:</td>
    </tr>
    <tr>
      <td colspan="2"><fieldset style="border: 1px solid #888; padding:20px;">
          <legend> After subscribing, follow up with:</legend>
          <?php

		 $query = "SELECT * FROM ".$wpdb->prefix."wpr_autoresponders";

		 $autoresponders = $wpdb->get_results($query);

		 

		 ?>
          <input type="radio" name="followup" id="autoresponder" <?php if (count($autoresponders) ==0 ) { echo 'disabled="disabled"';} ?> value="autoresponder" <?php if ($parameters->followup_type == "autoresponder") { echo "checked=\"checked\""; } ?> />
          <label for="autoresponder"> Follow up with the
            <select  <?php if (count($autoresponders) ==0 ) { echo 'disabled="disabled"';} ?>  name="autoresponder_id">
              <?php 

		 

		 foreach ($autoresponders as $autoresponder)

		 {

			 ?>
              <option value="<?php echo $autoresponder->id ?>" <?php if ($parameters->followup_type == "autoresponder" && $parameters->followup_id == $autoresponder->id) { echo 'selected="selected"'; } ?>><?php echo $autoresponder->name ?></option>
              <?php

		 }

		 ?>
            </select>
            autoresponder series. <a href="admin.php?page=wp-responder-email-autoresponder-and-newsletter-plugin/autoresponder.php&action=create" style="float:right">Create Autoresponder</a></label>
          <br />
          <?php 

		 $query = "SELECT * FROM ".$wpdb->prefix."wpr_blog_series";

		 $blogseries = $wpdb->get_results($query);

		 ?>
          <input type="radio" <?php if (count($blogseries) ==0 ) { echo 'disabled="disabled"'; } ?> name="followup" id="blogseries" value="postseries" <?php if ($parameters->followup_type == "postseries") { echo 'checked="checked"'; } ?> />
          <label for="blogseries"> Follow up with the
            <select <?php if (count($blogseries) ==0 ) { echo 'disabled="disabled"';} ?> name="post_series">
              <?php			  

		 foreach ($blogseries as $bs)

		 {

			 ?>
              <option value="<?php echo $bs->id ?>" <?php if ($parameters->followup_type == "postseries" && $parameters->followup_id == $bs->id) echo 'selected="selected"'; ?>><?php echo $bs->name ?></option>
              <?php

		 }

		 ?>
            </select>
            post series.</label>
          <a href="admin.php?page=wp-responder-email-autoresponder-and-newsletter-plugin/blogseries.php&action=create" style="float:right">Create Post Series</a> <br />
          <input type="radio" name="followup" value="none" id="nonea" <?php 

		  if ($parameters->followup_type == 'none' || empty($parameters->followup_type)) 

		  {

			  echo 'checked="checked"';

		   }  ?> />
          <label for="nonea">None</label>
        </fieldset></td>
    </tr>
    <tr>
      <td colspan="2"><fieldset style="border: 1px solid #888; padding:20px;">
          <legend>Subscription to blog content:</legend>
          <input type="radio" name="blog" <?php 

		  if ($parameters->blogsubscription_type == "all")

		  {

			  echo 'checked="checked"';

		  } ?> id="all" value="all" />
          <label for="all"> Automatically send all blog posts as I publish them</label>
          <br />
          <input type="radio" <?php 

		  if ($parameters->blogsubscription_type == "cat")

		  {

			  echo 'checked="checked"';

		  } ?> name="blog" id="cat" value="cat" />
         
          <label for="cat"> Automatically send posts only under the
            <select name="blog_cat">
         <?php $args = array(
                                            'type'                     => 'post',
                                            'child_of'                 => 0,
                                            'orderby'                  => 'name',
                                            'order'                    => 'ASC',
                                            'hide_empty'               => false,
                                            'hierarchical'             => 0);
         
		 $categories = get_categories($args);

		 foreach ($categories as $category)

		 {

			 ?>
              <option value="<?php echo $category->term_id ?>" <?php 

			  if ($parameters->blogsubscription_type=="cat" && $parameters->blogsubscription_id == $category->term_id)
			  {

				  echo 'selected="selected"';

			  } ?>><?php echo $category->cat_name ?></option>
              <?php

		 }

		 ?>
            </select>
            category. </label>
          <a href="categories.php" style="float:right">Create Categories</a><br />
          <input type="radio" <?php 

		  if ($parameters->blogsubscription_type == "none" || empty($parameters->blogsubscription_type))

		  {

			  echo 'checked="checked"';

		  } ?> name="blog" value="none" id="noneb" />
          <label for="noneb">None</label>
        </fieldset></td>
    </tr>
    <tr>
      <td colspan="2"><div class="wrap">
          <h3>More Form Fields</h3>
          <hr size="1" color="black">
          <p>Select the custom fields that should be added to the in the opt-in form.</p>
        </div>
        <div id="customfields"> </div></td>
    </tr>
    <tr>
      <td><h3> Confirmation E-Mail:</h3>
        <table>
          <tr>
            <td>Subject:</td>
            <td><input type="text" name="confirm_subject" size="70" value="<?php



   if (!$parameters->confirm_subject) 

   {

		$confirm_subject = get_option('wpr_confirm_subject');

		echo $confirm_subject;

   }

   else

   {

	      echo $parameters->confirm_subject;

   }

   ?>" /></td>
          </tr>
          <tr>
            <td colspan="2"> Message Body:<br />
              <textarea name="confirm_body" rows="10" cols="60" wrap="hard">

<?php 

if (!$parameters->confirm_body) 

{

	$confirm_email = get_option('wpr_confirm_body');

	echo $confirm_email;

}

else

{

	echo $parameters->confirm_body;

}

	?>

</textarea></td>
          </tr>
        </table>
        <h3>Subscription Confirmed E-Mail:</h3>
        <table>
          <tr>
            <td>Subject:</td>
            <td><input type="text" name="confirmed_subject" value="<?php echo ($parameters->confirmed_subject)?$parameters->confirmed_subject:get_option("wpr_confirmed_subject"); ?>" size="60" /></td>
          </tr>
          <tr>
            <td colspan="2"> Message Body:<br />
              <textarea name="confirmed_body" rows="10" cols="60">

<?php echo ($parameters->confirmed_body)?$parameters->confirmed_body:get_option("wpr_confirmed_body"); ?>

</textarea></td>
          </tr>
        </table></td>
    </tr>
    <tr>
      <td colspan="2"><input class="button" type="submit" value="Create Form And Get Code" />
        &nbsp;<a class="button" href="admin.php?page=wp-responder-email-autoresponder-and-newsletter-plugin/subscriptionforms.php">Cancel</a></td>
    </tr>
  </table>
</form>
<?php



}

