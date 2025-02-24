<?php
$pages = [
    "BNM" => "index.php",
    "Main" => "main.php",
    "Etatstock" => "etatstock.php",
    "Coming" => "no.html",
    "Product" => "product.php",
    "Recap_Achat" => "recap_achat.php",
    "Recap_Vente" => "recap.php",
    "signup" => "signup.html",
    "side" => "sidebar.html",
    "login" => "login.php",
    "bar" => "bar.html",
    "a" => "a.html",
    "b" => "b.html",
    "c" => "c.html",
    "t" => "tes.php",

    "build" =>"work.html"




];

$page = isset($_GET['page']) ? $_GET['page'] : 'BNM'; // Default page

if (array_key_exists($page, $pages)) {
    include $pages[$page]; // Load the requested page
} else {
    http_response_code(404);
    echo "Page not found.";
}
?>
