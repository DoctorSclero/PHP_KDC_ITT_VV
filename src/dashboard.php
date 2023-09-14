<?php

session_start(); // Inizializzo la sessione. (Necessario per accedere alla variabile $_SESSION)

require_once __DIR__ . '/utils/database.php';

$ERROR = null; // Variabile per gestire la visualizzazione degli errori

// Verifico che l'utente sia loggato
if (!isset($_SESSION['user_id'])) {
    // L'utente non è loggato, lo rimando alla pagina di login
    header('Location: /login.php');
    exit();
}

// Verifico se la richiesta sia una 
// richiesta di avvio di una conversazione
if (isset($_POST)) {
    // L'utente vuole conversare con qualcuno
    try {

        // Recupero i dati necessari
        $sender = $_SESSION["user_id"];
        $recipient = $connection->query(
            "SELECT id FROM users WHERE email = '{$_POST["recipient_email"]}'"
        )->fetch_assoc()["id"];

        // Verifico che il destinatario sia presente nel database
        if (empty($recipient)) {
            // Il destinatario non è presente lancio un errore
            throw new Exception("recipient_not_found");
        }

        // Verifico ora se esiste già una conversazione tra i due utenti
        $conversation = $connection->query(
            "SELECT * FROM conversations C WHERE
            (from_user = '$sender' AND to_user = '$recipient') OR
            (from_user = '$recipient' AND to_user = '$sender')"
        );

        // Verifico se la conversazione è presente
        if ($conversation->num_rows === 0) {
            // Non esiste una conversazione tra i due utenti
            // quindi ne creo una nuova

            // Genero casualmente una nuova chiave per la cifratura della conversazione
            $shared_key = bin2hex(random_bytes(32));

            // Creo una nuova conversazione
            $connection->query(
                "INSERT INTO conversations (first_user, second_user, shared_key) VALUES 
                    ('$sender', '$recipient', '$shared_key')"
            );

            // Indirizzo l'utente alla pagina della conversazione
            header("Location: /chat.php?with=$recipient");
        } else {
            // Esiste già una conversazione tra i due utenti
            // indirizzo l'utente alla pagina della conversazione
            header("Location: /chat.php?with=$recipient");
        }
    } catch (Exception $e) {
        $ERROR = $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="shortcut icon" href="./assets/stemma_IISVV.png" type="image/x-icon">
    <link rel="stylesheet" href="/css/style.css">
</head>

<body>
    <div>
        <h1>Dashboard</h1>
        <a name="logout" id="logout" class="btn-danger" href="/logout.php">Logout</a>
        <a name="renew_keys" id="renew_keys" class="btn-danger" href="/renew-keys.php">Elimina Conversazioni</a>
        <h2>Avvia conversazione</h2>
        <form method="POST">
            <label for="recipient_email">Inserisci email della persona con cui vuoi conversare</label>
            <input type="email" name="recipient_email" id="recipient_email" placeholder="Email destinatario">
            <input type="submit" value="Conversa">
        </form>
        <h2>Conversazioni attive</h2>
        <ul>
            <li><a href="#">tonyscarpetta@siracusa.it</a></li>
            <li><a href="#">tonyscarpetta@siracusa.it</a></li>
            <li><a href="#">tonyscarpetta@siracusa.it</a></li>
            <li><a href="#">tonyscarpetta@siracusa.it</a></li>
            <li><a href="#">tonyscarpetta@siracusa.it</a></li>
            <li><a href="#">tonyscarpetta@siracusa.it</a></li>
            <li><a href="#">tonyscarpetta@siracusa.it</a></li>
        </ul>
    </div>
</body>

</html>