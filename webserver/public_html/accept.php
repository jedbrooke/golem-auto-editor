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
                            <a href="http://auto-editor.com/"><span id="auto">Auto</span><span
                                    id="editor">-Editor</span></a>&nbsp;On Golem
                        </h1>
                        <h3><sup>Beta!</sup></h3>
                    </div>
                </div>
            </div>
            <div class="row">
                <?php
                    if($_SERVER["REQUEST_METHOD"] == "POST") {
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
                                    $error = 'Exceeded filesize limit.';
                                    break;
                                default:
                                    $file_ok = false;
                                    $error = 'Unknown errors.';
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
                            echo "File is good!";
                        } else {
                            echo "<br>";
                            echo $error;
                        }


                    
                    } elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
                        echo "GET";
                        echo $_SERVER["HTTP_USER_AGENT"];
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