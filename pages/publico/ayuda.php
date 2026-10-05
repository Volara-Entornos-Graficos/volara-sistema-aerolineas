<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

$pageTitle = 'Ayuda y preguntas frecuentes';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main id="contenido-principal" tabindex="-1">
    <section class="page-header">
        <div class="container">
            <h1>Ayuda</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('index.php') ?>">Inicio</a></li>
                    <li class="breadcrumb-item active">Ayuda</li>
                </ol>
            </nav>
        </div>
    </section>
    <section class="section">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <article class="volara-card mb-4">
                        <h2 class="h4">Cómo reservar un vuelo</h2>
                        <ol>
                            <li>Buscá origen, destino y fecha desde Inicio o Buscar vuelos.</li>
                            <li>Abrí el detalle del vuelo y, si estás autenticado como pasajero, elegí un asiento.</li>
                            <li>La reserva queda <strong>pendiente de pago</strong> y ocupa el asiento.</li>
                            <li>En Mis reservas usá <strong>Confirmar pago</strong> para completar la compra.</li>
                        </ol>
                    </article>
                    <article class="volara-card mb-4">
                        <h2 class="h4">Selección de asiento</h2>
                        <p>El mapa muestra cada asiento con su código (por ejemplo 12A). Podés usar el mouse o el teclado:</p>
                        <ul>
                            <li><strong>Disponible:</strong> se puede seleccionar. El botón está habilitado.</li>
                            <li><strong>Ocupado:</strong> aparece tachado y no se puede elegir.</li>
                            <li><strong>Seleccionado:</strong> es el asiento que vas a reservar. El resumen lo confirma por texto.</li>
                        </ul>
                    </article>
                    <article class="volara-card mb-4">
                        <h2 class="h4">Estados de una reserva</h2>
                        <ul>
                            <li><strong>Pendiente de pago:</strong> el asiento está reservado, falta confirmar el pago.</li>
                            <li><strong>Confirmada:</strong> la compra está realizada.</li>
                            <li><strong>Cancelada:</strong> el asiento se libera. Solo se puede cancelar hasta <?= (int) CANCELACION_HORAS ?> horas antes de la salida.</li>
                        </ul>
                    </article>
                    <article class="volara-card">
                        <h2 class="h4">Preguntas frecuentes</h2>
                        <h3 class="h6">¿Puedo cancelar después de pagar?</h3>
                        <p>Sí, si faltan al menos <?= (int) CANCELACION_HORAS ?> horas para la salida del vuelo.</p>
                        <h3 class="h6">¿Por qué no puedo iniciar sesión después de registrarme?</h3>
                        <p>Primero tenés que verificar el email con el enlace que enviamos. Si te registraste como CEO, además hace falta la aprobación del administrador.</p>
                        <h3 class="h6">¿Cómo se aplican las promociones?</h3>
                        <p>Solo las promociones vigentes y aprobadas de la aerolínea del vuelo descuentan el precio al reservar.</p>
                    </article>
                </div>
                <aside class="col-lg-4">
                    <div class="volara-card">
                        <h2 class="h5">¿Necesitás más ayuda?</h2>
                        <p>Escribinos desde el formulario de contacto. También podés revisar el mapa del sitio para ubicar cada sección.</p>
                        <a class="btn btn-volara w-100 mb-2" href="<?= url('pages/publico/contacto.php') ?>">Ir a contacto</a>
                        <a class="btn btn-volara-outline w-100" href="<?= url('pages/publico/mapa-sitio.php') ?>">Mapa del sitio</a>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
