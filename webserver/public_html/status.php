<?php
    if($_SERVER["REQUEST_METHOD"] == "POST") {
        echo "hello";
    } else {
        header("Location: /");
        exit;
    }
?>