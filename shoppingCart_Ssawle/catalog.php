<?php
if(!session_id()) {
    session_start();
}
?>

<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>Minis Mall Shopping Catalog</title>
        <link href="css/minismall.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <h2>Product Catalog</h2>
        <?php
        
        // check to see if the "number of items" already exists in session.
        // if not initialize it by setting it to 0
        if(!isset($_SESSION['numItems'])) {
            $_SESSION['numItems'] = 0;
        }
        
        if(!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = Array();
        }
        ?>
        
        <a href="cart_ssawle.php">View your cart</a> | 
        <a href="index.html">Back to Product Categories</a>
        
        <?php
        // connect to the database server
        // fetch all existing catagory ids
        require 'dbConnect.php';
        
        try {
            $sql = "SELECT catid FROM categories";
            
            $catResult = $pdo->query($sql);
            
        } catch (Exception $ex) {
            $error = "Error fetching categories";
            include 'error.html.php';
            exit();
        }
        
        $catIds = array();
        $ctr = 0;
        
        while($row = $catResult->fetch()) {
            $catIds[$ctr] = $row['catid'];
            $ctr++;
        }
        
        if(isset($_GET['cat']) && in_array($_GET['cat'], $catIds)) {
            
            $cat = $_GET['cat'];
            //echo "<h3>The Category was valid</h3>\n";
            
        } else {
            $cat = 1;
            //echo "<h3>The Category was NOT valid</h3>\n";
        }
        
        $_SESSION['cat'] = $cat;
        
        // query the DB for all products in the specified category
        try {
            $itemResult = $pdo->query("SELECT * FROM products WHERE category = $cat");
            
        } catch (Exception $ex) {
            $error = "Error fetching products: " . $ex->getMessage();
            include 'error.html.php';
            exit();
        }
        ?>
        
        <br><br>
        <form action="" method="post">
            <table>
                <tr class="header">
                    <th>Image</th>
                    <th>Description</th>
                    <th>Price - US$</th>
                    <th style="background-color:white">&nbsp;</th>
                </tr>
                <?php
                // step through the item results writing a table row for each result.
                while($row = $itemResult->fetch()) {
                    
                    // convert html special characters to their html entity.
                    // Also strip any html tags.
                    $imgLocation = htmlspecialchars(strip_tags($row['loc']));
                    $desc = htmlspecialchars(strip_tags($row['description']));
                    $price = htmlspecialchars(strip_tags($row['price']));
                    
                    $price = "\$" . number_format($price, 2);
                    $productId = htmlspecialchars(strip_tags($row['prodid']));
                    
                    // check the session to see if there are already items in the cart
                    // if this item exists in the cart then we want to get 
                    // the quantity of this item that the user has added to the cart.
                    // if item is not in cart, set quantity to 0.
                    
                    if(isset($_SESSION['cart'][$productId])) {
                        $qty = $_SESSION['cart'][$productId];
                    } else {
                        $qty = 0;
                    }
                    
                    echo <<<TABLEROW
                    <tr>
                        <td><img src="$imgLocation" alt="$desc"></td>
                        <td class="desc">$desc</td>
                        <td class="price">$price</td>
                        <td class="qty"><label
                        for="quantityForProduct$productId">Qty</label>
                        <input type="text" name="$productId" 
                        id="quantityForProduct$productId" value="0" size="3"></td>
                    </tr>
TABLEROW;
                }
                
                try {
                    $itemResult = $pdo->query("SELECT * FROM products WHERE category = $cat");
            
                } catch (Exception $ex) {
                    $error = "Error fetching products: " . $ex->getMessage();
                    include 'error.html.php';
                    exit();
                }
                
                if(isset($_POST['submit'])) {
                    while($row = $itemResult->fetch()) {
                        
                        $desc = htmlspecialchars(strip_tags($row['description']));
                        $price = htmlspecialchars(strip_tags($row['price']));
                        $productId = htmlspecialchars(strip_tags($row['prodid']));
                        $qty = $_POST[$productId];
                        
                        if ($qty > 0) { 
                            if(!isset($_SESSION['cart'][$productId])) {
                                $_SESSION['cart'][$productId] = 0;
                            }
                            $oldqty = $_SESSION['cart'][$productId];
                            $newqty = $qty + $oldqty;
                            $_SESSION['cart'][$productId] = $newqty;
                            $_SESSION['numItems'] += $qty;
                            $numItems = $_SESSION['numItems'];
                            
                            echo"<p>Your shopping cart contains $numItems items</p>";
                            echo "You have added to your cart: <em>$qty</em> of the product: <em>$desc</em>. At \$$price each.";
                        }
                    }
                }
                ?>
            </table>
            <input type="submit" name="submit" value="Add to Cart"><br>
            <a href="cart_ssawle.php">View your cart</a> | 
            <a href="index.html">Back to Product Categories</a>
        </form>
    </body>
</html>
