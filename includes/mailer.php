<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno si no están cargadas
if (!function_exists('env')) {
    require_once __DIR__ . '/../config/env.php';
}

function sendVolaraEmail(string $to, string $subject, string $body): bool
{
    $mail = new PHPMailer(true);

    try {
        // Configuración SMTP de Gmail (desde variables de entorno)
        $mail->isSMTP();
        $mail->Host       = env('MAIL_HOST', 'smtp.gmail.com');
        $mail->SMTPAuth   = true;
        $mail->Username   = env('MAIL_USERNAME', 'volara.aerolinea@gmail.com');

        // IMPORTANTE:
        // Las credenciales se cargan desde variables de entorno (.env)
        // NUNCA hardcodear contraseñas en archivos versionados
        $mail->Password   = env('MAIL_PASSWORD', '');

        $mail->SMTPSecure = env('MAIL_ENCRYPTION', 'tls') === 'tls' 
            ? PHPMailer::ENCRYPTION_STARTTLS 
            : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = (int) env('MAIL_PORT', 587);

        // Codificación
        $mail->CharSet = 'UTF-8';

        // Remitente
        $mail->setFrom(
            env('MAIL_USERNAME', 'volara.aerolinea@gmail.com'),
            env('APP_NAME', 'VOLARA')
        );

        // Destinatario
        $mail->addAddress($to);

        // Email HTML
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        // Texto alternativo
        $mail->AltBody = strip_tags($body);

        return $mail->send();

    } catch (Exception $e) {
        error_log(
            'Error enviando email VOLARA: ' . $mail->ErrorInfo
        );

        return false;
    }
}

