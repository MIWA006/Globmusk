<?php
// autoload.php

spl_autoload_register(function ($class) {
    // Base directory for PHPMailer library
    $base_dir = __DIR__ . '/Lib/PHPMailer/';

    // Replace namespace separators with directory separators in the class name
    $file = $base_dir . str_replace('\\', '/', $class) . '.php';

    // Require the file if it exists
    if (file_exists($file)) {
        require $file;
    }
});