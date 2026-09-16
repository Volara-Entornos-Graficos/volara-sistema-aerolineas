/**
 * VOLARA — JavaScript principal
 */

function initApp() {
    initNavbar();
    initSearchForm();
    initDeleteConfirmations();
    initFormValidation();
    initPasswordToggles();
    initCeoRegistration();
    initSeatSelection();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}

/* ─── Navbar scroll effect ──────────────────────────────── */
function initNavbar() {
    const navbar = document.querySelector('.volara-navbar');
    if (!navbar) return;

    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 10);
    }, { passive: true });
}

/* ─── Search form: trip toggle + validation ─────────────── */
function initSearchForm() {
    const form = document.getElementById('searchForm');
    if (!form) return;

    const toggleBtns = form.querySelectorAll('.trip-toggle-btn');
    const tipoInput = document.getElementById('tipoViaje');
    const vueltaGroup = document.getElementById('fechaVueltaGroup');
    const vueltaInput = document.getElementById('fecha_vuelta');
    const idaInput = document.getElementById('fecha_ida');

    toggleBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            toggleBtns.forEach(b => {
                b.classList.remove('active');
                b.setAttribute('aria-pressed', 'false');
            });
            btn.classList.add('active');
            btn.setAttribute('aria-pressed', 'true');

            const isRoundTrip = btn.dataset.trip === 'ida_vuelta';
            tipoInput.value = btn.dataset.trip;
            vueltaGroup.style.display = isRoundTrip ? '' : 'none';
            vueltaInput.required = isRoundTrip;
            if (!isRoundTrip) vueltaInput.value = '';
        });
    });

    idaInput?.addEventListener('change', () => {
        if (vueltaInput) vueltaInput.min = idaInput.value;
    });

    form.addEventListener('submit', (e) => {
        if (!validateSearchForm(form)) {
            e.preventDefault();
            form.querySelector('.is-invalid')?.focus();
        }
    });
}

function validateSearchForm(form) {
    let valid = true;
    const origen = form.querySelector('#origen');
    const destino = form.querySelector('#destino');
    const fechaIda = form.querySelector('#fecha_ida');
    const fechaVuelta = form.querySelector('#fecha_vuelta');
    const tipoViaje = form.querySelector('#tipoViaje');

    clearFieldError(origen);
    clearFieldError(destino);
    clearFieldError(fechaIda);
    clearFieldError(fechaVuelta);

    if (!origen.value.trim()) {
        setFieldError(origen, 'Ingresá un origen');
        valid = false;
    }
    if (!destino.value.trim()) {
        setFieldError(destino, 'Ingresá un destino');
        valid = false;
    }
    const normalizeAirport = value => value.trim().toLowerCase().replace(/\s*\([^)]*\)$/, '');
    if (origen.value.trim() && destino.value.trim() &&
        normalizeAirport(origen.value) === normalizeAirport(destino.value)) {
        setFieldError(destino, 'El destino debe ser diferente al origen');
        valid = false;
    }
    if (!fechaIda.value) {
        setFieldError(fechaIda, 'Seleccioná una fecha de ida');
        valid = false;
    }
    if (tipoViaje.value === 'ida_vuelta') {
        if (!fechaVuelta.value) {
            setFieldError(fechaVuelta, 'Seleccioná una fecha de vuelta');
            valid = false;
        } else if (fechaVuelta.value < fechaIda.value) {
            setFieldError(fechaVuelta, 'La vuelta debe ser posterior a la ida');
            valid = false;
        }
    }

    return valid;
}

function setFieldError(input, message) {
    input.classList.add('is-invalid');
    input.setAttribute('aria-invalid', 'true');
    const errorEl = document.getElementById(input.id + '-error');
    if (errorEl) errorEl.textContent = message;
}

function clearFieldError(input) {
    input.classList.remove('is-invalid');
    input.setAttribute('aria-invalid', 'false');
    const errorEl = document.getElementById(input.id + '-error');
    if (errorEl) errorEl.textContent = '';
}

