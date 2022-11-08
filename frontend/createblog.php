<?php require_once(__DIR__ . '/partials/header.php'); ?>
<body>
<?php require_once(__DIR__ . '/partials/navbar.php'); ?>
<div class="container p-5">

<form method="POST" action=""id="createblog">
<p class="h3">Title:</p>
<input type="text" placeholder="Title of Blog Post" name="blogtitle" class="form-control form-control-lg">
<br>
<p class="h3">Blog Post:</p>
<textarea  form="createblog" name="blogcontent"  placeholder="Start Writing!" class="form-control" style="min-height: 200px;"></textarea>
<br>
<input type="submit" value="Create Blog Page" name="submit" class="btn btn-primary">

</form>
<?php
if(isset($_POST['submit']))	{ 
    $newBlog = array();
    $newBlog['type'] = "createBlog";
    $newBlog['username'] = getUsername();
    $newBlog['title']=$_POST["blogtitle"];
    $newBlog['textBody']=$_POST["blogcontent"];
    $response = rabbitSearchClient($newBlog);
    //echo(var_dump($newBlog));

}
?>

</div>
</body>
<?php require_once(__DIR__ . '/partials/footer.php'); ?>