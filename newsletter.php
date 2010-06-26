<?php

include "newsletter.lib.php";

include "custom_fields.php";





function _wpr_newsletter_edit()

{

	

	$id = $_GET['nid'];

	$newsletter = _wpr_newsletter_get($id);	

	if ($_POST['name'] && $_POST['reply_to'])

	{

		

		if ($_POST['name'])

		{

			$info['id'] = $_POST['id'];

			$info['name'] = $_POST['name'];

			$info['reply_to'] = $_POST['reply_to'];

			$info['description'] = $_POST['description'];

			$info['confirm_subject'] = $_POST['confirm_subject'];

			$info['confirm_body'] = $_POST['confirm_body'];

			$info['confirmed_subject'] = $_POST['confirmed_subject'];

			$info['confirmed_body'] = $_POST['confirmed_body'];

                        $info['fromname'] = $_POST['fromname'];

                        $info['fromemail'] = $_POST['fromemail'];

			_wpr_newsletter_update($info);

			?>

            <script>window.location='admin.php?page=wp-responder-email-autoresponder-and-newsletter-plugin/newsletter.php'</script>

			<?php

            exit;

		}

		else
		{

			$error = "Name field is required";

			foreach ($_POST as $name=>$value)

			{

				$information->{$name} = $value;

			}

		}

		

	}

	_wpr_newsletter_form($newsletter,"Edit Newsletter","Update",$error);

	

}


function checkIfValidNewsletterName($name)
{
	$name = trim($name);
	if ( empty($name) )
		return false;
	global $wpdb;
	
	$query = "SELECT COUNT(*) num FROM ".$wpdb->prefix."wpr_newsletters where name='$name'";
	$results = $wpdb->get_results($query);
	return ($results[0]->num ==0); //if the number of newsletters with this name is zero then the name is unique
	
}


function _wpr_newsletter_add()

{

	if ($_POST['name'] && $_POST['reply_to'])
	{
		if (checkIfValidNewsletterName($_POST['name']) )
		{
			$info['name'] = $_POST['name'];
			$info['reply_to'] = $_POST['reply_to'];
			$info['description'] = $_POST['description'];
			$info['confirm_subject'] = $_POST['confirm_subject'];
			$info['confirm_body'] = $_POST['confirm_body'];
			$info['confirmed_subject'] = $_POST['confirmed_subject'];
			$info['confirmed_body'] = $_POST['confirmed_body'];
            $info['fromname'] = $_POST['fromname'];
            $info['fromemail'] = $_POST['fromemail'];
			
			_wpr_newsletter_create($info);
			
			?>

			<script>window.location='admin.php?page=wp-responder-email-autoresponder-and-newsletter-plugin/newsletter.php'</script>
			<?php

            exit;

		}

		else

		{

			if (empty($_POST['name']))			
			{
				$error = "Name field is required";
				
			}
			else 
			{
				$error = "The name field is not unique.";
			}

			$information = (object) $_POST;
		}

		

	}

	_wpr_newsletter_form($information,"Create Newsletter","Create Newsletter",$error);

	

}



function _wpr_newsletter_home()

{

	?>

    <div class="wrap"><h2>Newsletters</h2></div>

    <?php _wpr_newsletter_list(); ?>

    <input type="button" onclick="window.location='<?php echo $_SERVER['REQUEST_URI']; ?>&act=add'" value="Create Newsletter" class="button" />    

    <?php

}



function _wpr_newsletter_list()

{

	global $wpdb;

	$query = "SELECT * FROM ".$wpdb->prefix."wpr_newsletters";

	$results = $wpdb->get_results($query);

	?>

    <style>

	form {

		display:inline;

	}

	</style>

    <table class="widefat">

    <tr>

    <thead>

      <th scope="col">Name</th>

      <th scope="col">Reply-To</th>

      <th scope="col" width="600">Actions</th>

     </thead>

     </tr>

     <?php foreach ($results as $list) { ?>

     <tr>

       <td><?php echo $list->name ?></td>

       <td><?php echo $list->reply_to ?></td>

       <td>

       <input type="button" name="Edit" onclick="window.location='admin.php?page=wp-responder-email-autoresponder-and-newsletter-plugin/newsletter.php&act=edit&nid=<?php echo $list->id ?>';" value="Edit" class="button" />

       <input type="button" name="Delete" value="Delete" class="button" />

       <input type="button" name="Delete" value="Manage Leads" class="button" onclick="window.location='admin.php?page=wp-responder-email-autoresponder-and-newsletter-plugin/subscribers.php&action=nmanage&nid=<?php echo $list->id ?>';" />

       <input type="button" name="E-mails" value="Custom Fields" onclick="window.location='admin.php?page=wp-responder-email-autoresponder-and-newsletter-plugin/newsletter.php&act=custom_field&nid=<?php echo $list->id ?>';" class="button"/>

       </td>

       </tr>

       <?php

	 }	

?></table>

<?php

}



function wpr_newsletter()

{

	$action = $_GET['act'];

	

	switch ($action)

	{

		case 'add':

			_wpr_newsletter_add();

			break;

		case 'edit':

		    _wpr_newsletter_edit();

			break;

		case 'delete':

		   _wpr_newsletter_delete();

		case 'custom_field':

		   _wpr_newsletter_custom_fields();

		   break;

		case 'forms':

		   _wpr_newsletter_forms();

		 default:

		 _wpr_newsletter_home();

	}		

		

}


