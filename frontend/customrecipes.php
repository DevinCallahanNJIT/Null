<?php require_once(__DIR__ . '/partials/header.php'); ?>
<body>
<?php require_once(__DIR__ . '/partials/navbar.php'); ?>

<div class="container p-5">
<div>
    <h1>Custom Recipes</h1>
    <h4>Create a custom drink recipe!</h4>
    
    <form method="POST">
        <input type="text" placeholder="Recipe Name" name="recipeName" required><br>
        <input type="text" placeholder="Ingredient 1" name="ingredient1" required><input type="text" placeholder="Measurement" name="measurement1" required>
        
        <?php for($counter=2;$counter<=15;$counter++): ?>
            <?php print_r('<div id="ingredient' . (string)$counter . '"></div>');?>
        <?php endfor; ?>

        <input type="button" name="addbutton" class="button" value="Add Another Ingredient " id="addingredient" />
        <br><br>
        <label>Instructions for Recipe</label><br>
        <textarea name="instructions" rows="6" cols="50" required></textarea>
        <br><br>
        <input type="text" placeholder="Picture URL" name="picture" required>
        <br><br>
        <input type="submit" name="submit" class="button" value="Submit" />

    </form>

</div>
</body>

<?php
    if(isset($_POST['submit']))  //starts php when user clicks submit button
    {
        $request = array();
        $request['type'] = "createRecipe";
        $request['cocktailName'] = $_POST['recipeName'];
        $request['username'] = getUsername();
        $request['instructions'] = $_POST['instructions'];
        $request['imageRef'] = $_POST['picture'];

        for($counter2=1;$counter2<=15;$counter2++):
            if(!empty($_POST['ingredient'.(string)$counter2])){
                if(!empty($_POST['measurement'.(string)$counter2])){
                    $request['ingredient'.(string)$counter2] = $_POST['ingredient'.(string)$counter2];
                    $request['measurement'.(string)$counter2] = $_POST['measurement'.(string)$counter2];
                }
                else{
                    print_r("Missing measurement for ingredient " . $counter2);
                }
            }
        endfor;
    
        $response = rabbitSearchClient($request);
        
        if ($response == 1){
            print_r($_POST['recipeName'] . " was published!");
            logging($_POST['recipeName'] . " was published!", __FILE__);

        }
        else {
            print_r($_POST['recipeName'] . " could not be published.");
            loggingWarn($_POST['recipeName'] . " could not be published.", __FILE__);
        }
    }
?>

<script>
    const element = document.getElementById("addingredient");
    var add = 1;
    element.addEventListener("click", myFunction, add);
    function myFunction() {      
        add++; 
        if (add == 2)
            document.getElementById("ingredient2").innerHTML = '<input type="text" placeholder="Ingredient 2" name="ingredient2" ><input type="text" placeholder="Measurement" name="measurement2" >';
        if (add == 3)
            document.getElementById("ingredient3").innerHTML = '<input type="text" placeholder="Ingredient 3" name="ingredient3" ><input type="text" placeholder="Measurement" name="measurement3" >';
        if (add == 4)
            document.getElementById("ingredient4").innerHTML = '<input type="text" placeholder="Ingredient 4" name="ingredient4" ><input type="text" placeholder="Measurement" name="measurement4" >';
        if (add == 5)
            document.getElementById("ingredient5").innerHTML = '<input type="text" placeholder="Ingredient 5" name="ingredient5" ><input type="text" placeholder="Measurement" name="measurement5" >';
        if (add == 6)
            document.getElementById("ingredient6").innerHTML = '<input type="text" placeholder="Ingredient 6" name="ingredient6" ><input type="text" placeholder="Measurement" name="measurement6" >';
        if (add == 7)
            document.getElementById("ingredient7").innerHTML = '<input type="text" placeholder="Ingredient 7" name="ingredient7" ><input type="text" placeholder="Measurement" name="measurement7" >';
        if (add == 8)
            document.getElementById("ingredient8").innerHTML = '<input type="text" placeholder="Ingredient 8" name="ingredient8" ><input type="text" placeholder="Measurement" name="measurement8" >';
        if (add == 9)
            document.getElementById("ingredient9").innerHTML = '<input type="text" placeholder="Ingredient 9" name="ingredient9" ><input type="text" placeholder="Measurement" name="measurement9" >';
        if (add == 10)
            document.getElementById("ingredient10").innerHTML = '<input type="text" placeholder="Ingredient 10" name="ingredient10" ><input type="text" placeholder="Measurement" name="measurement10" >';
        if (add == 11)
            document.getElementById("ingredient11").innerHTML = '<input type="text" placeholder="Ingredient 11" name="ingredient11" ><input type="text" placeholder="Measurement" name="measurement11" >';
        if (add == 12)
            document.getElementById("ingredient12").innerHTML = '<input type="text" placeholder="Ingredient 12" name="ingredient12" ><input type="text" placeholder="Measurement" name="measurement12" >';
        if (add == 13)
            document.getElementById("ingredient13").innerHTML = '<input type="text" placeholder="Ingredient 13" name="ingredient13" ><input type="text" placeholder="Measurement" name="measurement13" >';
        if (add == 14)
            document.getElementById("ingredient14").innerHTML = '<input type="text" placeholder="Ingredient 14" name="ingredient14" ><input type="text" placeholder="Measurement" name="measurement14" >';
        if (add == 15){
            document.getElementById('addingredient').style.visibility = 'hidden';
            document.getElementById("ingredient15").innerHTML = '<input type="text" placeholder="Ingredient 15" name="ingredient15" ><input type="text" placeholder="Measurement" name="measurement15" >';
        }
    }
</script>

</div>
<?php require_once(__DIR__ . '/partials/footer.php'); ?>