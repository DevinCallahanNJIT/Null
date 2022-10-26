<?php
if (isset($_COOKIE['Session'])) {
    echo "Session was set.\n";
    unset($_COOKIE['Session']); 
    setcookie('Session', null, -1, '/');
    echo "Session unset.";
}
if (isset($_COOKIE['Username'])) {
    echo "Username was set.\n";
    unset($_COOKIE['Username']); 
    setcookie('Username', null, -1, '/');
    echo "Username unset.";
}

die(header("Location: index.php"));

?>