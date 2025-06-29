<?php
$pages = [
    "BNM" => "index.php",
    "Main" => "main.php",
    "whatsnew" => "whatsnew.html",
    "Etatstock" => "etatstock.php",
    "remise" => "remise.html",
    "rot_men_achat" => "rot_men_achat.php",
    "rot_men_vente" => "rot_men_vente.php",
    "Coming" => "comingsoon.html",
    "Product" => "product.php",
    "Recap_Achat" => "recap_achat.php",
    "Recap_Vente" => "recapvente.php",
    "Recap_Vente_Facturation" => "recapvente_fact.php",
    "ETAT_F" => "etat_fournisseur.php",
    "ETAT_F_CUMULE" => "etat_fournisseur_cumule.php",

    "CONFIRMED_ORDERS" => "confirm_order.php",
    "Annual_Recap_V" => "year.php",
    "Annual_Recap_A" => "yeara.php",
    "dz" => "algeria_map.html",
        "dzz" => "algeria_map.php",

    "DETTE_F" => "etatfournisseur.php",

    "recap_achat_facturation" => "recap_achat_facturation.php",
    "Rotation" => "rotation.php",
    
    "Journal_Vente" => "journal.php",
    "admin" => "admin_feedback.php",

    "draft" => "draft.php",

    "Mouvement_Stock"=> "mouvementstock.php",
    
    "AFFECTATION" => "affectation.php",
    "recouverement" => "rcvrmnt.php",
    "l" => "l.php",

    "signup" => "signup.html",
    "mony" => "money.php",
 "mony2" => "moneyv2.php",
  "mony3" => "moneyv3.php",


    "Acess_Denied" => "access_denied.html",
    "view_data" => "view_data.php",

    "side" => "sidebar.php",
    "Quota" => "quota.php",
    "bank" => "bank.php",


    "login" => "login.php",
    "c" => "arrowgame.html",
    "working" => "constract.html",
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
