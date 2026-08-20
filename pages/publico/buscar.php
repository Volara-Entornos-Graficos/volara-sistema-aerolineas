<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

$pageTitle = 'Buscar vuelos';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>

<main>
    <div class="page-header">
        <div class="container">
            <h1>Buscar vuelos</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('index.php') ?>">Inicio</a></li>
                    <li class="breadcrumb-item active">Buscar vuelos</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="section">
        <div class="container">
            <div class="search-box">
                <form action="<?= url('pages/publico/resultados.php') ?>" method="GET" id="searchForm" novalidate>
                    <div class="trip-toggle" role="group" aria-label="Tipo de viaje">
                        <button type="button" class="trip-toggle-btn active" data-trip="ida" aria-pressed="true">Solo ida</button>
                        <button type="button" class="trip-toggle-btn" data-trip="ida_vuelta" aria-pressed="false">Ida y vuelta</button>
                    </div>
                    <input type="hidden" name="tipo_viaje" id="tipoViaje" value="ida">

                    <div class="search-fields">
                        <div class="form-group mb-0">
                            <label class="volara-label" for="origen">Origen</label>
                            <input type="text" class="volara-input" id="origen" name="origen" placeholder="Ciudad o aeropuerto" required list="aeropuertos-list">
                            <div class="form-error" id="origen-error"></div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="volara-label" for="destino">Destino</label>
                            <input type="text" class="volara-input" id="destino" name="destino" placeholder="Ciudad o aeropuerto" required list="aeropuertos-list">
                            <div class="form-error" id="destino-error"></div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="volara-label" for="fecha_ida">Fecha de ida</label>
                            <input type="date" class="volara-input" id="fecha_ida" name="fecha_ida" required min="<?= date('Y-m-d') ?>">
                            <div class="form-error" id="fecha_ida-error"></div>
                        </div>
                        <div class="form-group mb-0" id="fechaVueltaGroup" style="display:none">
                            <label class="volara-label" for="fecha_vuelta">Fecha de vuelta</label>
                            <input type="date" class="volara-input" id="fecha_vuelta" name="fecha_vuelta" min="<?= date('Y-m-d') ?>">
                            <div class="form-error" id="fecha_vuelta-error"></div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="volara-label" for="pasajeros">Pasajeros</label>
                            <select class="volara-select" id="pasajeros" name="pasajeros">
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
                    <option value="Rosario (ROS)"><option value="Buenos Aires Aeroparque (AEP)">
                    <option value="Buenos Aires Ezeiza (EZE)"><option value="Córdoba (COR)">
                    <option value="Bariloche (BRC)"><option value="Madrid (MAD)">
                </datalist>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
