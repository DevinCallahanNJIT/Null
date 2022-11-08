<?php
require_once('../lib/path.inc');
require_once('../lib/get_host_info.inc');
require_once('../lib/rabbitMQLib.inc');
require_once('./connectDatabase.php');
require_once('./logging.php');


//returns true/false if ingredientName is in database.
function fetchIngredient($ingredientName){
	echo "Ingredient Search Began".PHP_EOL;
	logging("Ingredient Search Began", __FILE__);


	// Create Connection
	$connection = connDB();
		
	//Query Prepared Statement
	$query = $connection->prepare('SELECT * FROM Ingredient WHERE ingredientName = ?');
	//Binds Paramaters Into Query
	$query->bind_param("s", $ingredientName);	

	//Execute Query
	$query->execute();
	$result = $query->get_result();

	//array that will store response data
    $response = array();
	
	//stores data into response array
	if ($result->num_rows > 0){
		echo "Search Finished. Ingredient Name Is Present In Database.".PHP_EOL;
		logging("Search Finished. Ingredient Name Is Present In Database.", __FILE__);
		return true;
	}
	echo "Search Finished. Ingredient Name Is Not Present In Database.".PHP_EOL;
	logging("Search Finished. Ingredient Name Is Not Present In Database.", __FILE__);
    return false;
	
}

//returns data based on cocktailID
function fetchCocktail($searchString){

	echo "Cocktail Search Began".PHP_EOL;
	logging("Cocktail Search Began", __FILE__);

	// Create Connection
	$connection = connDB();
	
	//append wildcard characters to either side of search string
	$searchString = "%".$searchString."%";


	/*
		
		INSIDE OF FUNCTION:
		-CHECK NEW DB FOR SEARCH STRING
			-IF FOUND
				-CHECK IF TTL IS VALID
					-IF TTL IS VALID
						-SKIP TO PULLING LOCAL DATA
					-IF	TTL IS NOT VALID
						-MODIFY DB ENTRY FOR NEW TTL
						-PROCEED TO PULL API DATA
						-CREATE NEW ARRAY WITH PULLED API DATA, PROPERLY FORMATTED
						-PASS EACH DRINK INTO CREATE RECIPE
						-DELAY BETWEEN DB UPDATE
						-MOVE ONTO PULLING LOCAL DATA
			-IF NOT FOUND
				-CREATE NEW DB ENTRY FOR SEARCH STRING + TTL
				-PROCEED TO PULL API DATA
				-CREATE NEW ARRAY WITH PULLED API DATA, PROPERLY FORMATTED
				-PASS EACH DRINK INTO CREATE RECIPE
				-DELAY BETWEEN DB UPDATE
				-MOVE ONTO PULLING LOCAL DATA


	*/

//CHECK NEW DB FOR SEARCH STRING
	$dbservername = "localhost";
	$dbusername = "local";
	$dbpassword = "ChangeLater";
	$database = "Miscellaneous";
	
	// Create Connection
	$connection2 = new mysqli($dbservername, $dbusername, $dbpassword, $database);

    if($connection2->connect_error) {
		die("Connection failed: " . $connection->connect_error);
	}

	//Query Prepared Statement
	$query = $connection2->prepare('SELECT TTL FROM SearchTerms WHERE searchTerm = ?');
	//Binds Paramaters Into Query
	$query->bind_param("s", $searchString);	

	//Execute Query
	$query->execute();
	$result = $query->get_result();

//IF FOUND
	if(mysqli_num_rows($result)>0){
//CHECK IF TTL IS VALID
		$TTLDate;
		$currentDate;
		while ($row = $result->fetch_row()){
			$resultData = (array)$row;
			$TTLDate = strtotime($resultData[0]);
			$currentDate = time();
		}
//IF TTL IS VALID
		if($TTLDate >= $currentDate){
//SKIP TO PULLING LOCAL DATA

//IF TTL IS NOT VALID
		}else{
//PROCEED TO PULL API DATA

			$newTTL = date('Y-m-d H:i:s', time() + 86400);

			//Query Prepared Statement
			$query = $connection2->prepare('UPDATE SearchTerms SET TTL = ? WHERE searchTerm = ?');
			//Binds Username and Password Into Query Statement
			$query->bind_param("ss", $searchString, $newTTL);	
				
			//If query executes successfully, return success message
			if ($query->execute()) {
				echo "Successfully Modified TTL In SearchTerms Table \n".PHP_EOL;
				logging("Successfully Modified TTL In SearchTerms Table", __FILE__);
			}
			//If the last query failed and gave an error, return an error
			if ($connection2->errno) {
				echo "Failed To Modify TTL In SearchTerms Table \n".PHP_EOL;
				loggingWarn("Failed To Modify TTL In SearchTerms Table", __FILE__);
				return false;
			}

			echo "Sending Search Request To APISearch Queue\n".PHP_EOL;
			logging("Sending Search Request To APISearch Queue", __FILE__);
			$client = new rabbitMQClient("/home/ubuntu/Null/lib/RabbitMQ.ini","APISearch");
			$request = $searchString;
			$client->publish($request);
			
			//delay time for entries to be added.
			sleep(5);
		}


//IF NOT FOUND
	}else{
		$newTTL = date('Y-m-d H:i:s', time() + 86400);

		//Query Prepared Statement
		$query = $connection2->prepare('INSERT INTO SearchTerms VALUES(?, ?)');
		//Binds Username and Password Into Query Statement
		$query->bind_param("ss", $searchString, $newTTL);	
			
		//If query executes successfully, return success message
		if ($query->execute()) {
			echo "Successfully Created New Entry SearchTerms Table \n".PHP_EOL;
			logging("Successfully Created New Entry SearchTerms Table", __FILE__);
		}
		//If the last query failed and gave an error, return an error
		if ($connection2->errno) {
			echo "Failed To Create New Entry SearchTerms Table\n".PHP_EOL;
			loggingWarn("Failed To Create New Entry SearchTerms Table", __FILE__);
			return false;
		}

		echo "Sending Search Request To APISearch Queue\n".PHP_EOL;
		logging("Sending Search Request To APISearch Queue", __FILE__);
		$client = new rabbitMQClient("/home/ubuntu/Null/lib/RabbitMQ.ini","APISearch");
		$request = $searchString;
		$client->publish($request);
		
		//delay time for entries to be added.
		sleep(5);

	}

//PULLING LOCAL DATA
	//Query Prepared Statement
	$query = $connection->prepare('SELECT * FROM Cocktail WHERE cocktailName LIKE ?');
	//Binds Paramaters Into Query
	$query->bind_param("s", $searchString);	

	//Execute Query
	$query->execute();
	$result = $query->get_result();

	//stores all data into array
	$counter = 1;
    $response = array();
    $response['numResults'] = $result->num_rows;
	while ($row = $result->fetch_row()){
		$resultData = (array)$row;

		$response['cocktailID'.(string)$counter] = $resultData[0];
		$response['cocktailName'.(string)$counter] = $resultData[1];
		$response['publisher'.(string)$counter] = $resultData[2];
		$response['instructions'.(string)$counter] = $resultData[3];
		$response['imageRef'.(string)$counter] = $resultData[4];
		$response['rating'.(string)$counter] = $resultData[5];
		$counter++;
	}
    
	//returns data in array as response
	echo "Search Finished. Returning ".$response['numResults']." result(s).".PHP_EOL;
	logging("Search Finished. Returning ".$response['numResults']." result(s).", __FILE__);
    return $response;

}

