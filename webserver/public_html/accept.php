<?php
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        header('Location: /');
    }
?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]>      <html class="no-js"> <!--<![endif]-->
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Upload Video - auto-editor online</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        <!-- CSS only -->
        <link rel="stylesheet" href="styles.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

        <!-- JavaScript Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
        crossorigin="anonymous"></script>
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="#">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->
    <div class="flex-wrapper">
        <main class="container main-content">
            <div class="row">
                <div class="col-md-12" id="title-banner">
                    <div class="mouseover" style="display:flex;">
                        <h1>
                            <a href="http://auto-editor.online/"><span id="auto">Auto</span><span
                                    id="editor">-Editor</span></a>&nbsp;On Golem
                        </h1>
                        <h3><sup>Beta!</sup></h3>
                    </div>
                </div>
            </div>
            <div class="row">
                <?php
                    $PYTHON3="/usr/bin/python3";
                    $MAX_USER_REQUESTS = 100;
                    $MAX_IP_REQUESTS = 1000;
                    if($_SERVER["REQUEST_METHOD"] == "POST") {
                        $pepper = file_get_contents("/var/www/backend/pepper.txt");
                        $ip = hash("sha256",$pepper.$_SERVER["REMOTE_ADDR"]);
                        $user_agent = hash("sha256",$pepper.$_SERVER["HTTP_USER_AGENT"]);
                        $db_cmd = "$PYTHON3 /var/www/backend/logip.py $ip $user_agent";
                        $requests_data = json_decode(exec($db_cmd));
                        $quota_exceeded = ($requests_data->ip > $MAX_IP_REQUESTS) || ($requests_data->indv > $MAX_USER_REQUESTS);
                        if ($quota_exceeded) {
                            echo "Sorry, this IP address has exceeded it's daily quota, try again tomorrow.";
                            echo "<br>";
                        } else {

                            // echo "POST";
                            // echo "<br>";
                            // foreach($_POST as $key => $value) {
                            //     echo htmlspecialchars($key) . " = " . htmlspecialchars($value);
                            //     echo "<br>";
                            // }
                            // foreach($_FILES as $file => $value) {
                            //     foreach($value as $key => $parameters) {
                            //         echo htmlspecialchars($key) . " = " . htmlspecialchars($parameters);
                            //         echo "<br>";
                            //     }
                            // }
    
                            $file_ok = true;
                            $error = "";
    
                            // check php upload errors
                            if (sizeof($_POST) == 0) {
                                $file_ok = false;
                                $error = "Error, invalid form submission";
                            }
    
                            // check file upload errors
                            if ($file_ok) {
                                switch ($_FILES['filePath']['error']) {
                                    case UPLOAD_ERR_OK:
                                        break;
                                    case UPLOAD_ERR_NO_FILE:
                                        $file_ok = false;
                                        $error = 'No file sent.';
                                        break;
                                    case UPLOAD_ERR_INI_SIZE:
                                    case UPLOAD_ERR_FORM_SIZE:
                                        $file_ok = false;
                                        $error = 'Sorry, your file exceeded the upload limit of 250MB.';
                                        break;
                                    default:
                                        $file_ok = false;
                                        $error = 'Sorry, an unknown error occurred.';
                                }
                            }
                            
                            // check mime type
                            if ($file_ok) {
                                $mime = explode('/',mime_content_type($_FILES["filePath"]["tmp_name"]))[0];
                                if(strcmp('video',$mime) != 0) {
                                    $file_ok = false;
                                    $error = "\"" . htmlspecialchars($_FILES["filePath"]["name"]) . "\" is not a valid video file.";
                                }
    
                            }
    
                            // check if it is actually readable by ffprobe
                            if ($file_ok) {
                                // $cmd = "/usr/bin/ffprobe -v quiet -print_format json -show_format -show_streams " . $_FILES["filePath"]["tmp_name"] . " 2>&1";
                                $cmd = "/usr/bin/ffprobe -hide_banner " . $_FILES["filePath"]["tmp_name"] . " 2>&1";
                                $ret = null;
                                exec($cmd,$return_var=$ret);
                                if ($ret != 0) {
                                    $file_ok = false;
                                    $error = "Error reading \"" . htmlspecialchars($_FILES["filePath"]["name"]) . "\", make sure it is a valid video file.";
                                }
                            }
                            
                            if ($file_ok) {
                                // handle job
                                echo "<br>";
                                echo "Your video was succesfully uploaded!";
                                // $job_info["sha1"] = sha1_file($_FILES["filePath"]["tmp_name"]);
                                $token = bin2hex(random_bytes(16));
                                $ext = array_pop(explode(".",$_FILES["filePath"]["name"]));
                                $dest = "/var/www/backend/queue/files/$token.$ext";
                                $success = move_uploaded_file($_FILES["filePath"]["tmp_name"],$dest);
                                chmod($dest, 0764);
                                $job_info = array();
                                $job_info["video_path"] = $dest;
                                // this will be useful when creating the output file
                                $job_info["video_ext"] = $ext;
                                $job_info["status"] = "waiting";
                                $job_info["orig_name"] = $_FILES["filePath"]["name"];
                                $job_info["creation_time"] = date_timestamp_get(date_create());
                                $job_info["finished_time"] = -1;
                                $args = "";
                                foreach($_POST as $key => $value) {
                                    if ($value != '') {
                                        $args .= escapeshellarg("--$key") . " " . escapeshellarg($value);
                                        $args .= " "; 
                                    }
                                }
                                $job_info["args"] = $args;
                                echo "<br>";
                                echo "your access token is: <pre>$token</pre>";                                
                                $job_info["token"] = $token;
                                $job_info["job_path"] = "/var/www/backend/queue/jobs/$token.json";
                                file_put_contents($job_info["job_path"],json_encode($job_info));
                                chmod($job_info["job_path"],0764);

                                include "success.html";
                                echo "<form id='viewForm' action='download.php' method='POST'>";
                                echo "<input type='hidden' name='token' value='$token'/>";
                            } else {
                                echo "<br>";
                                echo $error;
                            }
                        }

                    }
                ?>
            </div>
        </main>
        <footer class="footer">
            <a style="color:#adb5bd;" href="https://github.com/jedbrooke/golem-auto-editor">Find us on GitHub</a>
            <img src="GithubLogo.png" alt="GitHub" style="width:2em; height:2em;">
        </footer>
    </div>
        <script src="" async defer></script>
    </body>
</html>