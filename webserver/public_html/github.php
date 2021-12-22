<?php
	if($_SERVER["REQUEST_METHOD"] == "POST") {
        $secret = file_get_contents("/var/www/backend/gh_secret.txt");
		$handle = fopen("/var/www/backend/github.log","w");
        $digest = 'sha256=' . hash_hmac('sha256',file_get_contents("php://input"), $secret);
        if(hash_equals($_SERVER["HTTP_X_HUB_SIGNATURE_256"],$digest)) {
            fwrite($handle, $_POST["payload"]);
        } else {
            fwrite($handle,"digest: $digest\n");
            fwrite($handle,"server digest: " . $_SERVER["HTTP_X_HUB_SIGNATURE_256"] . "\n");
            fwrite($handle,"hash mismatch");
        }
		fclose($handle);
	} else {
		header('Location: https://github.com/jedbrooke/golem-auto-editor');
	}
?>