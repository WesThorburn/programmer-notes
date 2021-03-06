<script type="text/javascript" src="{{ URL::to('/vendor/js/tinymce/tinymce.min.js') }}"></script>
<script type="text/javascript">
	tinymce.init({
		"selector":"#solution",
		"height":"450px",
		"language":"en",
		"theme":"modern",
		"skin":"lightgray",
		"menubar":"",
		"plugins":["advlist autolink link image lists charmap hr anchor pagebreak","searchreplace wordcount visualblocks visualchars fullscreen insertdatetime nonbreaking","save table contextmenu directionality template paste"],
		"toolbar":"undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist",
		"content_css":"/css/tinymce-content.css",
		"setup":function(ed) {
		    ed.on('keyup', function(e) {
		    	handleSave();
		    });
		}
	});

	var waitStatus = "ready";
	var saveTimer;

	function handleSave(){
		if(waitStatus == "ready"){
			changeSaveButton('needsToBeSaved');
			waitStatus = "waiting";
			saveTimer = setTimeout(saveForm, 2000);
		}
		else if(waitStatus == "waiting"){
			clearTimeout(saveTimer);
			saveTimer = setTimeout(saveForm, 2000);
		}
	}

	function changeSaveButton(saveStatus){
		if(saveStatus == "needsToBeSaved"){
			$('#saveButton').removeClass("colour-green");
			document.getElementById("saveButton").innerHTML = 'Save <span class="glyphicon glyphicon-floppy-disk">';
		} else if(saveStatus == "hasBeenSaved"){
			$('#saveButton').addClass("colour-green");
			document.getElementById("saveButton").innerHTML = 'Saved <span class="glyphicon glyphicon glyphicon-ok">';
		}
	}

	//Notes edit AJAX posting
	function saveForm(){
		clearTimeout(saveTimer);
		var $form = $( this ),
			csrfToken = document.getElementsByName("_token")[0].value;
			problem = document.getElementById("problem").value;
			solution = tinyMCE.activeEditor.getContent();
			privateNote = document.getElementById("privateNote").checked;

		var posting = $.post( "/note/{{ $note->id }}", { _method: 'put', _token: csrfToken, problem: problem, solution: solution, privateNote: privateNote } );

		posting.done(function( data ) {
			changeSaveButton("hasBeenSaved");
			waitStatus = "ready";
		});
	}
</script>