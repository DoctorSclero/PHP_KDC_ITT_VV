<?php

session_start(); // Inizializzo la sessione. (Necessario per accedere alla variabile $_SESSION)

require_once __DIR__ . '/utils/nonce.php';
require_once __DIR__ . '/utils/database.php';

$ERROR = null; // Variabile per registrare eventuali errori di accesso

// Se l'utente è già loggato lo reindirizzo alla dashboard.
if (isset($_SESSION['user_email'])) {
    header('Location: /dashboard.php');
}

// Verifico che la richiesta non sia riprodotta
if (isset($_POST['nonce'])) {

    if (Nonce::verify($_POST['nonce'])) {
        // Il nonce è valido la richiesta non è riprodotta quindi procedo.

        $submitted_email = $_POST['email'];
        $submitted_password = $_POST['password'];

        // Verifico che non ci sia un account già registrato con la stessa email.
        $result = $connection->query("SELECT email, salt, hash FROM users WHERE email = '$submitted_email'");
        
        // Se la ricerca ha prodotto risultati allora esiste un account
        // associato all'email inserita.
        
        if ($result->num_rows> 0) {
            // Estraggo i dati dell'utente.
            $db_stored_user = $result->fetch_assoc();
            // Estraggo il salt e l'hash.
            $db_user_salt = $db_stored_user['salt'];
            $db_user_hash = $db_stored_user['hash'];
            // Genero l'hash della password inserita dall'utente insieme al sale.
            $password_hash = hash('sha256', $submitted_password . $db_user_salt);
            // Verifico che l'hash della password inserita dall'utente
            // sia uguale a quello salvato nel database.
            if ($password_hash === $db_user_hash) {
                // Se l'hash è uguale allora l'utente ha inserito la password corretta.
                // Imposto l'id dell'utente nella sessione.
                $_SESSION['user_email'] = $db_stored_user['email'];
                // Ora l'utente è loggato quindi posso reindirizzarlo alla dashboard.
                header('Location: /dashboard.php');
            } else {
                // Se l'hash non corrisponde allora l'utente ha inserito la password sbagliata.
                // E notifico alla pagina che la password è sbagliata per visualizzare un errore.
                // Reindirizzo l'utente alla pagina di login.
                $ERROR = 'wrong_password';
            }
        } else {
            // Se la ricerca non ha prodotto risultati allora non esiste un account
            // associato all'email inserita.
            // E notifico alla pagina che la password è sbagliata per visualizzare un errore.
            // Reindirizzo l'utente alla pagina di login.
            $ERROR = 'account_not_found';
        }
    } else {
        // Il nonce non è valido quindi la richiesta è riprodotta.
        // Reindirizzo l'utente alla pagina di login.
        $ERROR = 'replay_attack';
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
                case 'replay_attack':
                    echo '<div class="error">Richiesta riprodotta!</div>';
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