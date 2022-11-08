<?php require_once(__DIR__ . '/partials/header.php'); ?>
<body>
<?php require_once(__DIR__ . '/partials/navbar.php'); ?>

    <!-- ENTER STUFF HERE-->

    <?php   
    $request = array();
    $request['type'] = "searchBlogTitle";
    $request['searchString'] = "";
    
    $response = rabbitSearchClient($request);

    ?>


<div class="container p-5">

<?php //echo(var_dump($response));?>

<style>
    a {
        color: inherit; 
        text-decoration: inherit;
    }
</style>

<?php for($counter=1;$counter<=$response['numResults'];$counter++): ?>
    <a href="blog.php?id=<?php echo($response['blogID'.(string)$counter]);?>">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title"><?php echo($response['title'.(string)$counter]);?></h4>
            <p class="card-text h6">Blog by <?php echo($response['publisher'.(string)$counter]);?> on <?php echo($response['publishDate'.(string)$counter]);?></p>
            <p class="card-text"> <?php echo(substr($response['textBody'.(string)$counter], 0, 150). " . . .");?></p>
        </div>
    </div>
    </a>
    <br>

<?php endfor; ?>
</div>
</body>
<?php require_once(__DIR__ . '/partials/footer.php'); ?>