<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]>      <html class="no-js"> <![endif]-->
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>PHP</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="">
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="#">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->
        <pre>
        <?php
            //TODO: load this from a config file
            $pepper="some_random_string";
            $PYTHON3="/usr/bin/python3";

            echo(passthru("$PYTHON3 hello.py"));
            echo("<br>");
            echo(passthru("$PYTHON3 /backend/logip.py " . hash("sha256",$pepper.$_SERVER["REMOTE_ADDR"])));
            // echo(passthru("ls -l /backend/iplog.db"));
        ?>
        </pre>

    </body>
</html>