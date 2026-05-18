/**
 * PNK Inmobiliaria — JavaScript Principal
 */

document.addEventListener('DOMContentLoaded', function() {
    initNavbar();
    initDropdowns();
    initRutValidation();
    initFormValidation();
    initImagePreview();
    initDeleteConfirmation();
});

/* ========== NAVBAR ========== */
function initNavbar() {
    const toggle = document.getElementById('navToggle');
    const links = document.getElementById('navLinks');
    if (toggle && links) {
        toggle.addEventListener('click', () => links.classList.toggle('active'));
        document.addEventListener('click', (e) => {
            if (!toggle.contains(e.target) && !links.contains(e.target)) {
                links.classList.remove('active');
            }
        });
    }
    // Navbar scroll effect
    window.addEventListener('scroll', () => {
        const nav = document.getElementById('navbar-main');
        if (nav) nav.classList.toggle('scrolled', window.scrollY > 50);
    });
}

/* ========== DROPDOWNS ========== */
function initDropdowns() {
    document.querySelectorAll('.nav-dropdown-toggle').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const menu = btn.nextElementSibling;
            document.querySelectorAll('.nav-dropdown-menu.show').forEach(m => {
                if (m !== menu) m.classList.remove('show');
            });
            menu.classList.toggle('show');
        });
    });
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.nav-dropdown')) {
            document.querySelectorAll('.nav-dropdown-menu.show').forEach(m => m.classList.remove('show'));
        }
    });
}

/* ========== RUT VALIDATION ========== */
function initRutValidation() {
    document.querySelectorAll('input[name="rut"]').forEach(input => {
        input.addEventListener('blur', function() {
            const valid = validarRut(this.value);
            this.classList.toggle('is-invalid', !valid && this.value.length > 0);
            this.classList.toggle('is-valid', valid);
        });
        input.addEventListener('input', function() {
            this.value = formatRut(this.value);
        });
    });
}

function formatRut(rut) {
    rut = rut.replace(/[^0-9kK]/g, '');
    if (rut.length > 1) {
        const dv = rut.slice(-1);
        let num = rut.slice(0, -1);
        num = num.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return num + '-' + dv;
    }
    return rut;
}

function validarRut(rut) {
    rut = rut.replace(/[.\s]/g, '');
    if (!/^[0-9]{7,8}-[0-9kK]{1}$/.test(rut)) return false;
    const [numero, dv] = rut.split('-');
    let suma = 0, factor = 2;
    for (let i = numero.length - 1; i >= 0; i--) {
        suma += parseInt(numero[i]) * factor;
        factor = factor === 7 ? 2 : factor + 1;
    }
    const resto = suma % 11;
    let dvCalc = 11 - resto;
    if (dvCalc === 11) dvCalc = '0';
    else if (dvCalc === 10) dvCalc = 'K';
    else dvCalc = String(dvCalc);
    return dv.toUpperCase() === dvCalc;
}

/* ========== FORM VALIDATION ========== */
function initFormValidation() {
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', function(e) {
            let valid = true;
            // Password match
            const pass = form.querySelector('input[name="password"]');
            const confirm = form.querySelector('input[name="password_confirm"]');
            if (pass && confirm && pass.value !== confirm.value) {
                confirm.classList.add('is-invalid');
                valid = false;
            }
            // Required fields
            form.querySelectorAll('[required]').forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    valid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            if (!valid) e.preventDefault();
        });
    });
}

/* ========== IMAGE PREVIEW ========== */
function initImagePreview() {
    document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
        input.addEventListener('change', function() {
            const container = document.getElementById(this.dataset.preview);
            if (!container) return;
            container.innerHTML = '';
            Array.from(this.files).forEach(file => {
                if (!file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.cssText = 'width:100px;height:100px;object-fit:cover;border-radius:8px;margin:4px;';
                    container.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    });
}

/* ========== DELETE CONFIRMATION ========== */
function initDeleteConfirmation() {
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm || '¿Está seguro de realizar esta acción?')) {
                e.preventDefault();
            }
        });
    });
}

