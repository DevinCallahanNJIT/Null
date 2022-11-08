<?php require_once(__DIR__ . '/partials/header.php'); ?>
<body>
<?php require_once(__DIR__ . '/partials/navbar.php'); ?>

<div class="container p-5">

<div>
    <h1>Home</h1>

<?php if (isSessionValid()):?>
    <?php 
    echo "<h3>Welcome " . getUsername() . "</h3>";
        

    $request = array();
    $request['type'] = "fetchRecommendation";
    $request['username'] = getUsername();
    $response = rabbitSearchClient($request);
    //echo(var_dump($response));
    ?>  


    <style>
    a {
        color: inherit; 
        text-decoration: inherit;
    }
    .card{
        min-height: 300px;
        min-width: 300px;
        margin-right: 5px; 
        max-height:600px;
        max-width:400px;    
    }
    img{
        object-fit: cover;
        max-height:400px;
        max-width:400px;
    }

    </style>
<div class="container-fluid">
    <hr>
    <?php foreach ($response as $key => $value):?>
        

    <p class="h3">Drinks based off of <?php echo($key); ?></p>
    <?php //echo(var_dump($value)); ?>
    <div class="card-group d-flex flex-row flex-nowrap overflow-auto">
        <?php for($counter=1;$counter<=count($value);$counter++): ?>
        <a href="drinks.php?id=<?php echo($value[$counter]['cocktailID']);?>">
        <div class="card m-2 ">
            <img class="card-img-top" src="<?php echo($value[$counter]['imageRef']); ?>" alt="Card image">
            <div class="card-body">
                <h4 class="Long vodka"><?php echo($value[$counter]['cocktailName']);?></h4>
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
                        <?php if($x<=$value['rating'.(string)$counter]):?>
                            <span class="fa fa-star checked"></span>
                        <?php else:?>
                            <span class="fa fa-star"></span>
                        <?php endif;?>
                    <?php endfor;?>
                    <!--<p><?php/* if($response['numResults']>=2){
                        echo((string) $response['numResults'] . " reviews");
                    } elseif($response['numResults']==1) {
                        echo((string) $response['numResults'] . " review");
                    } else{
                        echo("No reviews");
                    }*/?></p>-->
                </div>
            </div>
        </div>
        </a>
        <?php endfor; ?>
    </div>

    <hr>
    <?php endforeach;?>
</div>
<?php else:?>
    <?php echo "<h3>Welcome Guest</h3>";?>
<?php endif;?>
</div>

</div>
</body>
<?php require_once(__DIR__ . '/partials/footer.php'); ?>