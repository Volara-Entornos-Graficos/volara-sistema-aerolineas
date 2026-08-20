<?php
/**
 * Configuración general de VOLARA
 */

define('APP_NAME', 'VOLARA');
define('APP_TAGLINE', 'Tu próximo destino comienza aquí.');
define('APP_URL', '/volara-sistema-aerolineas');
define('APP_ROOT', dirname(__DIR__));
define('APP_VERSION', '1.0.0');

define('ITEMS_PER_PAGE', 10);
define('CANCELACION_HORAS', 72);

date_default_timezone_set('America/Argentina/Buenos_Aires');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
