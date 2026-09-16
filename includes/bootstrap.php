<?php
/**
 * Bootstrap — carga config, DB, funciones y auth
 * 
 * IMPORTANTE: El env.php DEBE cargarse PRIMERO
 * para que todas las variables de entorno estén disponibles
 */

// Cargar variables de entorno (.env)
require_once __DIR__ . '/../config/env.php';
loadEnv(__DIR__ . '/../.env');

// Cargar configuración
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mailer.php';
