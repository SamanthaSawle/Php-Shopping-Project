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
        <title>Minis Mall Shopping Cart</title>
        <link href="css/minismall.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <h2>Personal Shopping Cart!</h2>
        <a href="index.html">Back to Product Categories</a>
        <?php
        require 'dbConnect.php';
       
        $totalPrice = 0;
        $prodIDStr = "";
        $numItems = $_SESSION['numItems'];
        $cartResult = Array();
        
        if ($numItems != 0) {
            echo "<h3>Your shopping cart has $numItems Items!</h3>";
            $cart = $_SESSION['cart'];
            
            foreach ($cart as $key => $item) {
                
                $prodIDStr .= $key . " OR ";
                
            }
            
            $prodIDStr = rtrim($prodIDStr, " OR ");
            
            try {
                    
                $sql = "SELECT * FROM products WHERE prodid = $prodIDStr";
            
                $cartResult = $pdo->query($sql);
                    
            } catch (Exception $ex) {
                $error = "Error fetching Cart Products";
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
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Remove?</th>
                    <th style="background-color:white">&nbsp;</th>
                </tr>
                
                <?php
                
                while($row = $cartResult->fetch()) {
                    
                    $imgLocation = htmlspecialchars(strip_tags($row['loc']));
                    $desc = htmlspecialchars(strip_tags($row['description']));
                    $price = htmlspecialchars(strip_tags($row['price']));
                    
                    $productId = htmlspecialchars(strip_tags($row['prodid']));
                    
                    if (isset($_SESSION['cart'][$productId])) {
                        $qty = $_SESSION['cart'][$productId];
                    }
                    
                    $accumPrice = $price * $qty;
                    $totalPrice += $accumPrice;
                    
                    $price = "\$" . number_format($price, 2);
                    
                    echo <<<TABLEROW
                    <tr>
                        <td><img src="$imgLocation" alt="$desc"></td>
                        <td class="desc">$desc</td>
                        <td class="price">$price</td>
                        <td class="qty"><label
                        for="quantityForProduct$productId">Qty</label>
                        <input type="text" name="$productId" 
                        id="quantityForProduct$productId" value="$qty" size="3"></td>
                        <td class="qty"><input type="checkbox" name="remove$productId" id="removeCheckboxFor$productId"></td>
                    </tr>
TABLEROW;
                }
                echo <<<TABLEROW2
                    <tr>
                        <td><br></td>
                        <td><br></td>
                        <td class="price">Total Price:<br>\$$totalPrice</td>
                        <td class="qty">Total Items:<br>$numItems</td>
                        <td><input type="submit" name="submit" value="Submit Changes"></td>
                    </tr>
TABLEROW2;
                ?>
            </table>
        <a href="index.html">Back to Product Categories</a>
        
        <?php
            
     	} else {  // $_SESSION['cart'] doesn NOT exist
            echo "<h3>Your shopping cart is empty!</h3>";
            $cart = Array();
    	}

//Check if ‘remove’ form field exists and set shortcut
//
	if (isset($_POST['remove'])) {
     		$remove = $_POST['remove'];
   	}
   	else {
     		$remove = Array();
   	}
        
        if(isset($_POST['submit'])) {
            echo"SUBMIT BUTTON";
            
            try {
                    
                $sql = "SELECT * FROM products WHERE prodid = $prodIDStr";
            
                $cartResult = $pdo->query($sql);
                    
            } catch (Exception $ex) {
                $error = "Error fetching Cart Products";
                include 'error.html.php';
                exit();
            }
            
            while($row = $cartResult->fetch()) {
                echo"WHILE LOOP ID";
                $productId = htmlspecialchars(strip_tags($row['prodid']));
                $newqty = $_POST[$productId];
                
                $oldqty = $_SESSION['cart'][$productId];
                
                if ($newqty != $oldqty) {
                    echo"QUANTITY CHANGED";
                    $_SESSION['cart'][$productId] = $newqty;
                    
                    if ($newqty == 0) {
                    
                        unset($_SESSION['cart'][$productId]);
                    
                    } elseif ($newqty > $oldqty) {
                        $alter = $newqty - $oldqty;
                        $numItems = $_SESSION['numItems'];
                        $numItems += $alter;
                        
                    } else {
                        $alter = $oldqty - $newqty;
                        $numItems = $_SESSION['numItems'];
                        $numItems -= $alter;
                        $_SESSION['numItems'] = $numItems;
                    }
                    
                }
                
                if (isset($_POST['remove' . $productId])) {
                    
                    unset($_SESSION['cart'][$productId]);
                }
                
                if ($_SESSION['numItems'] < 0) {
                    $_SESSION['numItems'] = 0;
                }
            }
            header("Refresh:0");
        }

        ?>
        
    </body>
</html>
