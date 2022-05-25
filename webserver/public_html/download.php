<?php
    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $token = $_POST["token"];
        // check if the video for this token is done
        $job_path = "/var/www/backend/queue/jobs/$token.json";
        if (file_exists($job_path)) {
            $job = json_decode(file_get_contents($job_path), true);
            if (strcmp($job["status"],"finished") == 0) {
                header("refresh:1; url=send_video.php?token=$token");
            }
        }

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
                        $token = $_POST["token"];
                        echo "<pre>" . htmlspecialchars($_POST["token"]) . "</pre>";
                        // check if the video for this token is done
                        $job_path = "/var/www/backend/queue/jobs/$token.json";
                        $job = NULL;
                        if (file_exists($job_path)) {
                            $job = json_decode(file_get_contents($job_path), true);
                        } else {
                            $job = array("status" => "missing");
                        }
                        $msg = "";
                        switch ($job["status"]) {
                            case 'waiting':
                                $msg = "Your video is still waiting in the queue to be processed";
                                break;
                            case 'started':
                                // TODO: fancy progress monitoring
                                $msg = "Your video is currently processing! check back here to check if it's done";
                                break;
                            case 'finished':
                                // $msg = "Your download is ready! it will begin shortly";
                                $msg = <<<END
                                Your download is ready! it will begin shortly <br>
                                <a href="send_video.php?token=$token">click here if it did not download</a>
END;
                                break;
                            case 'missing':
                                $msg = "Error: the token you provided is not valid. make sure you copied it correctly";
                                break;
                            default:
                                $msg = "Wait, this shouldn't happen";
                                break;
                        }
                        echo "<div>$msg</div>";
                        if ($job["status"] == 'missing') {
                            include "tokenform.html";
                        }

                    } elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
                        include "tokenform.html";
                    }
                ?>
                <br>
            </div>
            <div class="row">
                <div><a href="/" class="btn btn-secondary btn-lg">Back to main page</a></div>
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