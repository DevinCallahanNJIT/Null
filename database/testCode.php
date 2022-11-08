<?php

/*
	Nothing In This File Is Relevant Towards The System.
	This Code Exists Just To Test Parts Of Functions Being Developed Independently, And Is Changed Frequently.
	Code In This File May Exist As Incomplete Code And Should Not Be Referenced For Other Code.
*/

require_once('../lib/path.inc');
require_once('../lib/get_host_info.inc');
require_once('../lib/rabbitMQLib.inc');
require_once('./connectDatabase.php');

	//Create Connection
	$connection = connDB();

	$request = array();
	$request['type']='createRecipe';
	$request['cocktailName']='Mimosa';
	$request['username']='test';
	$request['instructions']='Ensure both ingredients are well chilled, then mix into glass. Serve cold.';
	$request['imageRef']='https://www.thecocktaildb.com/images/media/drink/juhcuu1504370685.jpg';
	$request['ingredient1']='Champagne';
	$request['measurement1']='Chilled';
	$request['ingredient2']='Orange Juice';
	$request['measurement2']='2 oz';


		//Get New Cocktail ID (1 higher than the highest valued cocktail id);
		$result = mysqli_query($connection, 'SELECT MAX(cocktailID) FROM Cocktail');
		$result = mysqli_fetch_array($result);
		$cocktailID = $result[0]+1;

		$defaultRating = "0";
	
		//Query Prepared Statement
		$query = $connection->prepare('INSERT INTO Cocktail VALUES (?, ?, ?, ?, ?, ? )');
		//Binds Username and Password Into Query Statement
		$query->bind_param("ssssss", $cocktailID, $request['cocktailName'], $request['username'], $request['instructions'], $request['imageRef'], $defaultRating);	
			
		//If query executes successfully, return success message
		if ($query->execute()) {
			echo "Cocktail Successfully Added Into Cocktail Table \n".PHP_EOL;
			//logging("Cocktail Successfully Added Into Cocktail Table", __FILE__);
		}
		//If the last query failed and gave an error, return an error
		if ($connection->errno) {
			echo "Database Error: Failed To Insert Data Into Cocktail Table \n".PHP_EOL;
			//loggingWarn("Database Error: Failed To Insert Data Into Cocktail Table", __FILE__);
			//return false;
		}

	$numIngredients = (count($request)-5)/2;

	for($iter=1; $iter <= $numIngredients; $iter++){

		//Query Prepared Statement
		$query = $connection->prepare('INSERT INTO Recipe VALUES (?, ?, ?)');
		//Binds Username and Password Into Query Statement
		$query->bind_param("sss", $cocktailID, $request['ingredient'.(string)$iter], $request['measurement'.(string)$iter]);	
			
		//If query executes successfully, return success message
		if ($query->execute()) {
			echo "Recipe Successfully Added Into Recipe Table \n".PHP_EOL;
			//logging("Recipe Successfully Added Into Recipe Table", __FILE__);
		}
		//If the last query failed and gave an error, return an error
		if ($connection->errno) {
			echo "Database Error: Failed To Insert Data Into Recipe Table \n".PHP_EOL;
			//loggingWarn("Database Error: Failed To Insert Data Into Recipe Table", __FILE__);
			//return false;
		}

	}

?>