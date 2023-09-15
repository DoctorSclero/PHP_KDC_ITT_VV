<?php 

session_start();

require_once __DIR__ . '/utils/database.php';

$ERROR = null;

// Verifico che l'utente sia loggato
if (!isset($_SESSION['user_id'])) {
    // L'utente non è loggato lo reindirizzo alla pagina di login
    header('Location: /login.php');
    exit();
}

// Elimino tutte le conversazioni con l'utente
$connection->query(
    "DELETE FROM conversations WHERE from_user = {$_SESSION['user_id']} OR to_user = {$_SESSION['user_id']}"
);

?>