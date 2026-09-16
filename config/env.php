<?php
/**
 * Cargar variables de entorno desde archivo .env
 * 
 * Este archivo debe incluirse al inicio de la aplicación
 * ANTES de cualquier otra configuración.
 */

/**
 * Cargar variables de entorno desde archivo .env
 * 
 * @param string $path Ruta al archivo .env
 * @return void
 */
function loadEnv(string $path = __DIR__ . '/../.env'): void
{
    if (!file_exists($path)) {
        // Si no existe .env, usar valores por defecto (desarrollo)
        // En producción, fallará explícitamente
        if (getenv('APP_ENV') === 'production') {
            throw new Exception('Archivo .env no encontrado en producción');
        }
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parsear KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remover comillas si existen
            if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                $value = substr($value, 1, -1);
            }

            // Establecer variable de entorno
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

/**
 * Obtener variable de entorno con valor por defecto
 * 
 * @param string $key Nombre de la variable
 * @param mixed $default Valor por defecto si no existe
 * @return mixed
 */
function env(string $key, $default = null)
{
    $value = getenv($key);
    
    if ($value === false) {
        return $default;
    }

    // Convertir strings booleanos
    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'empty':
        case '(empty)':
            return '';
        case 'null':
        case '(null)':
            return null;
        default:
            return $value;
    }
}

// Cargar archivo .env automáticamente
loadEnv();
