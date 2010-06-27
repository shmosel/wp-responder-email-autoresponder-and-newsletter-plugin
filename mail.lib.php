<?php

include "htmltemplates.lib.php";

function wpr_mail_form($parameters=array(),$mode="new",$error)
{

	global $wpdb;

	?>
<style>
.wrap label {
	font-family: Arial;
	font-size: 15px;
	font-weight: bold;
}
</style>
<script type="text/javascript">

<?php require "customize_recipients.php"; ?>

</script>
<script>

function Field(id,name,label)

{

	this.id = id;

	this.name = name;

	this.label= label;

}



var ListOfFields = new Array();

<?php

$query = "select id,name from ".$wpdb->prefix."wpr_newsletters";

$newsletters = $wpdb->get_results($query);

foreach ($newsletters as $newsletter)

{

	$query = "select * from ".$wpdb->prefix."wpr_custom_fields where nid=".$newsletter->id;

	$fields = $wpdb->get_results($query);

?>

ListOfFields['<?php echo $newsletter->id ?>'] = new Array();

<?php

	foreach ($fields as $field)

	{

		?>

ListOfFields['<?php echo $newsletter->id ?>'].push(new Field("<?php echo $field->id ?>","<?php echo $field->name ?>","<?php echo $field->label ?>"));

<?php
	}	
}

?>function loadCustomFields(id)

{

	var fieldList = ListOfFields[id];

	var container = document.getElementById('custom_fields');

	container.innerHTML=''

	var listItem  = document.createElement("ol");

	var item1 = document.createElement("li");

	newItem = document.createElement("li");

	newItem.innerHTML = "Enter <strong>[!name!]</strong> to substitute for Name.";

	listItem.appendChild(newItem);

	newItem = document.createElement("li");

	newItem.innerHTML = "Enter <strong>[!email!]</strong> to substitute for E-Mail Address.";

	listItem.appendChild(newItem);

	var newItem;

	for (field in fieldList)

	{

		newItem = document.createElement("li");

		newItem.innerHTML = "Enter <strong>[!"+fieldList[field].name+"!]</strong> to substitute for "+fieldList[field].label+".";

		listItem.appendChild(newItem);

	}

	container.appendChild(listItem);

}







/*

*  Functions for the preview email function.

 */


function wpr_GetNewsletter()

{

    return document.mailForm.newsletter.value;



}



function wpr_GetSubject()

{

    return document.mailForm.subject.value;

}



function wpr_GetHtmlBody()

{

    return editor.getData();

}



function wpr_GetTextBody()

{

    return document.mailForm.body.value;

}



function wpr_GetWhetherHtmlEnabled()

{

    return (document.mailForm.htmlenabled.checked)?1:0;

}



function wpr_CheckWhetherImagesShouldBeAttached()

{

    return (document.mailForm.attachimages.checked)?1:0;

}



function showPreviewForm()

{

    var nid = wpr_GetNewsletter();

    if (window.open('<?php echo bloginfo("home") ?>/<?php echo PLUGINDIR ?>/wp-responder-email-autoresponder-and-newsletter-plugin/preview_email.php?nid='+nid,'previewWindow','width=500,height=500'))

    {

        

    }

    else

    {

       alert("Please disable your pop up blocker to see the preview email form.");

    }

}



function previewEmail()

{

    showPreviewForm();

}


</script>
<div style="float:right; background-color: #9F0; padding: 10px; display:block;"><strong>Time Now Is:</strong> <?php echo date("H:iA d F Y"); ?></div>
<div style="clear:both"></div>
<blockquote>
  <div class="wrap">
  <h2><?php echo ($parameters->formtitle)?$parameters->formtitle:"New Mail"; ?></h2>
  <?php if ($error) { ?>
  <div class="updated fade" style="background-color: rgb(255,241,204);">
    <div style="color:red; font-weight:bold; display:inline"> Error: </div>
    <?php echo $error ?></div>
  <?php } ?>
  <form action="<?php echo $_SERVER['REQUEST_URI'] ?>" name="mailForm" method="post">
    <table width="800" cellpadding="20" border="0" cellspacing="10">
      <?php 



if (!isset($_GET['aid']))

{

	$query = "SELECT id,name from ".$wpdb->prefix."wpr_newsletters";
	$newsletters = $wpdb->get_results($query);

	?>
      <tr>
        <td width="200"><label for="thenewsletter">Select A Newsletter:</label>
          <br>
          <small>Select the newsletter that receives this email broadcast.</small></td>
        <td width="474"><select style="width: 520px;" name="newsletter" id="thenewsletter" onchange="var davalue=this.options[this.selectedIndex].value; loadCustomFields(davalue); newsletterChanged(davalue) ">
            <?php

	  foreach ($newsletters as $newsletter )

	  {

		   ?>
            <option value="<?php echo $newsletter->id ?>" <?php if ($parameters->nid == $newsletter->id) { echo 'selected="selected"'; } ?> ><?php echo $newsletter->name ?></option>
            <?php

	  }

	  ?>
          </select></td>
      </tr>
      <?php

  }

  else

  {

	  $responder = _wpr_autoresponder_get($_GET['aid']);
	  $newsletter = _wpr_newsletter_get($responder->nid);

	  ?>
      <tr>
        <td width="200">Select A Newsletter:
          <p><small>Select the newsletter that receives this email.</td>
        <td width="474"><?php echo $newsletter->name ?></td>
      </tr>
      <input type="hidden" name="newsletter" id="thenewsletter" value="<?php echo $newsletter->id ?>" />
      <?php

  }

  ?>
      <tr>
        <td><label for="subject">Subject</label>
          <br>
          <small>Enter the subject of the email that your subscribers will receive</small></td>
        <td><input name="subject" value="<?php echo $parameters->subject ?>" type="text" id="subject" size="70" /></td>
      </tr>
      <tr>
        <td colspan="2"><label for="textbody">Text Body </label>
          <br />
          <small>Enter the email to be shown to subscribers who read your email in a mail client that doesn't support HTML email. </small>
          <div style="float:right"><a href="http://www.krusible.com/"><img style="border: 1px solid #000; width:300px; height: 250px;" src="http://www.wpresponder.com/mailpage.png" /></a></div>
          <textarea name="body" id="textbody" cols="55" rows="20" wrap="hard"><?php echo $parameters->textbody ?></textarea>
          
          <br />
          Hard breaks are inserted at the end of each line. 
          </p>
          <h2>Custom Fields:</h2>
          <div id="custom_fields">
            <?php

if (isset($_GET['nid']))

{

	$fields = _wpr_newsletter_all_custom_fields_get($nid);

	if (count($fields))
	{

		?>
            Use the following placeholders to be substituted in the newsletter.
            <ul>
              <?php

		foreach ($fields as $field)

		{

			?>
              <li>&lt;!<?php echo $field->name ?>!&gt; for <?php echo $field->label ?></li>
              <?php

		}

		?>
            </ul>
            <?php

	}

}



 ?>
          </div></td>
      </tr>
      <tr>
        <td colspan="2"><input type="checkbox" name="htmlenabled" id="htmlenabled" onchange="changeHTMLBodyFieldsAvailability(this.checked,'htmlbodyfields');" id="htmlenable" <?php if (!isset($paramaters->htmlenabled) || $parameters->htmlenabled=="on" ) { echo 'checked="checked"'; } ?> />
          <label for="htmlenable">Enable HTML Body</label>
          <br />
          <div id="htmlbodyfields"> <small>Check/uncheck this checkbox to enable or disable the HTML body of the email. When disabled only the text body will be sent.</small><br/>
            <br/>
            <input type="checkbox" id="attachimages" <?php if ($parameters->attachimages == 1) { echo 'checked="checked"'; } ?> name="attachimages" value="1">
            <label for="attachimages">Embed images in the HTML body to the email</label>
            <br/>
            <small>If you enable embedding images, subscribers on opening your email will not receive a security warning about loading external images. They will be able to see the images directly. But all the images in the HTML body will be sent to all subscribers so this may consume much bandwidth. The subscriber will not receive the images as email attachments but the images will be a part of the email.</small> <br/>
            <br/>
            <?php CreateNewTemplateSwitcherButton("editor","htmlbody"); ?>
            <label for="htmlbody">Enter the HTML Body Of The Email:</label>
            <br>
            <small>When HTML is enabled, most of your subscribers will see only the content in this body when they open the email.  If you don't enter a HTML body the email will be sent as text email.
            <div id="htmlwrapper">
              <textarea name="htmlbody" id="htmlbody" rows="20" cols="90"><?php echo htmlspecialchars($parameters->htmlbody) ?></textarea>
            </div>
            <br/>
            <input type="button" value="Disable WYSIWYG Editor" onclick="toggleHTML();this.value=(editorExists)?'Disable WYSIWYG Editor':'Enable WYSIWYG Editor';">
          </div></td>
      </tr>
      <?php

  if ($mode == "new")

  {

	  $theminute = date("i",$parameters->time);

	  $thehour = date("H",$parameters->time);

          if ($parameters->time)

            $date = date("m/d/Y",$parameters->time);

	  ?>
      <tr>
        <td>Send At: </td>
        <td><?php if (empty($parameters->time)) { ?>
          <input name="whentosend" <?php if ($parameters->whentosend == "now") { echo "checked=\"checked\""; } ?> type="radio" id="sendnow" value="now" checked="checked" />
          <label for="sendnow"> Immediately </label>
          (Now)<br />
          <input type="radio" <?php if ($parameters->whentosend == "date") { echo "checked=\"checked\""; } ?> name="whentosend" id="sendattime" value="date" />
          <label for="sendattime">
            <?php } ?>
            On
            <input type="text" name="date" id="date" value="<?php echo $date ?>">
          </label>
          at
          <select name="hour" onfocus="document.getElementById('sendattime').checked=true;">
            <?php

		 for ($i=0; $i<24; $i++)

  {

	  $hour = sprintf("%'02d",$i);

	  ?>
            <option <?php if ( $hour == $thehour ) { echo "selected=\"selected\""; } ?>><?php echo $hour; ?></option>
            <?php

  }

  ?>
          </select>
          :00 Hrs<br/>
          Date format: mm/dd/yyyy
       <script>

jQuery(document).ready(function()

{

	jQuery("#date").datepicker({ minDate: 0});

});



function changeHTMLBodyFieldsAvailability(field,nameOfTheDivToHide)

{

    if (!field)
        {
            document.getElementById(nameOfTheDivToHide).style.display = "none";
        }
		else
		{
			document.getElementById(nameOfTheDivToHide).style.display="inline";
		}

}


var editorExists=false;

var editor;
function toggleHTML()
{
    if (editorExists)
   {

           

            var html = editor.getData();

            editor.destroy();

            editorExists=false;

            var textElement = document.createElement("textarea");

            

        }

    else

        {

            var element = document.getElementById("htmlbody");            

            editor = CKEDITOR.replace("htmlbody",{

        toolbar :

        [



            ['Source','-','Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat','-','NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link','Image'],

            '/',

            ['Styles', 'Format','Font','FontSize','-','Cut','Copy','Paste','PasteText','PasteFromWord','-','Table'],



        ]



    });

            editorExists=true;

    }

}

function setVisibilityOfHTMLFields()
{
	changeHTMLBodyFieldsAvailability(document.getElementById('htmlenabled').checked,'htmlbodyfields');
}


setVisibilityOfHTMLFields();
toggleHTML();


</script></td>
      </tr>
      <?php

  }

  else

  {

	  ?>
      <tr>
        <td>Send On<br />
          <small>0 for immediately after subscribing</small></td>
        <td><label for="select2"></label>
          <label for="textfield2"></label>
          <input name="sequence" type="text" id="textfield2" size="4" maxlength="2" value="<?php echo (int) $parameters->sequence ?>" />
          <label for="radio3"> Days </label>
          
                 <script>

jQuery(document).ready(function()

{

	jQuery("#date").datepicker({ minDate: 0});

});



function changeHTMLBodyFieldsAvailability(field,nameOfTheDivToHide)

{

    if (!field)
        {
            document.getElementById(nameOfTheDivToHide).style.display = "none";
        }
		else
		{
			document.getElementById(nameOfTheDivToHide).style.display="inline";
		}

}


var editorExists=false;

var editor;
function toggleHTML()
{
    if (editorExists)
   {

           

            var html = editor.getData();

            editor.destroy();

            editorExists=false;

            var textElement = document.createElement("textarea");

            

        }

    else

        {

            var element = document.getElementById("htmlbody");            

            editor = CKEDITOR.replace("htmlbody",{

        toolbar :

        [



            ['Source','-','Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat','-','NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link','Image'],

            '/',

            ['Styles', 'Format','Font','FontSize','-','Cut','Copy','Paste','PasteText','PasteFromWord','-','Table'],



        ]



    });

            editorExists=true;

    }

}

function setVisibilityOfHTMLFields()
{
	changeHTMLBodyFieldsAvailability(document.getElementById('htmlenabled').checked,'htmlbodyfields');
}


setVisibilityOfHTMLFields();
toggleHTML();


</script>
          
          </td>
      </tr>
      <?php
	  printTheJavascript();

  }

  ?>
      <?php if ($mode == "new")

  {

	  ?>
      <tr>
        <td colspan="3"><br />
          <input type="hidden" name="mid" value="<?php echo $parameters->id ?>"  />
          <input type="hidden" name="recipients" id="recipients" value="<?php echo $parameters->recipients ?>" />
          <a href="javascript:showWindow();" class="button">Customize Recipients <img src="<?php bloginfo("siteurl") ?>/<?php echo PLUGINDIR ?>/wp-responder-email-autoresponder-and-newsletter-plugin/newwindow.gif" /></a><br />
          <br /></td>
      </tr>
      <?php

  }

  ?>
      <tr>
        <td colspan="2"><label for="button"></label>
          <input type="submit" class="button-primary" name="button" id="button" value="<?php echo ($parameters->buttontext)?$parameters->buttontext:"Send Message";?>"/>
          <input type="button" name="PreviewEmailButton" onclick="wpr_GetHtmlBody();previewEmail()" value="Preview This Email" class="button-primary"></td>
      </tr>
      <script>

  function getCurrentNewsletter()

  {

	  newsletter = document.getElementById('thenewsletter');

	  var nid = newsletter.options[newsletter.selectedIndex].value;

	  return nid;

  }

  

  function showSavedSet()

  {

      

  }

  

  jQuery(document).ready( 

		function () {
		var ele = document.getElementById('thenewsletter')

		if (ele.tagName == "SELECT")

		{

			var id = ele.options[document.getElementById('thenewsletter').selectedIndex].value

		}

		else

		{

			var id = ele.value;

		}

		loadCustomFields(id)

	}

);





/*

 * Javascript validation for this form

 * 

 */



  </script>
    </table>
  </form>
</blockquote>
</div>
<?php

}

