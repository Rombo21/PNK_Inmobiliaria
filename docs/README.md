# PNK Inmobiliaria

Plataforma inmobiliaria para la Región de Coquimbo, Chile.

## Requisitos

- PHP 8.x
- MySQL 8.x
- Apache con mod_rewrite habilitado (WAMP/XAMPP/LAMP)

## Instalación Local (WAMP/XAMPP)

1. **Clonar/copiar** el proyecto en la carpeta `www/` (WAMP) o `htdocs/` (XAMPP).

2. **Crear la base de datos:**
   - Abrir phpMyAdmin → SQL → Pegar contenido de `sql/pnk_inmobiliaria.sql` → Ejecutar.
   - O desde terminal: `mysql -u root < sql/pnk_inmobiliaria.sql`

3. **Configurar conexión:** Editar `config/db.php` si es necesario (por defecto: localhost, root, sin contraseña).

4. **Crear directorios de uploads:**
   ```bash
   mkdir -p uploads/propiedades uploads/certificados
   ```

5. **Acceder:** `http://localhost/PNK_Inmobiliaria/`

### Credenciales de prueba

| Rol | Correo | Contraseña |
|-----|--------|------------|
| Admin | admin@pnkinmobiliaria.cl | Admin123! |
| Propietario | maria.gonzalez@email.cl | Admin123! |
| Gestor | roberto.pizarro@email.cl | Admin123! |

## Despliegue en AWS

### 1. EC2 (Ubuntu 22.04)

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y apache2 php8.1 php8.1-mysql php8.1-mbstring php8.1-xml libapache2-mod-php8.1
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Copiar proyecto a `/var/www/html/PNK_Inmobiliaria/`.

Configurar Apache (`/etc/apache2/sites-available/000-default.conf`):
```apache
<Directory /var/www/html>
    AllowOverride All
</Directory>
```

### 2. RDS (MySQL 8.0)

- Crear instancia RDS MySQL 8.0
- Crear base de datos `pnk_inmobiliaria`
- Importar SQL: `mysql -h <RDS_ENDPOINT> -u admin -p pnk_inmobiliaria < sql/pnk_inmobiliaria.sql`

### 3. Configurar conexión

Crear `.env` en la raíz del proyecto:
```env
DB_HOST=tu-rds-endpoint.amazonaws.com
DB_PORT=3306
DB_NAME=pnk_inmobiliaria
DB_USER=admin
DB_PASS=tu_password
SITE_URL=http://tu-dominio.com
GOOGLE_MAPS_KEY=tu_api_key
```

### 4. S3 (opcional)

Para almacenar imágenes en S3, configurar las variables en `.env` y modificar la función `handleFileUpload()` en `includes/auth.php`.

### 5. Permisos

```bash
sudo chown -R www-data:www-data /var/www/html/PNK_Inmobiliaria/uploads
sudo chmod -R 755 /var/www/html/PNK_Inmobiliaria/uploads
```

## Estructura del Proyecto

```
├── index.php                  # Página principal
├── login.php                  # Login + recuperar contraseña
├── logout.php                 # Cerrar sesión
├── registro-propietario.php   # Registro propietario
├── registro-gestor.php        # Registro gestor freelance
├── dashboard.php              # Dashboard admin
├── crud-usuarios.php          # CRUD usuarios (admin)
├── crud-propiedades.php       # CRUD propiedades
├── detalle-propiedad.php      # Detalle de propiedad
├── config/db.php              # Conexión MySQL PDO
├── includes/
│   ├── header.php             # Header + navbar
│   ├── footer.php             # Footer
│   └── auth.php               # Autenticación y utilidades
├── api/
│   ├── propiedades.php        # API AJAX propiedades
│   └── usuarios.php           # API AJAX usuarios
├── css/style.css              # Estilos CSS
├── js/main.js                 # JavaScript principal
├── sql/pnk_inmobiliaria.sql   # Script BD completo
└── uploads/                   # Archivos subidos
```
