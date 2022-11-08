<?php require_once(__DIR__ . '/partials/header.php'); ?>
<body>

<?php require_once(__DIR__ . '/partials/navbar.php'); ?>

<?php
$id=0;
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
if(isset($_POST['submit']))	{
    //echo("SUBMIT!!");
    //echo($_POST['rating1']);
    $review = array();
    $review['type'] = "fetchRecipeAndReviewANDcreateReview";
    $review['cocktailID'] = $id;
    $review['username'] = getUsername();
    if(isset($_POST['rating'])){
        $review['rating'] = $_POST['rating'];
        //echo($_POST['rating']);
    } else {
        $review['rating'] = "1";
    }
    if(isset($_POST['title'])){
        $review['title'] = $_POST['title'];
    }
    if(isset($_POST['written'])){
        $review['description'] = $_POST['written'];
    }
    $response = rabbitSearchClient($review);
    //echo(var_dump($response));
} else {
    $request = array();
    $request['type'] = "fetchRecipeAndReview";
    $request['cocktailID'] = $id;
    
    $response = rabbitSearchClient($request);
}
foreach ($_POST['name'] as $key => $value)
{
   //echo $key; // $key is the id you want
   //echo(addToCart($key));
   addToCart($key);
}
$instructions = (count($response['recipe'])-5)/2;
?>



