# 🏢 Alfa Business Group - Sistema de Geolocalización y Control de KPIs

[![Laravel Version](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)
[![Vite Version](https://img.shields.io/badge/Vite-5.x-purple.svg)](https://vitejs.dev)
[![Leaflet Version](https://img.shields.io/badge/Leaflet-1.9.4-green.svg)](https://leafletjs.com)

Sistema integral de gestión de asesores, geocercas en tiempo real, mapas satelitales corporativos y supervisión de evidencias para **Alfa Business Group** (Bolivia).

---

## 🚀 Funcionalidades Principales

El sistema está estructurado bajo 3 niveles de roles jerárquicos:

### 1. 👤 Rol Asesor (Trabajo de Campo)
*   **Captura de Ubicación GPS Real:** El asesor captura sus coordenadas en tiempo real al registrar una visita.
*   **Control de Geofencing Anti-Fraude:** El sistema restringe la edición de la posición en el mapa. Si se intenta arrastrar o fijar un marcador a más de **500 metros** de la ubicación GPS actual capturada, la acción es bloqueada y se muestra un aviso visual elegante.
*   **Mapas Flexibles (Leaflet):** Los mapas permiten cambiar entre **Vista Satélite 🛰️** (Esri World Imagery) para ver techos/letreros y **Vista Calles 🗺️** (OpenStreetMap).
*   **Carga de Evidencias:** Carga directa de imágenes de letreros y firmas físicas directamente desde el celular.
*   **Ingreso de KPIs:** Registro diario de visitas, captaciones de letreros, exclusivas, cierres de ventas, llamadas y propiedades en sistema (AlphaX).

### 2. 👥 Rol Team Leader (Supervisor de Oficina)
*   **Dashboard de Desempeño Semanal:** Vista gráfica del cumplimiento del equipo de asesores de su sucursal.
*   **Mapa de Visitas del Equipo:** Mapa dinámico con capas de Satélite / Calles que agrupa todas las visitas registradas por sus asesores de campo.
*   **Exportación de Datos:** Descarga de reportes detallados en CSV para análisis en Excel.

### 3. 👑 Rol Director (Administración Global)
*   **Panel Directivo Consolidado:** Métricas de rendimiento corporativo global, ranking de asesores y perfilado automático de mejores asesores candidatos a Team Leader.
*   **📸 Sección de Supervisión de Evidencias:**
    *   Filtros inteligentes por Asesor y fecha.
    *   Detalle de visitas y minicarta Leaflet por cada coordenada GPS con opción satélite/calle.
    *   **Lightbox integrado:** Visualizador de fotos de letreros a pantalla completa con efectos visuales modernos.
*   **Gestión Total del Sistema:** Creación y edición de Oficinas, Equipos, Usuarios (Directores, Team Leaders, Asesores) y configuración dinámica de KPIs y metas mínimas.

---

## 🛠️ Requisitos del Sistema
*   **PHP** >= 8.2
*   **Composer** (Gestor de dependencias de PHP)
*   **Node.js** >= 18 & **NPM** (Para compilar y ejecutar Vite)
*   **Base de Datos:** MySQL o PostgreSQL
*   **Conexión HTTPS (Obligatorio en producción):** Necesario para que los navegadores móviles otorguen acceso a `navigator.geolocation`.

---

## 📥 Instrucciones de Instalación y Configuración

Sigue estos pasos detallados para instalar y configurar el proyecto de forma local:

### 1. Clonar el repositorio
```bash
git clone https://github.com/jhuniorcanaza/Alfa-Business-Group-V2.git
cd Alfa-Business-Group
```

### 2. Instalar dependencias de PHP
```bash
composer install
```

### 3. Instalar dependencias de Frontend (Javascript / CSS)
```bash
npm install
```

### 4. Configurar el archivo de entorno (`.env`)
Duplica el archivo de ejemplo y configúralo con los datos de tu entorno:
```bash
cp .env.example .env
```
Abre el archivo `.env` y configura los accesos a tu base de datos local:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alfa_business_db
DB_USERNAME=root
DB_PASSWORD=tu_contraseña
```

### 5. Generar la clave de la aplicación
```bash
php artisan key:generate
```

### 6. Ejecutar migraciones y carga de datos iniciales (Seeders)
Este comando creará todas las tablas necesarias e insertará las configuraciones base y los roles de prueba:
```bash
php artisan migrate --seed
```

### 7. Crear el enlace simbólico para las imágenes de evidencias
Laravel requiere un enlace simbólico de la carpeta `storage` hacia `public` para que las fotos subidas por los asesores se muestren en el visor del director:
```bash
php artisan storage:link
```

---

## 🖥️ Ejecución del Proyecto en Desarrollo

Para ejecutar el sistema de forma local, debes ejecutar los siguientes comandos en terminales separadas:

### Terminal 1: Servidor Backend Laravel
Inicia el servidor PHP local (por defecto corre en `http://127.0.0.1:8000`):
```bash
php artisan serve
```

### Terminal 2: Servidor Frontend Vite
Inicia el motor de Vite para procesar en tiempo real los estilos CSS y Javascript del frontend:
```bash
npm run dev
```

---

## 🔑 Cuentas de Acceso por Defecto (Seeders)
Una vez que ejecutas `php artisan db:seed`, puedes ingresar con los siguientes accesos de prueba:

*   **Director (Administrador):**
    *   **Email:** `director@alfa.com`
    *   **Password:** `password`
*   **Team Leader (Líder de Equipo):**
    *   **Email:** `leader@alfa.com`
    *   **Password:** `password`
*   **Asesor de Campo:**
    *   **Email:** `asesor@alfa.com`
    *   **Password:** `password`

---

## 📦 Estructura del Código Modificado para Optimización
*   `routes/auth.php` & `bootstrap/app.php`: Optimizaciones para manejar deslogueos limpios y evitar pantallas de error `419 | Página Caducada` al expirar la sesión.
*   `resources/views/director/reports-index.blade.php`: Nuevo módulo interactivo del Director para supervisión de evidencias, Lightbox para imágenes de letreros y mapas satelitales Leaflet.
*   `resources/views/asesor/report-form.blade.php` & `report-edit.blade.php`: Lógica de validación de geofencing dentro de un radio de 500 metros con modales de aviso estilizados.
