<?php
session_start();

// Restrict access for 'vente' and 'achat'
if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Sup Achat', 'Sup Vente'])) {
    header("Location: Acess_Denied");
    exit();
}
?>
