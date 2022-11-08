<?php require_once(__DIR__ . '/partials/header.php'); ?>
<body>
<?php require_once(__DIR__ . '/partials/navbar.php'); ?>

<div class="container p-5">

<div>
    <h1>Home</h1>

<?php
    if (isSessionValid())	//See if response if successful
    {
        echo "<h3>Welcome " . getUsername() . "</h3>";
    } else {
        echo "<h3>Welcome Guest</h3>";
	}
?>
</div>

</div>
</body>
<?php require_once(__DIR__ . '/partials/footer.php'); ?>