<div class="container p-5">
    <div class="row">
        <div class="col-md-4"><img src=<?php echo($response['recipe']['imageRef']);?> class="rounded img-fluid" alt="<?php echo($response['recipe']['cocktailName']);?>"></div>
        <div class="col-md-8">
            <div class="d-flex">
                <p class="h1 me-auto"><?php echo($response['recipe']['cocktailName']);?></p>
                <a href="#" class="d-flex pt-3">
                    <i class="bi bi-plus-square" style="scale:200%"></i>
                </a>
                
            </div>
            <hr>
            <p>Submitted by <?php echo($response['recipe']['publisher']);?></p>
            <p class="h3">Instructions</p>
            <!--<p class="h5">Glass - Highball glass</p>-->
            <p class="h6"><?php echo($response['recipe']['instructions']);?></p>
            <p class="h4">Ingredients</p>
            <form action="" method="post">
                <?php for($counter=1;$counter<=$instructions;$counter++):?>
                <p class="h6"> - <?php echo($response['recipe']['measurement'.(string)$counter]);?> of <?php echo($response['recipe']['ingredient'.(string)$counter]);?> - <input type="submit" name="name[<?php echo($response['recipe']['ingredient'.(string)$counter]);?>]" Value="Add to Cart" id="<?php echo($response['recipe']['ingredient'.(string)$counter]);?>"/></p>
                <?php endfor;?>
            </form>
            

        </div>
    </div>
    <div class="row">
        <!-- 5 star rating design in the form is from https://codeconvey.com/feedback-form-in-html/ -->
        <style>
            .checked {
                color: orange;
            }
            .fa{
                font-size: 200%;
            }
            .userReviewsHeader>.fa{
                font-size:150%;
            }
            .star-rating {
                margin: 10px 0 0px;
                font-size: 0;
                white-space: nowrap;
                display: inline-block;
                width: 175px;
                height: 35px;
                overflow: hidden;
                position: relative;
                background: url('data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IiB3aWR0aD0iMjBweCIgaGVpZ2h0PSIyMHB4IiB2aWV3Qm94PSIwIDAgMjAgMjAiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDIwIDIwIiB4bWw6c3BhY2U9InByZXNlcnZlIj48cG9seWdvbiBmaWxsPSIjREREREREIiBwb2ludHM9IjEwLDAgMTMuMDksNi41ODMgMjAsNy42MzkgMTUsMTIuNzY0IDE2LjE4LDIwIDEwLDE2LjU4MyAzLjgyLDIwIDUsMTIuNzY0IDAsNy42MzkgNi45MSw2LjU4MyAiLz48L3N2Zz4=');
                background-size: contain;
            }
            .star-rating i {
                opacity: 0;
                position: absolute;
                left: 0;
                top: 0;
                height: 100%;
                width: 20%;
                z-index: 1;
                background: url('data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IiB3aWR0aD0iMjBweCIgaGVpZ2h0PSIyMHB4IiB2aWV3Qm94PSIwIDAgMjAgMjAiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDIwIDIwIiB4bWw6c3BhY2U9InByZXNlcnZlIj48cG9seWdvbiBmaWxsPSIjRkZERjg4IiBwb2ludHM9IjEwLDAgMTMuMDksNi41ODMgMjAsNy42MzkgMTUsMTIuNzY0IDE2LjE4LDIwIDEwLDE2LjU4MyAzLjgyLDIwIDUsMTIuNzY0IDAsNy42MzkgNi45MSw2LjU4MyAiLz48L3N2Zz4=');
                background-size: contain;
            }
            .star-rating input {
                -moz-appearance: none;
                -webkit-appearance: none;
                opacity: 0;
                display: inline-block;
                width: 20%;
                height: 100%;
                margin: 0;
                padding: 0;
                z-index: 2;
                position: relative;
            }
            .star-rating input:hover + i,
            .star-rating input:checked + i {
                opacity: 1;
            }
            .star-rating i ~ i {
                width: 40%;
            }
            .star-rating i ~ i ~ i {
                width: 60%;
            }
            .star-rating i ~ i ~ i ~ i {
                width: 80%;
            }
            .star-rating i ~ i ~ i ~ i ~ i {
                width: 100%;
            }
            .review input{
                width: 100%;
                border: 1px solid #ddd;
            }
        </style>
        <div class="col-md-4 pt-5">
            <?php for($x=1;$x<=5;$x++):?>
                <?php if($x<=$response['recipe']['rating']):?>
                    <span class="fa fa-star checked"></span>
                <?php else:?>
                    <span class="fa fa-star"></span>
                <?php endif;?>
            <?php endfor;?>
            <p><?php if($response['review']['numResults']>=2){
                echo((string) $response['review']['numResults'] . " reviews");
            } elseif($response['review']['numResults']==1) {
                echo((string) $response['review']['numResults'] . " review");
            } else{
                echo("No reviews");
            }?></p>
            <a href="#review" class="btn btn-primary" data-bs-toggle="collapse">Leave a Review</a>
        </div>
        <div class="col-md-8 pt-5">
            <div id="review" class="collapse pb-3">
                <a href="#review" class="d-flex btn-close collapse" data-bs-toggle="collapse" style="margin-left:auto;"></a>
                <form action="" method="post">
                <p class="h3">Rating</p>
                <span class="star-rating">
                    <input type="radio" name="rating" value="1"><i></i>
                    <input type="radio" name="rating" value="2"><i></i>
                    <input type="radio" name="rating" value="3"><i></i>
                    <input type="radio" name="rating" value="4"><i></i>
                    <input type="radio" name="rating" value="5"><i></i>
                </span>
                <hr>
                <p class="h3">Add a title to your review</p>
                <input type="text" name="title" placeholder="Enter your Title" class="form-control" required>
                <hr>
                <p class="h3">Add a written review</p>
                <textarea name="written" rows="5" class="form-control" required></textarea>
                <br>
                <input type="submit" value="Submit Review" class="btn btn-primary" name="submit"/>
                </form>
            </div>
            <?php for($counter=1;$counter<=$response['review']['numResults'];$counter++): ?>
            <div class="userReview">
            <hr>
                <p class="h5"><?php echo($response['review']['publisher'.(string)$counter]); ?> - <?php echo($response['review']['title'.(string)$counter]); ?></p>
                <div class="userReviewsHeader" style="display: flex;">
                <?php for($x=1;$x<=5;$x++):?>
                    <?php if($x<=$response['review']['rating'.(string)$counter]):?>
                        <span class="fa fa-star checked"></span>
                    <?php else:?>
                        <span class="fa fa-star"></span>
                    <?php endif;?>
                <?php endfor;?>
                    <span style="flex: 1; text-align: right; white-space: nowrap; font-size: 120%"><?php echo($response['review']['date'.(string)$counter]); ?></span>
                </div>
                <p><?php echo($response['review']['description'.(string)$counter]); ?> </p>
                
            <div>
            <?php endfor;?>
            <hr>
                
                
                
            </div>
            <?php

?>
        </div>
    </div>  
</div>

</body>
<?php require_once(__DIR__ . '/partials/footer.php'); ?>