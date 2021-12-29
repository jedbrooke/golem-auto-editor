/*
filedrag.js - HTML5 File Drag & Drop demonstration
Featured on SitePoint.com
Developed by Craig Buckler (@craigbuckler) of OptimalWorks.net
*/
(function() {

	// getElementById
	function $id(id) {
		return document.getElementById(id);
	}


	// output information
	function Output(msg) {
		var m = $id("messages");
		m.innerHTML = msg;
		
	}


	// file drag hover
	function FileDragHover(e) {
		e.stopPropagation();
		e.preventDefault();
		e.target.className = (e.type == "dragover" ? "hover" : "");
	}


	// file selection
	function FileSelectHandler(e) {

		// cancel event and hover styling
		FileDragHover(e);

		// fetch FileList object
		var files = e.target.files || e.dataTransfer.files;

		// process all File objects
		for (var i = 0, f; f = files[i]; i++) {
			ParseFile(f);
		}

		let input = document.getElementById("filePath");
		input.files = files;
		document.getElementById("error-box").innerHTML = '';

	}

	// output file information
	function ParseFile(file) {
		console.log("file is type " + file.type);
		if(file.type.substring(0, 6) === "video/" || file.type.substring(0, 6) === "audio/") {
			Output(
				"File information: " + file.name
			);
			$id("exportButton").title = "Click here to upload";
			
		} else {
			Output("Please upload a video or audio file (.mp4, .mp3 ...etc)");
			$id("exportButton").title = "Please select a video!";
		}
		

	}


	// initialize
	function Init() {

		var fileselect = $id("filePath"),
			filedrag = $id("filedrag");
		$id("exportButton").title = "Please select a video!";
		// file select
		fileselect.addEventListener("change", FileSelectHandler, false);

		// is XHR2 available?
		var xhr = new XMLHttpRequest();
		if (xhr.upload) {

			// file drop
			filedrag.addEventListener("dragover", FileDragHover, false);
			filedrag.addEventListener("dragleave", FileDragHover, false);
			filedrag.addEventListener("drop", FileSelectHandler, false);
			filedrag.style.display = "block";
		}



	}

	// call initialization file
	if (window.File && window.FileList && window.FileReader) {
		Init();
	}


})();