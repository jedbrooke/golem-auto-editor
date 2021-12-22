<?php
	if($_SERVER["REQUEST_METHOD"] == "POST") {
		$handle = fopen("/var/www/backend/github.log","w");
		fwrite($handle, $_POST["payload"]);
		fclose($handle);
	} else {
		header('Location: https://github.com/jedbrooke/golem-auto-editor');
	}
?>