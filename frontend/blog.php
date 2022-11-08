
<?php require_once(__DIR__ . '/partials/header.php'); /* blog template https://www.w3schools.com/howto/howto_css_blog_layout.asp */?>
<body>
<?php require_once(__DIR__ . '/partials/navbar.php'); ?>
<div class="container p-5">
<?php
$id=0;
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
//echo(var_dump($id));
$request = array();
$request['type'] = "fetchBlog";
$request['blogID'] = $id;

$response = rabbitSearchClient($request);
//echo(var_dump($response));



?>

<p class="h1"><?php echo($response['title']); ?></p>
<hr>
<p class="h5">Blog by <?php echo($response['publisher']);?> on <?php echo($response['publishDate']);?></p>
<br>
<p><?php echo($response['textBody']); ?></p>

</body>

<?php require_once(__DIR__ . '/partials/footer.php'); ?>