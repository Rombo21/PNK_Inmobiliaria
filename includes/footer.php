    </main>
    <!-- Footer Moderno y Minimalista -->
    <footer class="footer-pnk text-white py-5 mt-auto" id="contacto" style="border-top: 1px solid rgba(255,255,255,0.1);">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4 col-md-6">
                    <img src="img/LogoPNK2.png" alt="PNK Inmobiliaria" class="mb-3" style="height: 60px; object-fit: contain;">
                    <p class="text-white small">Transformando la forma de encontrar tu lugar ideal en la Región de Coquimbo. Transparencia, confianza y cercanía.</p>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h6 class="text-uppercase fw-bold mb-3" style="letter-spacing: 1px; color: var(--color-acento);">Navegación</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><a href="index.php" class="text-white text-decoration-none opacity-75 custom-hover-link">Inicio</a></li>
                        <li class="mb-2"><a href="index.php#buscador" class="text-white text-decoration-none opacity-75 custom-hover-link">Buscar Propiedades</a></li>
                        <li class="mb-2"><a href="registro-propietario.php" class="text-white text-decoration-none opacity-75 custom-hover-link">Registro Propietario</a></li>
                        <li class="mb-2"><a href="registro-gestor.php" class="text-white text-decoration-none opacity-75 custom-hover-link">Registro Gestor</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h6 class="text-uppercase fw-bold mb-3" style="letter-spacing: 1px; color: var(--color-acento);">Contacto</h6>
                    <ul class="list-unstyled mb-0 text-white small">
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i> info@pnkinmobiliaria.cl</li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i> +56 9 1234 5678</li>
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> La Serena, Chile</li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h6 class="text-uppercase fw-bold mb-3" style="letter-spacing: 1px; color: var(--color-acento);">Síguenos</h6>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white opacity-75 custom-hover-link fs-5"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-white opacity-75 custom-hover-link fs-5"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white opacity-75 custom-hover-link fs-5"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white opacity-75 custom-hover-link fs-5"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
            
            <hr class="mt-5 mb-4 border-light opacity-25">
            
            <div class="text-center text-white small">
                <p class="mb-0">&copy; <?= date('Y') ?> PNK Inmobiliaria. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js?v=1.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    // SweetAlert para confirmaciones de eliminación
    function confirmDelete(event, form) {
        event.preventDefault();
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esto!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }
    </script>

    <?php if (isset($msg) && !empty($msg)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '<?= ($msgType === "success") ? "success" : "error" ?>',
                title: '<?= ($msgType === "success") ? "¡Éxito!" : "¡Atención!" ?>',
                text: '<?= addslashes(strip_tags($msg)) ?>',
                confirmButtonColor: '#ffc107',
                confirmButtonText: 'Aceptar'
            });
        });
    </script>
    <?php endif; ?>

    <?php if (isset($extraScripts)): ?>
        <?php foreach ($extraScripts as $script): ?>
            <script src="<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
