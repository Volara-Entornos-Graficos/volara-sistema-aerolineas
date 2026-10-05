<?php
/**
 * Configuración general de VOLARA
 */

define('APP_NAME', function_exists('env') ? (string) env('APP_NAME', 'VOLARA') : 'VOLARA');
define('APP_TAGLINE', 'Tu próximo destino comienza aquí.');
define('APP_URL', rtrim((string) (function_exists('env') ? env('APP_URL', 'http://localhost/volara-sistema-aerolineas') : 'http://localhost/volara-sistema-aerolineas'), '/'));
define('APP_ROOT', dirname(__DIR__));
define('APP_VERSION', '1.0.0');
define('MAIL_FROM', function_exists('env') ? (string) env('MAIL_USERNAME', 'volara.aerolinea@gmail.com') : 'volara.aerolinea@gmail.com');

define('ITEMS_PER_PAGE', 10);
define('CANCELACION_HORAS', (int) (function_exists('env') ? env('CANCELACION_HORAS', 72) : 72));

date_default_timezone_set('America/Argentina/Buenos_Aires');

if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}
