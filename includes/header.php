<?php
/**
 * Header común — VOLARA
 */
$pageTitle = $pageTitle ?? APP_NAME;
$pageDescription = $pageDescription ?? 'Sistema de gestión y reserva de vuelos';
$extraCss = $extraCss ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <title><?= e($pageTitle) ?> — <?= APP_NAME ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/styles.css') ?>" rel="stylesheet">

    <?php foreach ($extraCss as $css): ?>
        <link href="<?= asset($css) ?>" rel="stylesheet">
    <?php endforeach; ?>
</head>
<body>