/* ─── Generic form validation ───────────────────────────── */
function initFormValidation() {
    document.querySelectorAll('[data-validate]').forEach(form => {
        form.addEventListener('submit', (e) => {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                const firstInvalid = form.querySelector(':invalid');
                firstInvalid?.focus();
            }
            form.classList.add('was-validated');
        });
    });
}

/* ─── Delete confirmations ──────────────────────────────── */
function initDeleteConfirmations() {
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            const message = el.dataset.confirm || '¿Estás seguro de que deseas eliminar este registro?';
            if (!confirm(message)) e.preventDefault();
        });
    });
}

/* ─── Seat map (used in seat selection page) ────────────── */
function initSeatMap(containerId, occupiedSeats, onSelect) {
    const container = document.getElementById(containerId);
    if (!container) return;

    let selectedSeat = null;

    container.querySelectorAll('.seat:not(.occupied)').forEach(seat => {
        seat.addEventListener('click', () => {
            const label = seat.dataset.seat;
            if (seat.classList.contains('occupied')) return;

            if (selectedSeat) {
                const prev = container.querySelector(`.seat[data-seat="${selectedSeat}"]`);
                if (prev) prev.classList.remove('selected');
            }

            if (selectedSeat === label) {
                selectedSeat = null;
                onSelect(null);
            } else {
                seat.classList.add('selected');
                selectedSeat = label;
                onSelect(label, seat.dataset.id || null);
            }
        });

        seat.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                seat.click();
            }
        });
    });

    occupiedSeats.forEach(label => {
        const seat = container.querySelector(`.seat[data-seat="${label}"]`);
        if (seat) {
            seat.classList.add('occupied');
            seat.setAttribute('tabindex', '-1');
            seat.setAttribute('aria-disabled', 'true');
        }
    });
}
/* ─── Mostrar / ocultar contraseñas ─────────────────────── */
/* ─── Mostrar / ocultar contraseñas ─────────────────────── */
function initPasswordToggles() {
    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) return;

        const button = event.target.closest('[data-toggle-password]');
        if (!button) return;

        const input = document.getElementById(button.dataset.togglePassword);
        const icon = button.querySelector('i');
        if (!input || !icon) return;

        icon.style.pointerEvents = 'none';

        const mostrar = input.type === 'password';
        input.type = mostrar ? 'text' : 'password';
        icon.classList.toggle('bi-eye', !mostrar);
        icon.classList.toggle('bi-eye-slash', mostrar);
        button.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
        button.setAttribute('aria-pressed', String(mostrar));
    });
}

function initCeoRegistration() {
    const roleInput = document.getElementById('rol');
    const airlineGroup = document.getElementById('aerolineaGroup');
    const airlineInput = document.getElementById('aerolinea_id');

    if (!roleInput || !airlineGroup || !airlineInput) return;

    const updateVisibility = () => {
        const isCeo = roleInput.value === 'ceo';
        airlineGroup.hidden = !isCeo;
        airlineInput.required = isCeo;
        if (!isCeo) airlineInput.value = '';
    };

    roleInput.addEventListener('change', updateVisibility);
    updateVisibility();
}

function initSeatSelection() {
    const container = document.getElementById('seatMap');
    if (!container) return;

    const occupiedSeats = JSON.parse(container.dataset.occupied || '[]');
    const selectedLabel = document.getElementById('selectedSeatLabel');
    const selectedInput = document.getElementById('selectedSeatInput');
    const selectedId = document.getElementById('selectedSeatId');
    const continueButton = document.getElementById('continueSeatButton');

    initSeatMap('seatMap', occupiedSeats, (label, id) => {
        if (selectedLabel) selectedLabel.textContent = label || 'Elegí un asiento';
        if (selectedInput) selectedInput.value = label || '';
        if (selectedId) selectedId.value = id || '';
        if (continueButton) continueButton.disabled = !label;
    });
}
window.initSeatMap = initSeatMap;
