<?php 

/**
 * Questa classe contiene funzioni per la gestione dei nonce.
 * @package 
 */
class Nonce {
    // Lunghezza del nonce.
    public static $NONCE_SIZE = 25;

    /**
     * Questa funzione genera un nonce di lunghezza pari a quella specificata dalla variabile $NONCE_SIZE.
     * @return string Nonce generato.
     */
    public static function generate() : string {
        $nonce = '';
        
        // Creo un dizionario di caratteri da cui estrarre i caratteri casuali.
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        // Calcolo la lunghezza del dizionario per limitare i numeri estratti casualmente.
        $charactersLength = strlen($characters);

        for ($i = 0; $i < self::$NONCE_SIZE; $i++) {
            // Estraggo un numero casuale compreso tra 0 e la lunghezza del dizionario.
            $nonce .= $characters[rand(0, $charactersLength - 1)];
        }

        // Ritorno il nonce generato.
        return $nonce;
    }

    /**
     * Questa funzione genera un nonce e lo salva nella sessione
     * per poterlo confrontare in seguito.
     * @return string nonce generato.
     */
    public static function generate_and_store() : string {
        // Genero un nonce.
        $nonce = self::generate();
        // Lo salvo nella sessione.
        $_SESSION['nonce'] = $nonce;
        // Ritorno il nonce generato.
        return $nonce;
    }

    /**
     * Questa funzione verifica che il nonce passato come parametro
     * sia uguale a quello generato e salvato nella sessione se non lo è
     * ritorna false.
     */
    public static function verify(string $nonce) : bool {
        // Verifico che il nonce sia stato generato.
        if (isset($_SESSION['nonce'])) {
            // Verifico che il nonce sia uguale a quello generato.
            if ($_SESSION['nonce'] === $nonce) {
                // Ritorno true.
                return true;
            }
        }

        // Ritorno false.
        return false;
    }
}

?>