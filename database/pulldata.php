
<?php
require_once('../lib/path.inc');
require_once('../lib/get_host_info.inc');
require_once('../lib/rabbitMQLib.inc');
require_once('./connectDatabase.php');
require_once('./logging.php');
require_once  '/home/ubuntu/Null/lib/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$server = new rabbitMQServer("../lib/rabbitMQ.ini","API");
echo "API Server BEGIN \n".PHP_EOL;
$server->consumeAPI('doThing');
echo "API Server END \n".PHP_EOL;
exit();


function doThing($request){

	echo "Fetched Recipes From API".PHP_EOL;
	logging("Fetched Recipes From API", __FILE__);

	//Create Connection
	$connection = connDB();

   for($i=0; $i<count($request); $i++){

		//Query Prepared Statement
		$query = $connection->prepare('SELECT cocktailName FROM Cocktail WHERE cocktailName = ?');
		//Binds Username and Password Into Query Statement
		$query->bind_param("s", $request['strDrink']);	

		//Execute Query
		$query->execute();
		$result = $query->get_result();
			
		//If search has no results, add to db.
		if(mysqli_num_rows($result)<1){
			addRecipe($request[$i]);
		}else{

			echo "Skipping Recipe, Already In DB".PHP_EOL;
			logging("Skipping Recipe, Already In DB", __FILE__);

		}

   }

}

function addRecipe($request){

    echo "Beginning To Create New Recipe".PHP_EOL;
	logging("Beginning To Create New Recipe", __FILE__);

	//Create Connection
	$connection = connDB();

	//Get New Cocktail ID (1 higher than the highest valued cocktail id);
	$result = mysqli_query($connection, 'SELECT MAX(cocktailID) FROM Cocktail');
	$result = mysqli_fetch_array($result);
	$cocktailID = $result[0]+1;

	$defaultRating = "0";
    $username = "TheCocktailDB";

	//Query Prepared Statement
	$query = $connection->prepare('INSERT INTO Cocktail VALUES (?, ?, ?, ?, ?, ?)');
	//Binds Username and Password Into Query Statement
	$query->bind_param("ssssss", $cocktailID, $request['strDrink'], $username, $request['strInstructions'], $request['strDrinkThumb'], $defaultRating);	
		
	//If query executes successfully, return success message
	if ($query->execute()) {
		echo "Cocktail Successfully Added Into Cocktail Table \n".PHP_EOL;
		logging("Cocktail Successfully Added Into Cocktail Table", __FILE__);
	}
	//If the last query failed and gave an error, return an error
	if ($connection->errno) {
		echo "Database Error: Failed To Insert Data Into Cocktail Table \n".PHP_EOL;
		loggingWarn("Database Error: Failed To Insert Data Into Cocktail Table", __FILE__);
		return false;
	}

	echo "Beginning To Create New Entry In Ingredient Table".PHP_EOL;
	logging("Beginning To Create New Entry In Ingredient Table", __FILE__);

	$numIngredients = (count($request)-3)/2;

	for($iter=1; $iter <= $numIngredients; $iter++){

		//Query Prepared Statement
		$query = $connection->prepare('SELECT * FROM Ingredient WHERE ingredientName = ? LIMIT 1');
		//Binds Username and Password Into Query Statement
		$query->bind_param("s", $request['strIngredient'.$iter]);	

		//Execute Query
		$query->execute();
		$result = $query->get_result();

		//stores all data into array i
		if(mysqli_num_rows($result)==0){

			//Query Prepared Statement
			$query = $connection->prepare('INSERT INTO Ingredient VALUES (?)');
			//Binds Username and Password Into Query Statement
			$query->bind_param("s", $request['strIngredient'.(string)$iter]);	
				
			//If query executes successfully, return success message
			if ($query->execute()) {
				echo "Ingredient Successfully Added Into Ingredient Table \n".PHP_EOL;
				logging("Ingredient Successfully Added Into Ingredient Table", __FILE__);
			}
			//If the last query failed and gave an error, return an error
			if ($connection->errno) {
				echo "Database Error: Failed To Insert Data Into Ingredient Table \n".PHP_EOL;
				loggingWarn("Database Error: Failed To Insert Data Into Ingredient Table", __FILE__);
				return false;
			}

		}else{

		echo "Ingredient Is Already In Ingredient Table \n".PHP_EOL;
		logging("Ingredient Is Already In Ingredient Table", __FILE__);
		}

	}

	echo "Beginning To Create New Entry In Recipe Table".PHP_EOL;
	logging("Beginning To Create New Entry In Recipe Table", __FILE__);

	for($iter=1; $iter <= $numIngredients; $iter++){
		//Query Prepared Statement
		$query = $connection->prepare('SELECT * FROM Recipe WHERE cocktailID = ? AND ingredientName = ?');
		//Binds Username and Password Into Query Statement
		$query->bind_param("ss", $cocktailID, $request['strIngredient'.$iter]);	
		
		//Execute Query
		$query->execute();
		$result = $query->get_result();
		
		//stores all data into array i
		if(mysqli_num_rows($result)==0){
			//Query Prepared Statement
			$query = $connection->prepare('INSERT INTO Recipe VALUES (?, ?, ?)');
			//Binds Username and Password Into Query Statement
			$query->bind_param("sss", $cocktailID, $request['strIngredient'.(string)$iter], $request['strMeasure'.(string)$iter]);	
				
			//If query executes successfully, return success message
			if ($query->execute()) {
				echo "Recipe Successfully Added Into Recipe Table \n".PHP_EOL;
				logging("Recipe Successfully Added Into Recipe Table", __FILE__);
			}
			//If the last query failed and gave an error, return an error
			if ($connection->errno) {
				echo "Database Error: Failed To Insert Data Into Recipe Table \n".PHP_EOL;
				loggingWarn("Database Error: Failed To Insert Data Into Recipe Table", __FILE__);
				return false;
			}

		}

	}

	echo "All Data Has Successfully Been Inserted. \n".PHP_EOL;
	logging("All Data Has Successfully Been Inserted.", __FILE__);
	return true;

}

?>