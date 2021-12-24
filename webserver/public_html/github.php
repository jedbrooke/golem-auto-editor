<?php
	if($_SERVER["REQUEST_METHOD"] == "POST") {
        $secret = file_get_contents("/var/www/backend/gh_secret.txt");
        $digest = 'sha256=' . hash_hmac('sha256',file_get_contents("php://input"), $secret);
        if(hash_equals($_SERVER["HTTP_X_HUB_SIGNATURE_256"],$digest)) {
            exec("cd /var/www/git-repo && git pull");
            exec("rsync -ra /var/www/git-repo/webserver/public_html/ /var/www/html");
            exec("rsync -ra /var/www/git-repo/webserver/backend/ /var/www/backend");
        } else {
            fwrite($handle,"digest: $digest\n");
            fwrite($handle,"server digest: " . $_SERVER["HTTP_X_HUB_SIGNATURE_256"] . "\n");
            fwrite($handle,"hash mismatch");
        }
	} else {
		header('Location: https://github.com/jedbrooke/golem-auto-editor');
	}
?>