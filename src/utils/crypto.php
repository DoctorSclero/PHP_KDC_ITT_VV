<?php 

/**
 * Questa funzione utilizza l'algoritmo AES-256 con la modalità couter
 * per cifrare il messaggio passato come parametro con la chiave passata come parametro.
 * @param mixed $key La chiave di cifratura
 * @param mixed $message Il messaggio da cifrare
 * @return string Il messaggio cifrato
 */
function encryptAES_CTR($key, $message) {
    $cipher = "aes-256-ctr";
    $iv = openssl_random_pseudo_bytes(16);
    
    $encryptedMessage = openssl_encrypt($message, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    
    $result = base64_encode($iv . $encryptedMessage);

    return $result;
}

/**
 * Questa funzione utilizza l'algoritmo AES-256 con la modalità couter
 * per decifrare il messaggio passato come parametro con la chiave passata come parametro.
 * @param mixed $key La chiave di cifratura
 * @param mixed $encryptedMessage Il messaggio da decifrare
 * @return string Il messaggio decifrato se la chiave è corretta altrimenti testo senza senso
 */
function decryptAES_CTR($key, $encryptedMessage) {
    $cipher = "aes-256-ctr";
    $encryptedMessage = base64_decode($encryptedMessage);
    $iv = substr($encryptedMessage, 0, 16);
    $encryptedMessage = substr($encryptedMessage, 16);
    
    $decryptedMessage = openssl_decrypt($encryptedMessage, $cipher, $key, OPENSSL_RAW_DATA, $iv);

    return $decryptedMessage;
}

?>