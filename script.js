var listOfEditors= new Array();;
/*
Contract: number->boolean
Precondition: The editor doesn't already exist.
Postcondition: The editor is created and the contents of the text editor, if any, is loaded into the wysiwyg editor.
*/


function createEditor(editorId)
{
     //there is already a editor, no need to create it again
     var editor = eval("listOfEditors["+editorId+"]");
     if (editor)
     {
     	return;     
     }

     //get the contents of the current text editor
/*
     var textAreaNumber= "htmlbody-"+editorId;
     var sourceCode = document.getElementById(textAreaNumber).value;
     document.getElementById("editor-"+editorId).removeChild(document.getElementById(textAreaNumber));
  */
     //create the editor
	document.getElementById("editor-"+editorId);
	listOfEditors[editorId] = CKEDITOR.replace( 'htmlbody-'+editorId, {
        toolbar :
        [
            

            
            
            ['Source','-','Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat','-','NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link','Image'],
            '/',
            ['Styles', 'Format','Font','FontSize','-','Cut','Copy','Paste','PasteText','PasteFromWord','-','Table'],

        ]

    } );
	
}

function removeEditor(editorId)
{
      //get the editor
      if (!listOfEditors[editorId])
          return;
      var html = listOfEditors[editorId].getData();      
      listOfEditors[editorId].destroy();
      //create the texteditor
      theTextArea=document.createElement("textarea");
      theTextArea.setAttribute("rows","20");
      theTextArea.setAttribute("cols","80");
      theTextArea.setAttribute("id","htmlbody-"+editorId);
      theTextArea.setAttribute("name","htmlbody-"+editorId);
      theTextArea.innerHTML = html;      
      //set the data.
      document.getElementById("editor-"+editorId).appendChild(theTextArea);
      delete listOfEditors[editorId];
      
      
      
}


function toggleStatus(editorId,status) {
    document.getElementById("editorformitems-"+editorId).style.display=(!status)?"inline":"none";

}

function toggleCustomization(editorId,status)
{
    document.getElementById("customizationsform-"+editorId).style.display=(status)?"none":"inline";
}

function toggleHtmlBody(editorId,status)
{
    document.getElementById("htmlformitems-"+editorId).style.display=(status)?"inline":"none";
}

function changeTemplate(editor,nameOfTextArea)
{
      if (editor)
      {
          //
      }
}

