<?php require_once(__DIR__ . '/partials/header.php'); ?>
<body>
<?php require_once(__DIR__ . '/partials/navbar.php'); ?>


<?php
$request = array();
$request['type'] = "searchRecipe";
$request['ingredientName'] = "Tequila";
$response = rabbitSearchClient($request);

if (isSessionValid())	//See if response if successful
    {
        echo "<h3>Welcome " . getUsername() . "</h3>";
    } else {
        echo "<h3>Welcome Guest</h3>";
	}

echo(var_dump($response));

?>
</body>