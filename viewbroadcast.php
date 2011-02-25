<?php

$id = $_GET['id'];
$query = "select * from ".$wpdb->prefix."wpr_newsletter_mailouts where id=$id";
$mailout = $wpdb->get_results($query);
$mailout = $mailout[0];
$output["Subject"] = $mailout->subject;
$output["Text Body"] = "<pre>".$mailout->textbody."</pre>";
$output["HTML Body"] = $mailout->htmlbody;
$output["Sent At"] = date("H:ia \o\\n dS F Y",$mailout->time);
$newsletter = _wpr_newsletter_get($mailout->nid);
$output["Newsletter"] = $newsletter->name;
$output["Recipients"] = (!$mailout->recipients)?"All Subscribers":$mailout->recipients;
?>
<h2>Viewing Broadcast</h2>
<table width="800" border="1" style="border: 1px solid #ccc;" cellpadding="10">
  <tr>
    <td ><strong>Subject Of E-Mail:</strong></td>
    <td ><?php echo $output["Subject"] ?></td>
  </tr>
  <tr>
    <td><strong>Newsletter:</strong></td>
    <td><?php echo $output["Newsletter"] ?></td>
  </tr>
  <tr>
    <td  colspan="2"><h2>Text Body:</h2><br>
      <div style="height: 400px; overflow:scroll">
        <pre>
		<?php echo $output["Text Body"] ?>
        
        </pre>
        </div>
</td>
  </tr>
  <tr>
    <td colspan="2"> <h2>HTML Body:</h2>      
      <iframe width="100%" height="400" scrolling="yes" frameborder="0" border="0" id="htmlbodyframe">
      </iframe>
      <script>

function base64Decode(data){data=data.replace(/[^a-z0-9\+\/=]/ig,'');if(typeof(atob)=='function')return atob(data);var b64_map='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';var byte1,byte2,byte3;var ch1,ch2,ch3,ch4;var result=new Array();var j=0;while((data.length%4)!=0){data+='=';}
for(var i=0;i<data.length;i+=4){ch1=b64_map.indexOf(data.charAt(i));ch2=b64_map.indexOf(data.charAt(i+1));ch3=b64_map.indexOf(data.charAt(i+2));ch4=b64_map.indexOf(data.charAt(i+3));byte1=(ch1<<2)|(ch2>>4);byte2=((ch2&15)<<4)|(ch3>>2);byte3=((ch3&3)<<6)|ch4;result[j++]=String.fromCharCode(byte1);if(ch3!=64)result[j++]=String.fromCharCode(byte2);if(ch4!=64)result[j++]=String.fromCharCode(byte3);}
return result.join('');}
	  var theFrame = document.getElementById('htmlbodyframe');
	  var thecontent = '<?php echo base64_encode($output['HTML Body']) ?>';
	  theFrame.contentDocument.write(base64Decode(thecontent));
	  </script></td>
  </tr>
  <tr >
    <td >Recipients:</td>
    <td ><?php echo $output["Recipients"]?></td>
  </tr>
  <tr>
    <td>Sent At:</td>
    <td><?php echo $output["Sent At"] ?></td>
  </tr>
</table><br />
<br />

<a href="admin.php?page=wpresponder/allmailouts.php" class="button-primary" style="margin:10px; margin-top:20px;">&laquo; Back</a>
