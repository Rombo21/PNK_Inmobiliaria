    </main>
    <!-- Footer -->
    <footer class="footer-pnk" id="contacto">
        <div class="footer-wave">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 100" preserveAspectRatio="none">
                <path fill="currentColor" d="M0,40 C360,100 720,0 1080,60 C1260,90 1380,30 1440,50 L1440,100 L0,100 Z"/>
            </svg>
        </div>
        <div class="footer-content">
            <div class="footer-grid">
                <div class="footer-brand">
                    <img src="img/LogoPNK2.png" alt="PNK Inmobiliaria" class="footer-logo">
                    <p class="footer-tagline">Transformando la forma de encontrar tu lugar ideal en la Región de Coquimbo.</p>
                </div>
                
                <div class="footer-links-group">
                    <h4 class="footer-title">Navegación</h4>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
                        <li><a href="index.php#buscador"><i class="fas fa-search"></i> Buscar Propiedades</a></li>
                        <li><a href="registro-propietario.php"><i class="fas fa-user-plus"></i> Registro Propietario</a></li>
                        <li><a href="registro-gestor.php"><i class="fas fa-user-tie"></i> Registro Gestor</a></li>
                    </ul>
                </div>
                
                <div class="footer-links-group">
                    <h4 class="footer-title"><i class="fas fa-headset"></i> Contacto</h4>
                    <ul class="footer-links footer-contact-list">
                        <li><i class="fas fa-envelope"></i> info@pnkinmobiliaria.cl</li>
                        <li><i class="fas fa-phone"></i> +56 9 1234 5678</li>
                        <li><i class="fas fa-map-marker-alt"></i> La Serena, Chile</li>
                    </ul>
                </div>
                
                <div class="footer-links-group">
                    <h4 class="footer-title">Síguenos</h4>
                    <div class="footer-social">
                        <a href="#" class="social-link" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="social-link" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> PNK Inmobiliaria. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
    <?php if (isset($extraScripts)): ?>
        <?php foreach ($extraScripts as $script): ?>
            <script src="<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
