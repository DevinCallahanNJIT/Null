<?php require_once(__DIR__ . '/partials/header.php'); ?>
<body>
<?php require_once(__DIR__ . '/partials/navbar.php'); ?>

<div>
    <h1>Home</h1>

<?php
    if(isset($_COOKIE["Session"])){
        echo "Session Cookie Is Set!\n";
        if (isSessionValid())	//See if response if successful
        {
            echo "<h3>Welcome" . getUsername() . "</h3>";
        }else{
            echo "<h3>Welcome Guest</h3>";
        }

    }
?>
</div>

</body>
<?php require_once(__DIR__ . '/partials/footer.php'); ?>