//returns all recipe data based on cocktailID
function fetchRecipe($cocktailID){

	echo "Recipe Search By Cocktail ID Began".PHP_EOL;
	logging("Recipe Search By Cocktail ID Began", __FILE__);

	// Create Connection
	$connection = connDB();
	
	//Query Prepared Statement
	$query = $connection->prepare('SELECT * FROM Cocktail WHERE cocktailID = ? LIMIT 1');
	//Binds Username and Password Into Query Statement
	$query->bind_param("s", $cocktailID);	
	
	//Execute Query
	$query->execute();
	$result = $query->get_result();
	
	//stores all data into array
	if(mysqli_num_rows($result)>0){
			
		$counter = 1;
		$response = array();
		while ($row = $result->fetch_row()){
			$resultData = (array)$row;
			
			$response['cocktailID'] = $resultData[0];
			$response['cocktailName'] = $resultData[1];
			$response['publisher'] = $resultData[2];
			$response['instructions'] = $resultData[3];
			$response['imageRef'] = $resultData[4];
			$response['rating'] = $resultData[5];
		}
	
		///Query Prepared Statement
		$query = $connection->prepare('SELECT * FROM Recipe WHERE cocktailID = ?');
		//Binds Username and Password Into Query Statement
		$query->bind_param("s", $cocktailID);	
	
		//Execute Query
		$query->execute();
		$result = $query->get_result();
	
		$counter = 1;
		while ($row = $result->fetch_row()){
			$resultData = (array)$row;
			
			$response['ingredient'.(string)$counter] = $resultData[1];
			$response['measurement'.(string)$counter] = $resultData[2];
			$counter++;
			
		}
	
		echo "Search Finished. Returning Recipe Data.".PHP_EOL;
		logging("Search Finished. Returning Recipe Data.", __FILE__);
    	return $response;
	}
		echo "Invalid Cocktail ID.".PHP_EOL;
		loggingWarn("Invalid Cocktail ID.", __FILE__);
    	return "";

}

