<?php

function logger($message, array $data, $logFile = "error.log"){
    foreach ($data as $key => $val) {
      $message = str_replace("%{$key}%", $val, $message);
    }
    $message .= PHP_EOL;
    $_logFile = __DIR__ . '/../../logs/' . $logFile;
    return file_put_contents($_logFile, $message, FILE_APPEND);
}

// logger("%file% %level% %message%", ["level" => "warning", "message" =>"This is a message", "file" =>__FILE__]);

function logging($msg, $file){
    logger("%date%:%level%:%file%:%message%", ["date" => date("Y-m-d h:i:s", time()), "level" => "INFO", "message" =>$msg, "file" =>$file], "output.log");
}
function loggingWarn($msg, $file){
    logger("%date%:%level%:%file%:%message%", ["date" => date("Y-m-d h:i:s", time()), "level" => "ERROR", "message" =>$msg, "file" =>$file], "warning.log");
}
function loggingError($msg, $file){
    logger("%date%:%level%:%file%:%message%", ["date" => date("Y-m-d h:i:s", time()), "level" => "CRITICAL", "message" =>$msg, "file" =>$file]);
}

?>