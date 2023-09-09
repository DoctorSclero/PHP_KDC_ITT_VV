<?php

/**
 * Questo script si occupa di distruggere la sessione dell'utente
 * causando pertanto il logout dello stesso.
 * Infatti quando si distrugge la sessione perde il riferimento
 * all'email dell'utente impedendo alla pagina dashboard.php di
 * riconoscerlo deducendo che non è quindi loggato.
 */

session_start(); // Inizializzo la sessione. (Necessario per accedere alla variabile $_SESSION)
session_destroy(); // Distruggo la sessione

header('Location: /index.php'); // Reindirizzo l'utente alla home page.
exit;

?>