//fetches recipes from Recipe table on ingredient
//returns array of arrays
function searchRecipe($ingredientName){

	echo "Recipe Search By Ingredient Began".PHP_EOL;
	logging("Recipe Search By Ingredient Began", __FILE__);

	// Create Connection
	$connection = connDB();
	
	//Query Prepared Statement
	$query = $connection->prepare('SELECT * FROM Ingredient WHERE ingredientName = ?');
	//Binds Paramaters Into Query
	$query->bind_param("s", $ingredientName);	

	//Execute Query
	$query->execute();
	$result = $query->get_result();

	//stores all data into array
	if(mysqli_num_rows($result)>0){

		//Query Prepared Statement
		$query = $connection->prepare('SELECT cocktailID FROM Recipe WHERE ingredientName = ?');
		//Binds Paramaters Into Query
		$query->bind_param("s", $ingredientName);	

		//Execute Query
		$query->execute();
		$result = $query->get_result();

		//stores all data into array
		$counter = 1;
		$cocktailArray = array();
		while ($row = $result->fetch_row()){
			$resultData = (array)$row;

			$cocktailArray[$counter] = $resultData[0];
			$counter++;
		}

		$response = array();
		$counter = 1;
		foreach ($cocktailArray as $cocktailID){
			$response[$counter] = array();
			$response[$counter] = fetchRecipe($cocktailID);
			$counter++;
		}

		
		//returns data in array as response
		echo "Search Finished, Returning Results".PHP_EOL;
		logging("Search Finished, Returning Results", __FILE__);
		return $response;

	}

	echo "Ingredient Sent Is Not A Valid Ingredient".PHP_EOL;
	loggingWarn("Ingredient Sent Is Not A Valid Ingredient", __FILE__);
	return array();

}

