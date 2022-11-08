<?php require_once(__DIR__ . '/partials/header.php'); ?>
<body>
<?php require_once(__DIR__ . '/partials/navbar.php'); ?>
<style>
    .card-group{
        display: flex;
        flex-wrap: wrap;        
    }
    .card-group>.card {
        flex: 1 0 30%;
        margin-bottom: 0;
        width:30%;
    }
    a {
        color: inherit; 
        text-decoration: inherit;
    }
    img{
        max-height:400px;
        max-width:400px;
        object-fit: cover;
    }
    .card{
        min-height: 300px;
        min-width: 300px;
        margin-right: 5px; 
        max-height:600px;
        max-width:400px;    
    }
</style>
<?php
if (isset($_GET["s"])) {
    $s = $_GET["s"];
}


$request = array();
$request['type'] = "fetchCocktail";
$request['searchString'] = $s;
$response = rabbitSearchClient($request);
/*
        $response['cocktailID'.(string)$counter]
		$response['cocktailName'.(string)$counter]
		$response['publisher'.(string)$counter]
		$response['instructions'.(string)$counter]
		$response['imageRef'.(string)$counter]
		$response['rating'.(string)$counter]
		$counter++;*/
?>


<div class="container p-5 card-group">
    <?php for($counter=1;$counter<=$response['numResults'];$counter++): ?>
    <a href="drinks.php?id=<?php echo($response['cocktailID'.(string)$counter]);?>">
    <div class="card m-2">
        <img class="card-img-top" src="<?php echo($response['imageRef'.(string)$counter]);?>" alt="Card image">
        <div class="card-body">
            <h4 class="Long vodka"><?php echo($response['cocktailName'.(string)$counter]);?></h4>
            <div class="card-text">
                <style>
                    .checked {
                        color: orange;
                    }
                    .fa{
                        font-size: 150%;
                    }
                </style>
                <?php for($x=1;$x<=5;$x++):?>
                    <?php if($x<=$response['rating'.(string)$counter]):?>
                        <span class="fa fa-star checked"></span>
                    <?php else:?>
                        <span class="fa fa-star"></span>
                    <?php endif;?>
                <?php endfor;?>
            </div>
            <a href="drinks.php?id=<?php echo($response['cocktailID'.(string)$counter]);?>" class="btn btn-primary">View Recipe</a>
        </div>
    </div>
    </a>
    <?php endfor; ?>
</div>
</body>
<?php require_once(__DIR__ . '/partials/footer.php'); ?>