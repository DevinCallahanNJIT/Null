<?php require_once(__DIR__ . "/../lib/helper.php"); ?>

<?php 
if(isset($_POST['clear'])){
  clearCart();
}
?>

<nav class="navbar navbar-expand-sm navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="/../index.php">Logo</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mynavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mynavbar">
      <ul class="navbar-nav me-auto">
        <?php if (!isLoggedIn()): ?>
            <li class="nav-item">
                <a class="nav-link" href="/../login.php">Login</a>
            </li>
        <?php endif; ?>
        <?php if (isLoggedIn()): ?>
            <li class="nav-item">
                <a class="nav-link" href="/../ingredientcabinet.php">Ingredient Cabinet</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/../customrecipes.php">Custom Recipes</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/../createblog.php">Create Blog</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/../bloglist.php">Blog List</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/../logout.php">Logout</a>
            </li>
        <?php endif; ?>
      </ul>
      
      <form class="d-flex" action="search.php">
        <input class="form-control me-2" type="text" placeholder="Search" name="s">
        <input type="submit" class="btn btn-primary" value="Search"/>
      </form>
      <a href="" data-bs-toggle="offcanvas" data-bs-target="#demo">
      <div class="d-flex px-4"  >
        <!--style="-webkit-filter: invert(100%); filter: invert(100%);" -->
        <i class="bi bi-cart" style="scale:150%; -webkit-filter: invert(100%); filter: invert(100%);"></i>
      </div>
      </a>
    </div>
  </div>
</nav>

<div class="offcanvas offcanvas-end" id="demo">
  <div class="offcanvas-header">
    <h1 class="offcanvas-title">Shopping Cart</h1>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <?php createCart();
    $items = getCartItems();
    ?>
    <?php for($i = 1; $i <= count($items); $i++):?>
      <p><?php echo($items['item'.(string)$i]);?></p>
    <?php endfor;?>
    
    <form action="" method="post">
      <?php if(!cartEmpty()):?>
        <input type="submit" value="Clear Cart" class="btn btn-primary" name="clear"/>
      <?php endif;?>
    </form>
  </div>
</div>

