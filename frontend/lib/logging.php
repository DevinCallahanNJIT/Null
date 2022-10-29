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

function outputLog($msg){
    logger("%date% %file% %level% %message%", ["date" => date("Y-m-d h:i:sa", time()), "level" => "warning", "message" =>$msg, "file" =>__FILE__], "output.log");
}
function warningLog($msg){
    logger("%date% %file% %level% %message%", ["date" => date("Y-m-d h:i:sa", time()), "level" => "warning", "message" =>$msg, "file" =>__FILE__], "warning.log");
}
function errorLog($msg){
    logger("%date% %file% %level% %message%", ["date" => date("Y-m-d h:i:sa", time()), "level" => "error", "message" =>$msg, "file" =>__FILE__]);

}

?>