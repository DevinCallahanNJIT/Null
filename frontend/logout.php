<?php

require_once(__DIR__ . "/lib/logging.php");

if (isset($_COOKIE['Session'])) {
    echo "Session was set.\n";
    $cookieArray = (array) json_decode( $_COOKIE["Session"]);
    unset($_COOKIE['Session']); 
    //unset($_COOKIE["Cart"]);
    setcookie('Session', null, -1, '/');
    echo "Session unset.";
    logging($cookieArray['username'] . " has logged out", __FILE__);
}

die(header("Location: index.php"));

?>