//inserts recipe data into corresponding tables in the database; Cocktail -> Ingredient -> Recipe
//returns boolean
function createRecipe($request){

	echo "Beginning To Create New Recipe".PHP_EOL;
	logging("Beginning To Create New Recipe", __FILE__);

	//Create Connection
	$connection = connDB();

	//Get New Cocktail ID (1 higher than the highest valued cocktail id);
	$result = mysqli_query($connection, 'SELECT MAX(cocktailID) FROM Cocktail');
	$result = mysqli_fetch_array($result);
	$cocktailID = $result[0]+1;

	$defaultRating = "0";

	//Query Prepared Statement
	$query = $connection->prepare('INSERT INTO Cocktail VALUES (?, ?, ?, ?, ?, ?)');
	//Binds Username and Password Into Query Statement
	$query->bind_param("ssssss", $cocktailID, $request['cocktailName'], $request['username'], $request['instructions'], $request['imageRef'], $defaultRating);	
		
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

	$numIngredients = (count($request)-5)/2;

	for($iter=1; $iter <= $numIngredients; $iter++){

		//Query Prepared Statement
		$query = $connection->prepare('SELECT * FROM Ingredient WHERE ingredientName = ? LIMIT 1');
		//Binds Username and Password Into Query Statement
		$query->bind_param("s", $request['ingredient'.$iter]);	

		//Execute Query
		$query->execute();
		$result = $query->get_result();

		//stores all data into array i
		if(mysqli_num_rows($result)==0){

			//Query Prepared Statement
			$query = $connection->prepare('INSERT INTO Ingredient VALUES (?)');
			//Binds Username and Password Into Query Statement
			$query->bind_param("s", $request['ingredient'.(string)$iter]);	
				
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
		$query = $connection->prepare('INSERT INTO Recipe VALUES (?, ?, ?)');
		//Binds Username and Password Into Query Statement
		$query->bind_param("sss", $cocktailID, $request['ingredient'.(string)$iter], $request['measurement'.(string)$iter]);	
			
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

	echo "All Data Has Successfully Been Inserted. \n".PHP_EOL;
	logging("All Data Has Successfully Been Inserted.", __FILE__);
	return true;

}

//inserts recipe review data into CocktailReview table
//returns boolean
function createReview($cocktailID, $username, $rating, $title, $description){

	echo "Beginning To Create New Entry In CocktailReview Table".PHP_EOL;
	logging("Beginning To Create New Entry In CocktailReview Table", __FILE__);

	// Create Connection
	$connection = connDB();

	//Query Prepared Statement
	$query = $connection->prepare('SELECT * FROM CocktailReview WHERE cocktailID = ? AND publisher = ?');
	//Binds Username and Password Into Query Statement
	$query->bind_param("ss", $cocktailID, $username);	

	//Execute Query
	$query->execute();
	$result = $query->get_result();

	//stores all data into array
	if(mysqli_num_rows($result)<=0){

		//get current date and time
		$datetime = date('Y-m-d H:i:s', time());

		//Query Prepared Statement
		$query = $connection->prepare('INSERT INTO CocktailReview VALUES (?, ?, ?, ?, ?, ?)');
		//Binds Username and Password Into Query Statement
		$query->bind_param("ssssss", $cocktailID, $username, $rating, $datetime, $title, $description);	
			
		//If query executes successfully, return success message
		if ($query->execute()) {
			echo "Review Successfully Added Into CocktailReview Table \n".PHP_EOL;
			logging("Review Successfully Added Into CocktailReview Table", __FILE__);

			echo "Beginning To Change Recipe Rating".PHP_EOL;
			logging("Beginning To Change Recipe Rating", __FILE__);
		
			//Query Prepared Statement
			$query = $connection->prepare('SELECT rating FROM CocktailReview WHERE cocktailID = ?');
			//Binds Username and Password Into Query Statement
			$query->bind_param("s", $cocktailID);	
			
			//Execute Query
			$query->execute();
			$result = $query->get_result();
		
			//stores all data into array
			if(mysqli_num_rows($result)>0){
		
		
				$total = 0;
				$counter = 0;
				while ($row = $result->fetch_row()){
					$resultData = (array)$row;
					
					$total = floatval($resultData[0]) + $total;
					$counter++;
					
				}
		
				$averageRating = $total/floatval($counter);
		
				//Query Prepared Statement
				$query = $connection->prepare('UPDATE Cocktail SET rating = ? WHERE cocktailID = ?');
				//Binds Username and Password Into Query Statement
				$query->bind_param("ss", $averageRating, $cocktailID);
				
				//If query executes successfully, return success message
				if ($query->execute()) {
					echo "Review Score Successfully Modified \n".PHP_EOL;
					logging("Review Score Successfully Modified", __FILE__);
					return true;
				}
				//If the last query failed and gave an error, return an error
				if ($connection->errno) {
					echo "Database Error: Failed To Modify Review Score \n".PHP_EOL;
					loggingWarn("Database Error: Failed To Modify Review Score", __FILE__);
					return false;
				}
		
			}
			return true;
		}
		//If the last query failed and gave an error, return an error
		if ($connection->errno) {
			echo "Database Error: Failed To Insert Data Into CocktailReview Table \n".PHP_EOL;
			loggingWarn("Database Error: Failed To Insert Data Into CocktailReview Table", __FILE__);
			return false;
		}

	}

	echo "Failed To Insert Data: User has already made a review.".PHP_EOL;
	loggingwarn("Failed To Insert Data: User has already made a review.", __FILE__);
	return false;

}

//fetchs recipe review data from CocktailReview table based on cocktailID
//returns array of data
function fetchReviews($cocktailID){

	echo "Review Search Began".PHP_EOL;
	logging("Review Search Began", __FILE__);

	// Create Connection
	$connection = connDB();
	
	//Query Prepared Statement
	$query = $connection->prepare('SELECT * FROM CocktailReview WHERE cocktailID = ?');
	//Binds Paramaters Into Query
	$query->bind_param("s", $cocktailID);	

	//Execute Query
	$query->execute();
	$result = $query->get_result();

	//stores all data into array
	$counter = 1;
    $response = array();
    $response['numResults'] = $result->num_rows;
	while ($row = $result->fetch_row()){
		$resultData = (array)$row;

		$response['publisher'.(string)$counter] = $resultData[1];
		$response['rating'.(string)$counter] = $resultData[2];
		$response['date'.(string)$counter] = $resultData[3];
		$response['title'.(string)$counter] = $resultData[4];
		$response['description'.(string)$counter] = $resultData[5];
		$counter++;
	}
    
	//returns data in array as response
	echo "Search Finished. Returning ".$response['numResults']." result(s).".PHP_EOL;
	logging("Search Finished. Returning ".$response['numResults']." result(s).", __FILE__);
    return $response;
	
}

//inserts username and cocktail into LikedCocktail table
//returns boolean
function createLikedCocktail($username, $cocktailID){

	echo "Beginning To Create New Entry In LikedCocktails Table".PHP_EOL;
	logging("Beginning To Create New Entry In LikedCocktails Table", __FILE__);

	// Create Connection
	$connection = connDB();


	//Query Prepared Statement
	$query = $connection->prepare('SELECT * FROM LikedCocktail WHERE username = ? AND cocktailID = ?');
	//Binds Username and Password Into Query Statement
	$query->bind_param("ss", $username, $cocktailID);	

	//Execute Query
	$query->execute();
	$result = $query->get_result();

	//stores all data into array
	if(mysqli_num_rows($result)<=0){

		//Query Prepared Statement
		$query = $connection->prepare('INSERT INTO LikedCocktail VALUES (?, ?)');
		//Binds Username and Password Into Query Statement
			$query->bind_param("ss", $username, $cocktailID);	
			
		//If query executes successfully, return success message
		if ($query->execute()) {
			echo "Link Successfully Added Into LikedCocktail Table \n".PHP_EOL;
			logging("Link Successfully Added Into LikedCocktail Table", __FILE__);
			return true;
		}
		//If the last query failed and gave an error, return an error
		if ($connection->errno) {
			echo "Database Error: Failed To Insert Data \n".PHP_EOL;
			loggingWarn("Database Error: Failed To Insert Data", __FILE__);
			return false;
		}

	}
	
}

//fetchs liked cocktails from LikedCocktail table based on username
//returns array of data
function fetchLikedCocktail($username){

	echo "Liked Cocktails Search Began".PHP_EOL;

	// Create Connection
	$connection = connDB();
	
	//Query Prepared Statement
	$query = $connection->prepare('SELECT * FROM LikedCocktail WHERE username = ?');
	//Binds Paramaters Into Query
	$query->bind_param("s", $username);	

	//Execute Query
	$query->execute();
	$result = $query->get_result();

	//stores all data into array
	$counter = 1;
    $response = array();
    $response['numResults'] = $result->num_rows;
	while ($row = $result->fetch_row()){
		$resultData = (array)$row;

		$response['cocktailID'.(string)$counter] = $resultData[1];
		$counter++;
	}
    
	//returns data in array as response
	echo "Search Finished. Returning ".$response['numResults']." result(s).".PHP_EOL;
    return $response;

}

//inserts username and ingredient into IngredientCabinet table
//returns boolean
function createIngredientCabinet($username, $ingredientName){

	echo "Beginning To Create New Entry In IngredientCabinet Table".PHP_EOL;
	logging("Beginning To Create New Entry In IngredientCabinet Table", __FILE__);

	// Create Connection
	$connection = connDB();
	
	//Query Prepared Statement
	$query = $connection->prepare('SELECT * FROM Ingredient WHERE ingredientName = ? LIMIT 1');
	//Binds Username and Password Into Query Statement
	$query->bind_param("s", $ingredientName);	
	
	//Execute Query
	$query->execute();
	$result = $query->get_result();
	$check1 = false;
	//stores all data into array i
	if(mysqli_num_rows($result)>0){
		$check1 = true;
	}else{
		echo "Ingredient Does Not Exist: Failed To Insert Data \n".PHP_EOL;
		loggingWarn("Ingredient Does Not Exist: Failed To Insert Data", __FILE__);
		return false;
	}

	//Query Prepared Statement
	$query = $connection->prepare('SELECT * FROM IngredientCabinet WHERE username = ? AND ingredientName = ? LIMIT 1');
	//Binds Username and Password Into Query Statement
	$query->bind_param("ss", $username, $ingredientName);	
	
	//Execute Query
	$query->execute();
	$result = $query->get_result();
	$check2 = false;;
	//stores all data into array i
	if(mysqli_num_rows($result)==0){
		$check2 = true;
	}else{
		echo "Ingredient Is Already In Ingredient Cabinet: Failed To Insert Data \n".PHP_EOL;
		loggingWarn("Ingredient Is Already In Ingredient Cabinet: Failed To Insert Data", __FILE__);
		return false;
	}


	if ($check1 && $check2){

		//Query Prepared Statement
		$query = $connection->prepare('INSERT INTO IngredientCabinet VALUES (?, ?)');
		//Binds Username and Password Into Query Statement
		$query->bind_param("ss", $username, $ingredientName);	
		
		//If query executes successfully, return success message
		if ($query->execute()) {
			echo "Ingredient Successfully Added Into IngredientCabinet Table \n".PHP_EOL;
			logging("Ingredient Successfully Added Into IngredientCabinet Table", __FILE__);
			return true;
		}
		//If the last query failed and gave an error, return an error
		if ($connection->errno) {
			echo "Database Error: Failed To Insert Data \n".PHP_EOL;
			loggingWarn("Database Error: Failed To Insert Data", __FILE__);
			return false;
		}

	}

}

//fetchs ingredients from IngredientCabinet table based on username
//returns array of data
function fetchIngredientCabinet($username){

	echo "Ingredient Cabinet Search Began".PHP_EOL;

	// Create Connection
	$connection = connDB();
	
	//Query Prepared Statement
	$query = $connection->prepare('SELECT * FROM IngredientCabinet WHERE username = ?');
	//Binds Paramaters Into Query
	$query->bind_param("s", $username);	

	//Execute Query
	$query->execute();
	$result = $query->get_result();

	//stores all data into array
	$counter = 1;
    $response = array();
    $response['numResults'] = $result->num_rows;
	while ($row = $result->fetch_row()){
		$resultData = (array)$row;

		$response['ingredientName'.(string)$counter] = $resultData[1];
		$counter++;
	}
    
	//returns data in array as response
	echo "Search Finished. Returning ".$response['numResults']." result(s).".PHP_EOL;
    return $response;

}

//inserts blog data into the Blog table
//returns boolean
function createBlog($username, $title, $textBody){

	echo "Beginning To Create New Entry In Blog Table".PHP_EOL;
	logging("Beginning To Create New Entry In Blog Table", __FILE__);

	// Create Connection
	$connection = connDB();

	//get current date and time
	$datetime = date('Y-m-d H:i:s', time());

	//Query Prepared Statement
	$query = $connection->prepare('INSERT INTO Blog VALUES (DEFAULT, ?, ?, ?, ?)');
	//Binds Username and Password Into Query Statement
	$query->bind_param("ssss", $username, $datetime, $title, $textBody);	
		
	//If query executes successfully, return success message
	if ($query->execute()) {
		echo "Review Successfully Added Into Blog Table \n".PHP_EOL;
		logging("Review Successfully Added Into Blog Table", __FILE__);
		return true;
	}
	//If the last query failed and gave an error, return an error
	if ($connection->errno) {
		echo "Database Error: Failed To Insert Data Into Blog Table \n".PHP_EOL;
		loggingWarn("Database Error: Failed To Insert Data Into Blog Table", __FILE__);
		return false;
	}
	
}

//fetchs blogs from Blog table based on username
//returns array of data
function fetchUserBlogs($username){

	echo "Blog Search By Username Began".PHP_EOL;
	logging("Blog Search By Username Began", __FILE__);

	// Create Connection
	$connection = connDB();
	
	//Query Prepared Statement
	$query = $connection->prepare('SELECT * FROM Blog WHERE publisher = ?');
	//Binds Paramaters Into Query
	$query->bind_param("s", $username);	

	//Execute Query
	$query->execute();
	$result = $query->get_result();

	//stores all data into array
	$counter = 1;
    $response = array();
    $response['numResults'] = $result->num_rows;
	while ($row = $result->fetch_row()){
		$resultData = (array)$row;

		$response['blogID'.(string)$counter] = $resultData[0];
		$response['publishDate'.(string)$counter] = $resultData[2];
		$response['title'.(string)$counter] = $resultData[3];
		$response['textBody'.(string)$counter] = $resultData[4];
		$counter++;
	}
    
	//returns data in array as response
	echo "Search Finished. Returning ".$response['numResults']." result(s).".PHP_EOL;
	logging("Search Finished. Returning ".$response['numResults']." result(s).", __FILE__);
    return $response;

}

//fetchs blogs from Blog table based on a string
//returns array of data
function searchBlogTitle($searchString){

	echo "Blog Search By Title Began".PHP_EOL;
	logging("Blog Search By Title Began", __FILE__);

	// Create Connection
	$connection = connDB();
	
	//append wildcard characters to either side of search string
	$searchString = "%".$searchString."%";

	//Query Prepared Statement
	$query = $connection->prepare('SELECT * FROM Blog WHERE title LIKE ?');
	//Binds Paramaters Into Query
	$query->bind_param("s", $searchString);	

	//Execute Query
	$query->execute();
	$result = $query->get_result();

	//stores all data into array
	$counter = 1;
    $response = array();
    $response['numResults'] = $result->num_rows;
	while ($row = $result->fetch_row()){
		$resultData = (array)$row;

		$response['blogID'.(string)$counter] = $resultData[0];
		$response['publisher'.(string)$counter] = $resultData[1];
		$response['publishDate'.(string)$counter] = $resultData[2];
		$response['title'.(string)$counter] = $resultData[3];
		$response['textBody'.(string)$counter] = $resultData[4];
		$counter++;
	}
    
	//returns data in array as response
	echo "Search Finished. Returning ".$response['numResults']." result(s).".PHP_EOL;
	logging("Search Finished. Returning ".$response['numResults']." result(s).", __FILE__);
    return $response;

}

//fetchs a blog from Blog table based on a Blog ID
//returns array of data
function fetchBlog($blogID){

	echo "Blog Search By BlogID Began".PHP_EOL;

	// Create Connection
	$connection = connDB();
	
	//Query Prepared Statement
	$query = $connection->prepare('SELECT * FROM Blog WHERE blogID = ? LIMIT 1');
	//Binds Username and Password Into Query Statement
	$query->bind_param("s", $blogID);	
	
	//Execute Query
	$query->execute();
	$result = $query->get_result();
	
	//stores all data into array
	if(mysqli_num_rows($result)>0){
			
		$counter = 1;
		$response = array();
		while ($row = $result->fetch_row()){
			$resultData = (array)$row;
			
			$response['blogID'] = $resultData[0];
			$response['publisher'] = $resultData[1];
			$response['publishDate'] = $resultData[2];
			$response['title'] = $resultData[3];
			$response['textBody'] = $resultData[4];
		}
	
		echo "Search Finished. Returning Blog.".PHP_EOL;
    	return $response;
	}
		echo "Invalid Blog ID.".PHP_EOL;
    	return "";

}

//creates a link between a recipe and a blog and inserts it into RecipeBlog table
//returns boolean
function createRecipeBlog($blogID, $cocktailID){

	echo "Beginning To Create New Entry In RecipeBlog Table".PHP_EOL;
	logging("Beginning To Create New Entry In RecipeBlog Table", __FILE__);

	// Create Connection
	$connection = connDB();

	//Query Prepared Statement
	$query = $connection->prepare('SELECT * FROM RecipeBlog WHERE blogID = ? LIMIT 1');
	//Binds Username and Password Into Query Statement
	$query->bind_param("s", $blogID);	
	
	//Execute Query
	$query->execute();
	$result = $query->get_result();
	
	//stores all data into array
	if(mysqli_num_rows($result)<=0){

		//Query Prepared Statement
		$query = $connection->prepare('INSERT INTO RecipeBlog VALUES (?, ?)');
		//Binds Username and Password Into Query Statement
		$query->bind_param("ss", $blogID, $cocktailID);	
			
		//If query executes successfully, return success message
		if ($query->execute()) {
			echo "Review Successfully Added Into RecipeBlog Table \n".PHP_EOL;
			logging("Review Successfully Added Into RecipeBlog Table", __FILE__);
			return true;
		}
		//If the last query failed and gave an error, return an error
		if ($connection->errno) {
			echo "Database Error: Failed To Insert Data Into RecipeBlog Table \n".PHP_EOL;
			loggingWarn("Database Error: Failed To Insert Data Into RecipeBlog Table", __FILE__);
			return false;
		}

	}

	echo "Error: Blog is already linked to a recipe! \n".PHP_EOL;
	loggingWarn("Error: Blog is already linked to a recipe!", __FILE__);
	return false;

}

//fetchs a cocktailID from RecipeBlog based on blogID
//returns array of data
function fetchRecipeBlog($blogID){

	echo "Recipe-Blog Search Began".PHP_EOL;
	logging("Recipe-Blog Search Began", __FILE__);

	// Create Connection
	$connection = connDB();
	
	//Query Prepared Statement
	$query = $connection->prepare('SELECT * FROM RecipeBlog WHERE blogID = ? LIMIT 1');
	//Binds Username and Password Into Query Statement
	$query->bind_param("s", $blogID);	
	
	//Execute Query
	$query->execute();
	$result = $query->get_result();
	
	//stores all data into array
	if(mysqli_num_rows($result)>0){
			
		$counter = 1;
		$response = "No Link";
		while ($row = $result->fetch_row()){
			$resultData = (array)$row;
			
			$response = (string)$resultData[1];
			
		}
	
		echo "Search Finished. Returning Recipe.".PHP_EOL;
		logging("Search Finished. Returning Recipe.", __FILE__);
    	return $response;
	}
		echo "No recipe linked to blog.".PHP_EOL;
		logging("No recipe linked to blog.", __FILE__);
    	return "No Link";
}

//combines function of fetchRecipe and fetchArray
//returns array of arrays
function fetchRecipeAndReview($cocktailID){

	//Recipe
	$recipeArray = array();
	$recipeArray = fetchRecipe($cocktailID);
	//Review
	$reviewArray = array();
	$reviewArray = fetchReviews($cocktailID);

	$response = array();
	$response['recipe'] = $recipeArray;
	$response['review'] = $reviewArray;

	return $response;

}

//combines function of fetchRecipe and fetchBlog
//returns array of arrays
function fetchRecipeAndBlog($blogID){

	$blogArray = array();
	$blogArray = fetchBlog($blogID);

	$cocktailID = fetchRecipeBlog($blogID);

	$recipeArray = array();

	if ($cocktailID != "No Link"){
		$recipeArray = fetchRecipe($cocktailID);
	}

	$response = array();
	$response['blog'] = $blogArray;
	$response['recipe'] = $recipeArray;

	return $response;

}

//combines function of fetchRecipeAndReview and createReview
//returns array of arrays
function fetchRecipeAndReviewANDcreateReview($cocktailID, $username, $rating, $title, $description){

	$ghettoData = fetchRecipeAndReview($cocktailID);
	
	$response = array();

	$response['recipe'] = $ghettoData['recipe'];
	$response['review'] = $ghettoData['review'];
	$response['created'] = createReview($cocktailID, $username, $rating, $title, $description);

	return $response;

}

//combines function of fetchIngredientCabinet and createIngredientCabinet
//returns array of arrays
function fetchAndCreateIngredientCabinet($username, $ingredientName){
	
	$response = array();

	$response['ingredientCabinet'] = fetchIngredientCabinet($username);
	$response['created'] = createIngredientCabinet($username, $ingredientName);

	return $response;

}

//fetch 3 random recipes that use ingredients in a user's ingredient cabinet
//RANDOMNESS IS NOT IMPLEMENTED YET, AND THIS METHOD RETURNS THE FIRST 3 RECOMMENDATIONS
//return an array of arrays
function fetchRecommendation($username){

	echo "Recommendation Search Began".PHP_EOL;
	logging("Recommendation Search Began", __FILE__);

	$response = array();
	$tempResponse = array();

	$fetchIngredients = fetchIngredientCabinet($username);

		for($counter = 1; $counter < count($fetchIngredients); $counter++){
			$tempResponse[$fetchIngredients['ingredientName'.(string)$counter]] = searchRecipe($fetchIngredients['ingredientName'.(string)$counter]);
		}
		
		if (count($tempResponse)<=3){

			return $tempResponse;

		}

		$keys = array_rand($tempResponse, 3);

		for($i=0; $i<3; $i++){
			$response[$keys[$i]] = $tempResponse[$keys[$i]];
		}

		echo "Recommendation Search Finished".PHP_EOL;
		logging("Recommendation Search Finished", __FILE__);
		return $response;
	
	return array();

}

function requestProcessor($request)
{
	echo "\n[Received Request]".PHP_EOL;

	if(!isset($request['type']))
	{
		return "ERROR: unsupported message type";
	}
	switch ($request['type'])
	{
		case "isIngredient":
			return fetchIngredient($request['ingredientName']);

		case "fetchCocktail":
			return fetchCocktail($request['searchString']);

		case "fetchRecipe":
			return fetchRecipe($request['cocktailID']);

		case "createReview":
			return createReview($request['cocktailID'], $request['username'], $request['rating'],$request['title'], $request['description']);

		case "fetchReview":
			return fetchReviews($request['cocktailID']);

		case "fetchRecipeAndReview":
			return fetchRecipeAndReview($request['cocktailID']);

		/*
			- Create Recipe -
			Expected Input:
			Array(
				[type]=>createRecipe,
				[cocktailName]=>value,
				[username]=>value,
				[instructions]=>value,
				[imageRef]=>value,
				[ingredient1]=>value,
				[measurement1]=>value,
				...
			)
		*/
		case "createRecipe":
			return createRecipe($request);

		case "createLike":
			return createLikedCocktail($request['username'], $request['cocktailID']);

		case 'fetchLike':
			return fetchLikedCocktail($request['username']);

		case 'createIngredientCabinet':
			return createIngredientCabinet($request['username'], $request['ingredientName']);

		case 'fetchIngredientCabinet':
			return fetchIngredientCabinet($request['username']);

		case 'createBlog':
			return createBlog($request['username'], $request['title'], $request['textBody']);

		case 'fetchUserBlogs':
			return fetchUserBlogs($request['username']);
		
		case 'searchBlogTitle':
			return searchBlogTitle($request['searchString']);

		case 'fetchBlog':
			return fetchBlog($request['blogID']);
					
		case 'createRecipeBlog':
			return createRecipeBlog($request['blogID'], $request['cocktailID']);

		//don't use this one ↓
		case 'fetchRecipeBlog':
			return fetchRecipeBlog($request['blogID']);

		case 'fetchRecipeAndBlog':
			return fetchRecipeAndBlog($request['blogID']);

		case 'fetchRecipeAndReviewANDcreateReview':
			return fetchRecipeAndReviewANDcreateReview($request['cocktailID'], $request['username'], $request['rating'],$request['title'], $request['description']);

		case 'searchRecipe':
			return searchRecipe($request['ingredientName']);

		case 'fetchAndCreateIngredientCabinet':
			return fetchAndCreateIngredientCabinet($request['username'], $request['ingredientName']);

		case 'fetchRecommendation':
			return fetchRecommendation($request['username']);

	}
	return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("../lib/rabbitMQ.ini","DatabaseSearch");

echo "Search Server BEGIN \n".PHP_EOL;
logging("Search Server BEGIN", __FILE__);
$server->process_requests('requestProcessor');
echo "Search Server END \n".PHP_EOL;
logging("Search Server", __FILE__);
exit();
?>