<?php

session_start(); // Inizializzo la sessione. (Necessario per accedere alla variabile $_SESSION)

require_once __DIR__ . '/utils/database.php';
require_once __DIR__ . '/utils/crypto.php';

$ERROR = null; // Variabile per gestire la visualizzazione degli errori

// Verifico che l'utente sia loggato
if (!isset($_SESSION['user_email'])) {
    // Se l'utente non è loggato lo reindirizzo alla pagina di login
    header('Location: /login.php');
    exit();
}

// Verifico che esista una conversazione attiva tra i due utenti
$results = $connection->query(
    "SELECT * FROM conversations WHERE 
    (user1 = '{$_SESSION['user_email']}' AND user2 = '{$_GET['to']}') OR 
    (user1 = '{$_GET['to']}' AND user2 = '{$_SESSION['user_email']}')"
);

if ($results->num_rows !== 0) {
    // Esiste una conversazione tra i due utenti
    // Recupero la chiave di cifratura della conversazione
    $key = $results->fetch_assoc()['shared_key'];
    $conversation_id = $results->fetch_assoc()['id'];

    // Recupero i messaggi della conversazione
    $results = $connection->query(
        "SELECT * FROM messages WHERE 
        conversation_id = $conversation_id"
    );

    // Decifro i messaggi
    $messages = [];
    while ($message = $results->fetch_assoc()) {
        $message['content'] = decryptAES_CTR($message['content'], $key);
        $messages[] = $message;
    }

    // Invio i messaggi alla pagina
    echo json_encode($messages);    
} else {
    // Non esiste una conversazione tra i due utenti
    // Reindirizzo l'utente alla pagina delle conversazioni
    $ERROR = "conversation_not_found";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITT KDC - Chat with <?php echo $_GET["to"]; ?></title>
</head>

<body>

</body>

</html>