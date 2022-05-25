<?php 
    $token = $_GET["token"];
    // check if the video for this token is done
    $job_path = "/var/www/backend/queue/jobs/$token.json";
    if (file_exists($job_path)) {
        $job = json_decode(file_get_contents($job_path), true);
        if (strcmp($job["status"],"finished") == 0) {
            $file = $job["output_path"];
            if (file_exists($file)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.$job["orig_name"].'"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file));
                readfile($file);
            }
        }
    }
?>