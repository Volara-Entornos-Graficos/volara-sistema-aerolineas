<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

$pageTitle = 'Inicio';
$novedades = [];
$aeropuertos = ['ROS', 'AEP', 'EZE', 'COR', 'BRC', 'MAD', 'MIA', 'GRU'];

try {
    $db = getDB();

    $stmt = $db->query('SELECT titulo, contenido, created_at FROM novedades WHERE activa = 1 ORDER BY created_at DESC LIMIT 3');
    $novedades = $stmt->fetchAll();

    $stmt = $db->query(
        "SELECT v.*, a.nombre AS aerolinea_nombre, a.codigo AS aerolinea_codigo
         FROM vuelos v
         JOIN aerolineas a ON a.id = v.aerolinea_id
         WHERE v.estado = 'programado' AND v.fecha_salida > NOW()
         ORDER BY v.fecha_salida ASC LIMIT 4"
    );
    $vuelosDestacados = $stmt->fetchAll();
} catch (PDOException $e) {
    $vuelosDestacados = [];
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>

<?php $flash = getFlash(); if ($flash): ?>
<div class="container mt-3">
    <div class="volara-alert alert-<?= e($flash['type']) ?>" role="alert">
        <?= e($flash['message']) ?>
    </div>
</div>
<?php endif; ?>

<!-- Hero -->
<section class="hero" aria-labelledby="hero-title">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 id="hero-title" class="hero-title">
                    Tu próximo destino<br><span>comienza aquí.</span>
                </h1>
                <p class="hero-subtitle">
                    Buscá, compará y reservá vuelos de forma simple. VOLARA conecta pasajeros con las mejores aerolíneas.
                </p>
            </div>
        </div>

        <!-- Buscador -->
        <div class="search-box" role="search" aria-label="Buscador de vuelos">
            <form action="<?= url('pages/publico/resultados.php') ?>" method="GET" id="searchForm" novalidate>
                <div class="trip-toggle" role="group" aria-label="Tipo de viaje">
                    <button type="button" class="trip-toggle-btn active" data-trip="ida" aria-pressed="true">
                        Solo ida
                    </button>
                    <button type="button" class="trip-toggle-btn" data-trip="ida_vuelta" aria-pressed="false">
                        Ida y vuelta
                    </button>
                </div>
                <input type="hidden" name="tipo_viaje" id="tipoViaje" value="ida">

                <div class="search-fields">
                    <div class="form-group mb-0">
                        <label class="volara-label" for="origen">Origen</label>
                        <input type="text" class="volara-input" id="origen" name="origen"
                               placeholder="Ciudad o aeropuerto" required
                               list="aeropuertos-list" autocomplete="off"
                               aria-describedby="origen-error">
                        <div class="form-error" id="origen-error"></div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="volara-label" for="destino">Destino</label>
                        <input type="text" class="volara-input" id="destino" name="destino"
                               placeholder="Ciudad o aeropuerto" required
                               list="aeropuertos-list" autocomplete="off"
                               aria-describedby="destino-error">
                        <div class="form-error" id="destino-error"></div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="volara-label" for="fecha_ida">Fecha de ida</label>
                        <input type="date" class="volara-input" id="fecha_ida" name="fecha_ida"
                               required min="<?= date('Y-m-d') ?>"
                               aria-describedby="fecha_ida-error">
                        <div class="form-error" id="fecha_ida-error"></div>
                    </div>

                    <div class="form-group mb-0" id="fechaVueltaGroup" style="display:none">
                        <label class="volara-label" for="fecha_vuelta">Fecha de vuelta</label>
                        <input type="date" class="volara-input" id="fecha_vuelta" name="fecha_vuelta"
                               min="<?= date('Y-m-d') ?>"
                               aria-describedby="fecha_vuelta-error">
                        <div class="form-error" id="fecha_vuelta-error"></div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="volara-label" for="pasajeros">Pasajeros</label>
                        <select class="volara-select" id="pasajeros" name="pasajeros" required>
                            <?php for ($i = 1; $i <= 9; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?> pasajero<?= $i > 1 ? 's' : '' ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label class="volara-label" for="clase">Clase</label>
                        <select class="volara-select" id="clase" name="clase">
                            <option value="">Todas</option>
                            <option value="economica">Económica</option>
                            <option value="premium">Premium</option>
                            <option value="business">Business</option>
                        </select>
                    </div>

                    <div class="search-btn-wrap">
                        <button type="submit" class="btn btn-volara btn-volara-lg w-100">
                            <i class="bi bi-search"></i> Buscar vuelos
                        </button>
                    </div>
                </div>
            </form>

            <datalist id="aeropuertos-list">
                <option value="Rosario (ROS)">
                <option value="Buenos Aires Aeroparque (AEP)">
                <option value="Buenos Aires Ezeiza (EZE)">
                <option value="Córdoba (COR)">
                <option value="Bariloche (BRC)">
                <option value="Madrid (MAD)">
                <option value="Miami (MIA)">
                <option value="São Paulo (GRU)">
            </datalist>
        </div>
    </div>
</section>

<!-- Vuelos destacados -->
<?php if (!empty($vuelosDestacados)): ?>
<section class="section" aria-labelledby="vuelos-title">
    <div class="container">
        <h2 id="vuelos-title" class="section-title">Vuelos disponibles</h2>
        <p class="section-subtitle">Los próximos vuelos con asientos disponibles</p>

        <div class="row g-4">
            <?php foreach ($vuelosDestacados as $vuelo): ?>
            <div class="col-lg-6">
                <article class="flight-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-muted small"><?= e($vuelo['aerolinea_nombre']) ?></span>
                            <span class="volara-badge badge-neutral ms-2"><?= e($vuelo['codigo']) ?></span>
                        </div>
                        <span class="volara-badge badge-neutral"><?= ucfirst(e($vuelo['clase'])) ?></span>
                    </div>

                    <div class="flight-route">
                        <div class="flight-airport">
                            <div class="code"><?= e($vuelo['origen_codigo']) ?></div>
                            <div class="time"><?= formatTime($vuelo['fecha_salida']) ?></div>
                            <div class="city"><?= e($vuelo['origen']) ?></div>
                        </div>

                        <div class="flight-line">
                            <div class="duration"><?= flightDuration($vuelo['fecha_salida'], $vuelo['fecha_llegada']) ?></div>
                            <div class="plane-icon"><i class="bi bi-airplane"></i></div>
                        </div>

                        <div class="flight-airport">
                            <div class="code"><?= e($vuelo['destino_codigo']) ?></div>
                            <div class="time"><?= formatTime($vuelo['fecha_llegada']) ?></div>
                            <div class="city"><?= e($vuelo['destino']) ?></div>
                        </div>
                    </div>

                    <div class="flight-meta">
                        <div class="flight-meta-item">
                            <i class="bi bi-calendar3 me-1"></i>
                            <?= formatDate($vuelo['fecha_salida']) ?>
                        </div>
                        <div class="flight-meta-item">
                            <i class="bi bi-person me-1"></i>
                            <strong><?= (int)$vuelo['asientos_disponibles'] ?></strong> asientos
                        </div>
                        <div class="flight-price">
                            <div class="amount"><?= formatPrice((float)$vuelo['precio']) ?></div>
                        </div>
                        <a href="<?= url('pages/publico/buscar.php') ?>"
                           class="btn btn-volara btn-volara-sm">
                            Buscar este vuelo
                        </a>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="<?= url('pages/publico/buscar.php') ?>" class="btn btn-volara-outline">
                Ver todos los vuelos <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Novedades -->
<?php if (!empty($novedades)): ?>
<section class="section section-gray" aria-labelledby="novedades-title">
    <div class="container">
        <h2 id="novedades-title" class="section-title">Novedades</h2>
        <p class="section-subtitle">Lo último en VOLARA y el mundo de la aviación</p>

        <div class="row g-4">
            <?php foreach ($novedades as $novedad): ?>
            <div class="col-md-4">
                <article class="news-card">
                    <div class="news-card-body">
                        <div class="news-card-date">
                            <i class="bi bi-calendar3 me-1"></i>
                            <?= formatDate($novedad['created_at']) ?>
                        </div>
                        <h3><?= e($novedad['titulo']) ?></h3>
                        <p><?= e(mb_strimwidth($novedad['contenido'], 0, 120, '...')) ?></p>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>
<?php endif; ?>

<!-- Features -->
<section class="section" aria-labelledby="features-title">
    <div class="container">
        <h2 id="features-title" class="section-title text-center">¿Por qué VOLARA?</h2>
        <p class="section-subtitle text-center mx-auto" style="max-width:500px">
            Una plataforma pensada para simplificar tu experiencia de viaje
        </p>

        <div class="row g-4">
            <div class="col-md-4 text-center">
                <div class="stat-icon stat-icon-red mx-auto mb-3" style="width:56px;height:56px;font-size:1.5rem;border-radius:16px">
                    <i class="bi bi-search"></i>
                </div>
                <h3 class="h5">Búsqueda inteligente</h3>
                <p class="text-muted small">Encontrá el vuelo ideal filtrando por origen, destino, fecha y clase.</p>
            </div>
            <div class="col-md-4 text-center">
                <div class="stat-icon stat-icon-red mx-auto mb-3" style="width:56px;height:56px;font-size:1.5rem;border-radius:16px">
                    <i class="bi bi-grid-3x3"></i>
                </div>
                <h3 class="h5">Selección de asientos</h3>
                <p class="text-muted small">Elegí tu asiento preferido con nuestra interfaz gráfica interactiva.</p>
            </div>
            <div class="col-md-4 text-center">
                <div class="stat-icon stat-icon-red mx-auto mb-3" style="width:56px;height:56px;font-size:1.5rem;border-radius:16px">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h3 class="h5">Reserva segura</h3>
                <p class="text-muted small">Gestioná tus reservas, consultá el historial y cancelá con facilidad.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
