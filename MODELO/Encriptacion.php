<?php

/**
 * Clase para cifrado simétrico reversible (AES-256-CBC con IV fijo).
 * 
 * IMPORTANTE: Se usa IV fijo para que el mismo correo siempre produzca
 * el mismo texto cifrado, lo que permite buscar por correo en la BD
 * (WHERE correo = :correo_encriptado).
 * 
 * NO usar este método para datos que requieran máxima seguridad con IV aleatorio.
 * Para contraseñas usar password_hash() / password_verify() (más seguro, irreversible).
 */
class Encriptacion
{
    // Clave de 32 bytes (256 bits) — cámbiala por una frase secreta propia
    private static $clave  = 'RefaccionariaMTY_2026_SecKey32!!';

    // IV fijo de 16 bytes (necesario para que el cifrado sea determinístico y buscable)
    private static $iv     = 'RefacMTY_IV16By!';

    private static $metodo = 'AES-256-CBC';

    /**
     * Encripta un texto plano y devuelve la cadena cifrada en base64.
     */
    public static function encriptar($texto): string
    {
        if (empty($texto)) return '';
        $cifrado = openssl_encrypt($texto, self::$metodo, self::$clave, 0, self::$iv);
        return $cifrado !== false ? $cifrado : $texto;
    }

    /**
     * Desencripta una cadena cifrada en base64 y devuelve el texto plano.
     * Si falla (p.ej. dato ya estaba sin encriptar), devuelve el valor original.
     */
    public static function desencriptar($textoCifrado): string
    {
        if (empty($textoCifrado)) return '';
        $plano = openssl_decrypt($textoCifrado, self::$metodo, self::$clave, 0, self::$iv);
        return $plano !== false ? $plano : $textoCifrado;
    }
}
?>
