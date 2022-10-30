<!DOCTYPE html>
<html>
<body>

<nav>
    <a href='home.php'>Home</a> |
    <a href='liquorcabinet.php'>Your Liquor Cabinet</a> |
    <a href='logout.php'>Logout</a>
</nav>

<div>
    <h1>Home</h1>
    <form method="POST" action="">
    <h3>Welcome</h3>
    <div>
        <label>Enter the liquor that you own</label><br>
	    <input type="text" placeholder="Add Liquor" name="addliquor" >
        <input type="submit" value="Search" name="submit" />
	</div>
</div>