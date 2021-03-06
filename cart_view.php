<?php
//include header file, connact file, classes file
include("head.php");
include("navbar.php");
include("connect.php");

//save the cart1 to a variable
$cart = $_SESSION['cart1']->cart_array;

//get the keys from the cart array
$keys = array_keys($cart);
?>

<!-- content -->
<section class="h-100 h-custom" style="background-color: #eee;">
    <div class="container py-5 h-100">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-lg-7">
                                <h5 class="mb-3"><a href="product_category.php" class="text-body"><i class="fas fa-long-arrow-alt-left me-2"></i>Continue shopping</a></h5>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div>
                                        <p class="mb-1">Shopping cart</p>
                                        <p class="mb-0">You have <?= count($cart) ?> item(s) in your cart</p>
                                    </div>
                                </div>

                                <?php
                                $subtotal = 0;

                                //for each key, get the product name and price and other details from the database
                                foreach ($keys as $key) {
                                    $result = $mysqli_db->query("SELECT * FROM product WHERE product_id = '$key'");
                                    $row = $result->fetch_assoc();
                                    $product_name = $row['product_name'];
                                    $product_price = $row['product_price'];
                                    $product_unit = $row['product_unit'];
                                    $product_image = $row['product_image'];
                                    $product_quantity = $cart[$key];
                                    $subtotal += ($product_price * $product_quantity);
                                    include("cart_item.php");
                                }
                                ?>
                            </div>

                            <div class="col-lg-5">
                                <div class="card bg-primary text-white rounded-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <h5 class="mb-0">Card details</h5>
                                            <img src="assets/avatars/<?= $_SESSION['profile_photo'] ?>" class="img-fluid rounded-3" style="width: 45px;" alt="Avatar">
                                        </div>
                                        <p class="small mb-2">Card type</p>
                                        <a href="#!" type="submit" class="text-white"><i class="fab fa-cc-mastercard fa-2x me-2"></i></a>
                                        <a href="#!" type="submit" class="text-white"><i class="fab fa-cc-visa fa-2x me-2"></i></a>
                                        <a href="#!" type="submit" class="text-white"><i class="fab fa-cc-amex fa-2x me-2"></i></a>
                                        <a href="#!" type="submit" class="text-white"><i class="fab fa-cc-paypal fa-2x"></i></a>
                                        <form class="mt-4">
                                            <div class="form-outline form-white mb-4">
                                                <input type="text" id="typeName" class="form-control form-control-lg" siez="17" placeholder="Cardholder's Name" />
                                                <label class="form-label" for="typeName">Cardholder's Name</label>
                                            </div>

                                            <div class="form-outline form-white mb-4">
                                                <input type="text" id="typeText" class="form-control form-control-lg" siez="17" placeholder="1234 5678 9012 3457" minlength="19" maxlength="19" />
                                                <label class="form-label" for="typeText">Card Number</label>
                                            </div>

                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <div class="form-outline form-white">
                                                        <input type="text" id="typeExp" class="form-control form-control-lg" placeholder="MM/YYYY" size="7" id="exp" minlength="7" maxlength="7" />
                                                        <label class="form-label" for="typeExp">Expiration</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-outline form-white">
                                                        <input type="password" id="typeText" class="form-control form-control-lg" placeholder="&#9679;&#9679;&#9679;" size="1" minlength="3" maxlength="3" />
                                                        <label class="form-label" for="typeText">Cvv</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                        <hr class="my-4">
                                        <div class="d-flex justify-content-between">
                                            <p class="mb-2">Subtotal</p>
                                            <p class="mb-2">KES <?= $subtotal ?></p>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <?php $shipping = $subtotal * 0.05 ?>
                                            <p class="mb-2">Shipping</p>
                                            <p class="mb-2">KES <?= $shipping ?></p>
                                        </div>
                                        <div class="d-flex justify-content-between mb-4">
                                            <?php $total = $subtotal + $shipping ?>
                                            <p class="mb-2">Total</p>
                                            <p class="mb-2">KES <?= $total ?></p>
                                        </div>

                                        <!-- Checkout form -->
                                        <form action="cart_view.php" method="POST">
                                            <button type="submit" name="checkout" class="btn btn-success btn-block btn-lg">
                                                <div class="d-flex justify-content-between">
                                                    <span>KES <?= $total ?></span>
                                                    <span>Checkout <i class="fas fa-long-arrow-alt-right ms-2"></i></span>
                                                </div>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// handle remove from item
if (isset($_POST['remove'])) {
    include("cart_remove.php");
    echo "<script>";
    echo "location.assign('cart_view.php')";
    echo "</script>";
}

// handle checkout
if (isset($_POST['checkout'])) {
    // save data 
    // into order table

    // email address
    $email_address = $_SESSION['email_address'];

    insert_into_orders($email_address, $subtotal, $shipping, $total);

    //get order id
    $result1 = $mysqli_db->query("SELECT `id` FROM `orders` WHERE `email_address` = '$email_address' ORDER BY `id` DESC LIMIT 1");
    $row1 = $result1->fetch_assoc();
    $order_id = $row1['id'];

    // order_item table
    foreach ($keys as $key) {
        global $order_id;
        $result = $mysqli_db->query("SELECT product_price FROM product WHERE product_id = '$key'");
        $row = $result->fetch_assoc();

        // product id
        $product_id = $key;

        // product price
        $product_price = $row['product_price'];

        // quantity
        $quantity = $cart[$key];

        insert_into_order_item($product_id, $order_id, $product_price, $quantity);
    }

    // transactions table
    $res = insert_into_transactions($email_address, $order_id);

    if ($res && $result1 && $result) {
        echo "<script>";
        echo "alert('Your order has been placed.')";
        echo "</script>";

        // clear the cart
        foreach ($keys as $key) {
            $_SESSION['cart1']->remove_from_cart($key);
        }
    } else {
        echo "<script>";
        echo "alert('Something went wrong. Please contact Support.')";
        echo "</script>";
    }
}

include("footer.php");
?>