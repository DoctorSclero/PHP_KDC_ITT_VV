<?php

session_start();

if (isset($_SESSION['user_email'])) {
    header('Location: /dashboard.php');
}

require_once './vendor/nonce.php';

if (isset($_POST['nonce'])) {
    if (Nonce::verify($_POST['nonce'])) {
        // Il nonce è valido la richiesta non è riprodotta quindi procedo.
        require_once './vendor/database.php';

        $submitted_email = $_POST['email'];
        $submitted_password = $_POST['password'];

        // Prima di fare le query sanifico i dati per evitare attacchi di SQL Injection
        // per farlo uso un prepared statement di mysqli.
        $query = $connection->prepare("SELECT email, salt, hash FROM users WHERE email = ?");
        // Bindiamo i parametri al prepared statement.
        $query->bind_param("s", $submitted_email);
        // Eseguiamo la query per ottenere i risultati
        $result = $connection->query($query);
        
        // Se la ricerca ha prodotto risultati allora esiste un account
        // associato all'email inserita.
        
        if ($result->num_rows > 0) {
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
                header('Location: /login.php?error=wrong_password');
            }
        } else {
            // Se la ricerca non ha prodotto risultati allora non esiste un account
            // associato all'email inserita.
            // E notifico alla pagina che la password è sbagliata per visualizzare un errore.
            // Reindirizzo l'utente alla pagina di login.
            header('Location: /login.php?error=account_not_found');
        }
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <div class="vh-100 bg-light">
        <div class="h-100 d-flex justify-content-center align-items-center">
            <div class="content text-center">
                <div class="mb-3">
                    <h1>Login</h1>
                </div>    
                <form method="POST">
                    <!--
                        Per rendere sicuro il form dalla da attacchi di replica facciamo uso di un nonce.
                        Lo facciamo inserire dal server all'interno del form in modo che ci venga restituito
                        al momento della submit. Se il nonce non corrisponde a quello generato dal server
                        allora il form non viene accettato in quanto potrebbe essere una replica di una richiesta
                        effettuata in precedenza. (Attacco di replica)
                    -->
                    <input type="hidden" name="nonce" value="<?php echo Nonce::generate_and_store(); ?>">
                    <div class="mb-3">
                        <input type="email" class="form-control" name="" id="" placeholder="Email" title="Email">
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" name="" id="" placeholder="Password" title="Password">
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                    <a class="btn btn-secondary" href="index.php" role="button">Back</a>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
</body>

</html>