function printTheJavascript()
{
	?>
              <script>

jQuery(document).ready(function()

{

	jQuery("#date").datepicker({ minDate: 0});

});



function changeHTMLBodyFieldsAvailability(field,nameOfTheDivToHide)

{

    if (!field)
        {
            document.getElementById(nameOfTheDivToHide).style.display = "none";
        }
		else
		{
			document.getElementById(nameOfTheDivToHide).style.display="inline";
		}

}


var editorExists=false;

var editor;
function toggleHTML()
{
    if (editorExists)
   {

           

            var html = editor.getData();

            editor.destroy();

            editorExists=false;

            var textElement = document.createElement("textarea");

            

        }

    else

        {

            var element = document.getElementById("htmlbody");            

            editor = CKEDITOR.replace("htmlbody",{

        toolbar :

        [



            ['Source','-','Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat','-','NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link','Image'],

            '/',

            ['Styles', 'Format','Font','FontSize','-','Cut','Copy','Paste','PasteText','PasteFromWord','-','Table'],



        ]



    });

            editorExists=true;

    }

}

function setVisibilityOfHTMLFields()
{
	changeHTMLBodyFieldsAvailability(document.getElementById('htmlenabled').checked,'htmlbodyfields');
}


setVisibilityOfHTMLFields();
toggleHTML();


</script>
<?php
}