/* ========== BUSCADOR AJAX ========== */
function buscarPropiedades() {
    const provincia = document.getElementById('filtro-provincia')?.value || '';
    const comuna = document.getElementById('filtro-comuna')?.value || '';
    const tipo = document.getElementById('filtro-tipo')?.value || '';
    const container = document.getElementById('resultados-busqueda');
    if (!container) return;

    container.innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-warning"></i><p class="mt-3">Buscando propiedades...</p></div>';

    fetch(`api/propiedades.php?action=buscar&provincia=${encodeURIComponent(provincia)}&comuna=${encodeURIComponent(comuna)}&tipo=${encodeURIComponent(tipo)}`)
        .then(r => r.json())
        .then(data => {
            if (!data.length) {
                container.innerHTML = '<div class="text-center py-5"><i class="fas fa-search fa-3x text-muted"></i><p class="mt-3 text-muted">No se encontraron propiedades con esos criterios.</p></div>';
                return;
            }
            container.innerHTML = '<div class="row g-4">' + data.map(p => `
                <div class="col-md-4">
                    <div class="card premium-card border-0 h-100">
                        <img src="${p.foto || 'img/LogoPNK2.png'}" class="card-img-top" alt="${p.tipo} en ${p.sector}" style="height:220px;object-fit:cover;">
                        <div class="card-body text-center">
                            <span class="badge bg-warning text-dark mb-2">${p.tipo.charAt(0).toUpperCase()+p.tipo.slice(1)}</span>
                            <h5 class="fw-bold">${p.tipo.charAt(0).toUpperCase()+p.tipo.slice(1)} en ${p.sector || p.comuna}</h5>
                            <p class="text-muted small"><i class="fas fa-map-marker-alt"></i> ${p.comuna}, ${p.provincia}</p>
                            <p class="text-muted small">#${p.codigo}</p>
                            <p class="fw-bold fs-5">$${Number(p.precio_clp).toLocaleString('es-CL')}</p>
                            <a href="detalle-propiedad.php?id=${p.id}" class="btn btn-warning text-dark fw-bold">
                                <i class="fas fa-eye me-1"></i> ¡Quiero saber más!
                            </a>
                        </div>
                    </div>
                </div>
            `).join('') + '</div>';
        })
        .catch(() => {
            container.innerHTML = '<div class="alert alert-danger">Error al buscar propiedades.</div>';
        });
}

/* ========== COMUNAS DINÁMICAS ========== */
const comunasPorProvincia = {
    'Elqui': ['La Serena','Coquimbo','Andacollo','La Higuera','Paihuano','Vicuña'],
    'Limarí': ['Ovalle','Combarbalá','Monte Patria','Punitaqui','Río Hurtado'],
    'Choapa': ['Illapel','Canela','Los Vilos','Salamanca']
};

function actualizarComunas(selectProvincia, selectComuna) {
    const prov = document.getElementById(selectProvincia);
    const com = document.getElementById(selectComuna);
    if (!prov || !com) return;

    prov.addEventListener('change', function() {
        com.innerHTML = '<option value="">Todas las comunas</option>';
        const comunas = comunasPorProvincia[this.value] || [];
        comunas.forEach(c => {
            com.innerHTML += `<option value="${c}">${c}</option>`;
        });
    });
}

/* ========== PROPERTY CAROUSEL ========== */
function initCarousel(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const slides = container.querySelectorAll('.carousel-slide');
    const dots = container.querySelectorAll('.carousel-dot');
    let current = 0;

    function showSlide(n) {
        current = (n + slides.length) % slides.length;
        slides.forEach((s, i) => s.classList.toggle('active', i === current));
        dots.forEach((d, i) => d.classList.toggle('active', i === current));
    }

    container.querySelector('.carousel-prev')?.addEventListener('click', () => showSlide(current - 1));
    container.querySelector('.carousel-next')?.addEventListener('click', () => showSlide(current + 1));
    dots.forEach((d, i) => d.addEventListener('click', () => showSlide(i)));

    // Auto-play
    setInterval(() => showSlide(current + 1), 5000);
}

/* ========== ALERTS ========== */
function showAlert(message, type = 'success') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alert.style.cssText = 'top:100px;right:20px;z-index:9999;min-width:300px;';
    alert.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 4000);
}
