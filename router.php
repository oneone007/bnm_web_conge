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

    "retour" => "retour_documents.php",

    
    "charge" => "charges_dashboard.php",

    "CONFIRMED_ORDERS" => "confirm_order.php",
    "Annual_Recap_V" => "year.php",
    "Annual_Recap_A" => "yeara.php",
    "dz" => "algeria_map.html",
        "dzz" => "algeria_map.php",
    "portf" => "portf.php",
    "DETTE_F" => "etatfournisseur.php",
        "inv" => "inventory/inv.php",
        "inv_admin" => "inventory/inv_admin.php",
        "inv_saisie" => "inventory/saise.php",

        "inv_ad" => "inventory/inv_admin_new.php",
        "print" => "printing.php",
    "manage" => "fil_manage/manage.php",
    "upload" => "fil_manage/upload.php",

    "recap_achat_facturation" => "recap_achat_facturation.php",
    "Rotation" => "rotation.php",
    
    "Journal_Vente" => "journal.php",
    "feedback" => "admin_feedback.php",
    "sess" => "session.php",
    "draft" => "draft.php",
    "simuler" => "simulation.php",
    "sudo" => "sudo.php",

    "Mouvement_Stock"=> "mouvementstock.php",
    
    "AFFECTATION" => "affectation.php",
    "recouverement" => "rcvrmnt.php",
    "l" => "l.php",

    "signup" => "signup.html",
    "monyold" => "moneyold.php",
 "mony" => "moneyv2.php",


    "Acess_Denied" => "access_denied.html",
    "view_data" => "view_data.php",

    "side" => "sidebar.php",
    "Quota" => "quota.php",
    "bank" => "bank.php",


    "login" => "login.php",
    "c" => "arrowgame.html",
    "403" => "403.php",
    "t" => "tes.php",
    "403_viewer" => "403_log_viewer.php",

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
