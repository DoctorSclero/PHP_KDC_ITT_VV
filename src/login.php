<?php

session_start(); // Inizializzo la sessione. (Necessario per accedere alla variabile $_SESSION)

require_once __DIR__ . '/utils/nonce.php';
require_once __DIR__ . '/utils/database.php';

$ERROR = null; // Variabile per registrare eventuali errori di accesso

// Se l'utente è già loggato lo reindirizzo alla dashboard.
if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard.php');
    exit();
}

// Verifico se è stata inoltrata una richiesta di login
if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    // E' stata inoltrata una richiesta di login
    // pocedo nella gestione della stessa

    try {

        // Estrapolo i dati della richiesta
        // e li inserisco in un array associativo
        // $submitted_user
        $submitted_user["nonce"] = $_POST["nonce"] ?? null;
        $submitted_user["email"] = $_POST["email"] ?? null;
        $submitted_user["password"] = $_POST["password"] ?? null;

        // Verifico il nonce contro gli attacchi di replica
        if (empty($submitted_user["nonce"]) || !Nonce::verify($submitted_user["nonce"])) {
            // Il nonce non è valido potrebbe essere un attacco
            // di replica, lancio un eccezione
            throw new Exception("invalid_nonce");
        }

        // Ottengo i dati corrispondenti all'utente dal database
        $database_user = $connection->query(
            "SELECT id, salt, hash FROM users
            WHERE email = '{$submitted_user["email"]}'"
        )->fetch_assoc();

        // Verifico che l'utente sia registrato nel KDC
        if (empty($database_user)) {
            // L'utente non è registrato, informo l'utente
            // attraverso un'eccezione
            throw new Exception("account_not_found");
        }

        $submitted_user["hash"] = hash('sha256', $submitted_user["password"] . $database_user["salt"]);

        // Verifico che gli hash combacino
        if ($submitted_user["hash"] !== $database_user["hash"]) {
            // Gli hash non combaciano, quindi la password
            // è errata, notifico l'utente
            throw new Exception("wrong_password");
        }

        // La password è corretta e l'utente è loggato
        // con successo, salvo l'id utente nella sessione
        // e lo reindirizzo alla dashboard
        $_SESSION["user_id"] = $database_user["id"];
        header("Location: /dashboard.php");

    } catch (Exception $e) {
        // Salvo il messaggio di errore in una 
        // variabile per visualizzarlo nella pagina
        $ERROR = $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITT KDC</title>
    <link rel="shortcut icon" href="./assets/stemma_IISVV.png" type="image/x-icon">
    <link rel="stylesheet" href="/css/style.css">
</head>

<body>
    <div>
        <a href="/index.php">Indietro</a>

        <h1>Login</h1>

        <?php
        // Controllo se l'utente è stato appena registrato.
        if (isset($_GET["success"]) && $_GET["success"] == "account_created") {
            echo '<div class="success">Account creato con successo!</div>';
        }

        // Controllo se c'è qualche messaggio di errore da visualizzare.
        if (isset($ERROR)) {
            switch ($ERROR) {
                case 'invalid_nonce':
                    echo '<div class="error">Il nonce non è valido!</div>';
                    break;
                case 'account_not_found':
                    echo '<div class="error">Account non trovato!</div>';
                    break;
                case 'wrong_password':
                    echo '<div class="error">Password errata!</div>';
                    break;
            }
        }

        ?>

        <form method="POST">
            <!--
                Per rendere sicuro il form dalla da attacchi di replica facciamo uso di un nonce.
                Lo facciamo inserire dal server all'interno del form in modo che ci venga restituito
                al momento della submit. Se il nonce non corrisponde a quello generato dal server
                allora il form non viene accettato in quanto potrebbe essere una replica di una richiesta
                effettuata in precedenza. (Attacco di replica)
            -->
            <input type="hidden" name="nonce" value="<?php echo Nonce::generate_and_store(); ?>">
            <input type="email" name="email" id="email" placeholder="Email">
            <input type="password" name="password" id="password" placeholder="Password">
            <input type="submit" value="Login">
        </form>
    </div>
</body>

</html>