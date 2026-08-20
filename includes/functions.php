<?php
/**
 * Funciones utilitarias de VOLARA
 */

function url(string $path = ''): string
{
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function formatPrice(float $amount): string
{
    return '$' . number_format($amount, 0, ',', '.');
}

function formatDate(string $date, string $format = 'd/m/Y'): string
{
    return date($format, strtotime($date));
}

function formatTime(string $datetime): string
{
    return date('H:i', strtotime($datetime));
}

function flightDuration(string $departure, string $arrival): string
{
    $diff = strtotime($arrival) - strtotime($departure);
    $hours = floor($diff / 3600);
    $minutes = floor(($diff % 3600) / 60);
    return $hours . ' h ' . $minutes . ' min';
}

function generateCode(string $prefix, int $length = 6): string
{
    return strtoupper($prefix . bin2hex(random_bytes($length / 2)));
}

function paginate(int $total, int $page, int $perPage = ITEMS_PER_PAGE): array
{
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;

    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'current'     => $page,
        'total_pages' => $totalPages,
        'offset'      => $offset,
        'has_prev'    => $page > 1,
        'has_next'    => $page < $totalPages,
    ];
}

function badgeClass(string $estado): string
{
    $map = [
        'pendiente'       => 'badge-pending',
        'pendiente_pago'  => 'badge-pending',
        'aprobada'        => 'badge-approved',
        'vigente'         => 'badge-approved',
        'confirmada'      => 'badge-confirmed',
        'activa'          => 'badge-confirmed',
        'programado'      => 'badge-confirmed',
        'denegada'        => 'badge-denied',
        'cancelada'       => 'badge-cancelled',
        'cancelado'       => 'badge-cancelled',
        'inactiva'        => 'badge-cancelled',
        'vencida'         => 'badge-cancelled',
        'completado'      => 'badge-neutral',
    ];
    return $map[$estado] ?? 'badge-neutral';
}

function estadoLabel(string $estado): string
{
    $map = [
        'pendiente'       => 'Pendiente',
        'pendiente_pago'  => 'Pendiente de pago',
        'aprobada'        => 'Aprobada',
        'vigente'         => 'Vigente',
        'confirmada'      => 'Confirmada',
        'activa'          => 'Activa',
        'programado'      => 'Programado',
        'denegada'        => 'Denegada',
        'cancelada'       => 'Cancelada',
        'cancelado'       => 'Cancelado',
        'inactiva'        => 'Inactiva',
        'vencida'         => 'Vencida',
        'completado'      => 'Completado',
    ];
    return $map[$estado] ?? ucfirst(str_replace('_', ' ', $estado));
}

function isActivePage(string $page): string
{
    $current = $_GET['page'] ?? 'inicio';
    return $current === $page ? 'active' : '';
}

function old(string $key, string $default = ''): string
{
    return e($_SESSION['old'][$key] ?? $default);
}

function clearOld(): void
{
    unset($_SESSION['old']);
}