function activationEmail(string $name, string $activationUrl): string
{
    return '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificá tu cuenta - VOLARA</title>
</head>

<body style="
    margin:0;
    padding:0;
    background-color:#f4f6f8;
    font-family:Arial, Helvetica, sans-serif;
    color:#263238;
">

    <table width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background-color:#f4f6f8; padding:40px 15px;">

        <tr>
            <td align="center">

                <!-- CONTENEDOR PRINCIPAL -->
                <table width="600" cellpadding="0" cellspacing="0" border="0"
                       style="
                            max-width:600px;
                            width:100%;
                            background-color:#ffffff;
                            border-radius:14px;
                            overflow:hidden;
                            box-shadow:0 4px 18px rgba(0,0,0,0.08);
                       ">

                    <!-- HEADER -->
                    <tr>
                        <td align="center"
                            style="
                                background:linear-gradient(135deg,#0d6efd,#084298);
                                padding:32px 25px;
                            ">

                            <div style="
                                font-size:32px;
                                font-weight:bold;
                                letter-spacing:2px;
                                color:#ffffff;
                            ">
                                VOLARA
                            </div>

                            <div style="
                                margin-top:8px;
                                font-size:14px;
                                color:#dbeafe;
                            ">
                                Volando hacia nuevos destinos
                            </div>

                        </td>
                    </tr>

                    <!-- CONTENIDO -->
                    <tr>
                        <td style="padding:40px 45px;">

                            <h1 style="
                                margin:0 0 20px 0;
                                font-size:28px;
                                line-height:1.3;
                                color:#172b4d;
                            ">
                                Verificá tu cuenta ✈️
                            </h1>

                            <p style="
                                margin:0 0 18px 0;
                                font-size:16px;
                                line-height:1.7;
                                color:#4b5563;
                            ">
                                Hola <strong>' . e($name) . '</strong>,
                            </p>

                            <p style="
                                margin:0 0 18px 0;
                                font-size:16px;
                                line-height:1.7;
                                color:#4b5563;
                            ">
                                Gracias por registrarte en <strong>VOLARA</strong>.
                                Para completar la creación de tu cuenta necesitamos
                                confirmar que esta dirección de email te pertenece.
                            </p>

                            <!-- BOTÓN -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="margin:30px 0;">

                                <tr>
                                    <td align="center">

                                        <a href="' . e($activationUrl) . '"
                                           style="
                                                display:inline-block;
                                                padding:15px 32px;
                                                background-color:#0d6efd;
                                                color:#ffffff;
                                                text-decoration:none;
                                                font-size:16px;
                                                font-weight:bold;
                                                border-radius:8px;
                                           ">
                                            Verificar mi email
                                        </a>

                                    </td>
                                </tr>

                            </table>

                            <!-- VENCIMIENTO -->
                            <div style="
                                background-color:#f8fafc;
                                border-left:4px solid #0d6efd;
                                padding:15px 18px;
                                margin:25px 0;
                                border-radius:5px;
                            ">

                                <p style="
                                    margin:0;
                                    font-size:14px;
                                    line-height:1.6;
                                    color:#475569;
                                ">
                                    <strong>Importante:</strong>
                                    este enlace de verificación es válido durante
                                    <strong>24 horas</strong>.
                                </p>

                            </div>

                            <p style="
                                margin:25px 0 0 0;
                                font-size:13px;
                                line-height:1.6;
                                color:#94a3b8;
                            ">
                                Si el botón no funciona, copiá y pegá el siguiente
                                enlace en tu navegador:
                            </p>

                            <p style="
                                margin:8px 0 0 0;
                                font-size:12px;
                                line-height:1.6;
                                word-break:break-all;
                                color:#64748b;
                            ">
                                ' . e($activationUrl) . '
                            </p>

                        </td>
                    </tr>

                    <!-- SEPARADOR -->
                    <tr>
                        <td style="padding:0 45px;">
                            <div style="
                                height:1px;
                                background-color:#e5e7eb;
                            "></div>
                        </td>
                    </tr>

                    <!-- SEGURIDAD -->
                    <tr>
                        <td style="padding:25px 45px;">

                            <p style="
                                margin:0;
                                font-size:13px;
                                line-height:1.6;
                                color:#64748b;
                            ">
                                ¿No creaste una cuenta en VOLARA?
                                Entonces podés ignorar este mensaje.
                                No se realizará ninguna acción sobre tu correo.
                            </p>

                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td align="center"
                            style="
                                background-color:#f8fafc;
                                padding:25px;
                            ">

                            <div style="
                                font-size:18px;
                                font-weight:bold;
                                color:#172b4d;
                                letter-spacing:1px;
                            ">
                                VOLARA
                            </div>

                            <p style="
                                margin:8px 0 0 0;
                                font-size:12px;
                                color:#94a3b8;
                            ">
                                Tu próximo destino comienza acá.
                            </p>

                            <p style="
                                margin:12px 0 0 0;
                                font-size:11px;
                                color:#cbd5e1;
                            ">
                                © ' . date('Y') . ' VOLARA. Todos los derechos reservados.
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>

    </table>

</body>
</html>';
}



function resetEmail(string $name, string $resetUrl): string
{
    return '<!doctype html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Restablecer contraseña</title>
    </head>

    <body style="
        margin: 0;
        padding: 30px;
        font-family: Arial, Helvetica, sans-serif;
        background-color: #f5f5f5;
    ">

        <div style="
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 10px;
        ">

            <h1>Restablecer contraseña</h1>

            <p>
                Hola ' . e($name) . ',
            </p>

            <p>
                Recibimos una solicitud para cambiar tu contraseña.
            </p>

            <p style="margin: 30px 0;">
                <a href="' . e($resetUrl) . '" style="
                    display: inline-block;
                    padding: 12px 24px;
                    background-color: #0d6efd;
                    color: #ffffff;
                    text-decoration: none;
                    border-radius: 6px;
                    font-weight: bold;
                ">
                    Crear una nueva contraseña
                </a>
            </p>

            <p>
                Este enlace vence en una hora.
                Si no solicitaste el cambio, ignorá este mensaje.
            </p>

            <p style="color: #666666; font-size: 13px;">
                Equipo VOLARA
            </p>

        </div>

    </body>
    </html>';
}

