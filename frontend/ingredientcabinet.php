<?php require_once(__DIR__ . '/partials/header.php'); ?>
<body>
<?php require_once(__DIR__ . '/partials/navbar.php'); ?>

<div class="container p-5">
<div>
    <h1>Ingredient Cabinet</h1>
    <form method="POST">
    <div>
        <h4>Enter the ingredient(s) that you own</h4>
        <input type="text" placeholder="Add Ingredient" name="ingredient" >
        <input type="submit" value="Add" name="submit" />
    </div>
</form>
</div>
<br>
<h4>Your Ingredients</h4>
<?php
if (isSessionValid() == true)
{
    if(isset($_POST['submit']))  //starts php when user clicks submit button
    {
        $username = getUsername();
        $ingredient = $_POST['ingredient'];    //getting the ingredient from the form

        $request = array();
        $request['type'] = "fetchAndCreateIngredientCabinet";
        $request['username'] = $username;
        $request['ingredientName'] = $ingredient;
        $response = rabbitSearchClient($request);
        if($response['created'] == 1){
            print_r("Successfully Added " . $ingredient);
            logging("Successfully Added " . $ingredient, __FILE__);
        }
        else{
            print_r($ingredient . " couldn't be added (duplicate or it doesn't exist).");
            loggingWarn($ingredient . "couldn't be added (duplicate or it doesn't exist).", __FILE__);
        }


    }
    else
    {
        $username = getUsername();
        $request2 = array();
        $request2['type'] = "fetchIngredientCabinet";
        $request2['username'] = $username;
        $response2 = rabbitSearchClient($request2);
        
        for($counter=1;$counter<=$response2['numResults'];$counter++):
            print_r(" - " . $response2['ingredientName'.(string)$counter] . "<br>");
        endfor;
        logging("Fetched Ingredient Cabinet", __FILE__);
    }
}
else
{
    die(header("Location: index.php"));
}

?>
<div>
    <?php for($counter=1;$counter<=$response['ingredientCabinet']['numResults'];$counter++): ?>
    <?php print_r(" - " . $response['ingredientCabinet']['ingredientName'.(string)$counter] . "<br>");?>
    <?php endfor; ?>
</div>




    </div>
</body>
<?php require_once(__DIR__ . '/partials/footer.php'); ?>