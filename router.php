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
    "ETAT_F_CUMULE_PAIMENT" => "etat_fournissuer_paiment.php",
    "Arrivage" => "reception.php",
    "retour" => "retour_documents.php",
    "expire" => "expire.php",
    "charge" => "charges_dashboard.php",
    "manque_casse" => "manque_casse.php",
    "vente_logs" => "vente_track/view_logs.php",
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
        "inv_adv2" => "inventory/inv_admin_new.php",
        "Manque_Casse" => "manque_casse.php",

        "inv_ad" => "inventory/inv_admin.php",
        "print" => "printing.php",
    "manage" => "fil_manage/manage.php",
    "upload" => "fil_manage/upload.php",
    "rot_men_global" => "rot_men_global.php",
    "manage_default_emplacements" => "manage_default_emplacements.php",

    "analyse_client" => "analyse_client.php",

    "recap_achat_facturation" => "recap_achat_facturation.php",
    "Rotation" => "rotation.php",
    
    "Journal_Vente" => "journal.php",
    "feedback" => "admin_feedback.php",
    "sess" => "session.php",
    "draft" => "draft.php",
    "sudo" => "sudo.php",
    "space_cleaner" => "space_cleaner.php",

    "Mouvement_Stock"=> "mouvementstock.php",
    
    "AFFECTATION" => "affectation.php",
    "recouverement" => "rcvrmnt.php",
    "l" => "l.php",
    "adminmail" => "adminmail.php",
    "signup" => "signup.html",
    "monyold" => "moneyold.php",
 "mony" => "moneyv2.php",
    "en" => "english.html",

    "Acess_Denied" => "access_denied.html",
    "view_data" => "view_data.php",

    "side" => "sidebarv2.php",
    "Quota" => "quota.php",
    "bank" => "bank_improved.php",

    "editnavbar" => "sidebar/editnavbar.php",
    "login" => "login.php",
    "c" => "arrowgame.html",
    "403" => "403.php",
    "t" => "test.html",
    "403_viewer" => "403_log_viewer.php",
    "build" =>"work.html",
    "secure_login" => "secure_login.html",

    "articles" => "articles.php",

    // Mail Management Pages
     "mail_dashboard" => "mail/dashboard.php",
    "mail_templates" => "mail/templates.php",
    "mail_contacts" => "mail/contacts.php",
    "send_mail" => "mail/send.php",
    "mail_logs" => "mail/logs.php",
    "mail_settings" => "mail/settings.php",
    "mail_recipients" => "mail/recipients.php",


    "network" => "wifi.php",
       // "simuler" => "simulation.php",


    "simuler" => "real_simulation.php"
];

$page = isset($_GET['page']) ? $_GET['page'] : 'BNM'; // Default page

if (array_key_exists($page, $pages)) {
    include $pages[$page]; // Load the requested page
} else {
    http_response_code(404);
    echo "Page not found.";
}
?>
