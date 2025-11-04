# 🧱 Storage Manager – Laravel + Vite + Tailwind

Sistema completo de **gestión de almacenamiento seguro** con backend en **Laravel 11 (Sanctum + MySQL)**  
y frontend en **Vite + TailwindCSS + Vanilla JS**.  

Permite:
- Subir y gestionar archivos de forma segura.
- Definir **roles (admin / user)**.
- Configurar **extensiones prohibidas** y **límites de almacenamiento** globales, por grupo y por usuario.

---

## 🚀 Tecnologías principales

| Área | Tecnologías |
|------|--------------|
| Backend | Laravel 11, Sanctum, PHP 8.2+, MySQL 8+, Docker |
| Frontend | Vite, TailwindCSS, Vanilla JavaScript |
| Autenticación | Laravel Sanctum (SPA) |
| Infraestructura | Docker Compose |

---

## ⚙️ Decisiones de diseño

### 🧩 Arquitectura limpia
- Controladores ligeros que delegan lógica a **servicios** (`SettingsService`, `StorageLimitService`) y **repositorios** (`SettingsRepository`).
- Modelo `Setting` centralizado para configuraciones globales (límite por defecto, extensiones prohibidas).

### 💾 Control de almacenamiento jerárquico
- Prioridad de límites:
  ```
  Usuario > Grupo > Global
  ```
- Antes de subir un archivo, el sistema valida el espacio disponible:
  > (uso_actual + tamaño_archivo) <= cuota_asignada

### 🔐 Seguridad
- Laravel Sanctum para autenticación por sesión segura.
- Validación estricta de extensiones y tipos MIME.
- Subida segura (carpeta `storage/app/public/uploads`).
- ZIPs descomprimidos con validación interna de archivos.

### 🎨 Frontend minimalista
- **TailwindCSS** para un diseño limpio y moderno.
- **Vanilla JS + módulos ES6** para un SPA ligero y sin dependencias pesadas.
- Vistas reactivas y controladas por rol (admin / user).

---

## 🧩 Estructura general

```
storage-manager/
├── backend/
│   ├── app/
│   ├── routes/
│   ├── database/
│   ├── composer.json
│   ├── docker-compose.yml
│   └── README.md
│
└── frontend/
    ├── js/
    │   ├── api.js
    │   ├── main.js
    │   ├── pages/
    │   │   ├── login.js
    │   │   ├── dashboard.js
    │   │   ├── extensions.js
    │   │   ├── limits.js
    │   │   └── groups.js
    ├── tailwind.config.js
    ├── vite.config.js
    └── package.json
```

---

# ⚙️ Backend (Laravel 11)

## 🧰 Instalación paso a paso

### 1️⃣ Clonar el repositorio
```bash
git clone https://github.com/tuusuario/storage-manager.git
cd storage-manager/backend
```

### 2️⃣ Instalar dependencias
```bash
composer install
```

### 3️⃣ Configurar entorno
```bash
cp .env.example .env
```
Configura tus credenciales en `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=storage_manager
DB_USERNAME=root
DB_PASSWORD=root
```

### 4️⃣ Generar clave
```bash
php artisan key:generate
```

### 5️⃣ Migrar y sembrar la base de datos
```bash
php artisan migrate --seed
```

> Esto creará los valores iniciales en la tabla `settings`:
> - `default_limit` (100 MB)
> - `forbidden_extensions` (`exe,php,js,bat,sh`)

### 6️⃣ Crear enlace de almacenamiento público
```bash
php artisan storage:link
```

### 7️⃣ Ejecutar servidor
```bash
php artisan serve
```
Backend disponible en 👉 `http://localhost:8000`

---

## 🧱 Endpoints principales

| Método | Ruta | Descripción |
|--------|------|--------------|
| `POST` | `/login` | Iniciar sesión (Sanctum) |
| `POST` | `/logout` | Cerrar sesión |
| `GET` | `/files` | Listar archivos del usuario |
| `POST` | `/upload` | Subir archivo o ZIP |
| `DELETE` | `/files/{id}` | Eliminar archivo |
| `GET` | `/settings` | Obtener configuración global |
| `POST` | `/settings/update` | Actualizar configuración global |
| `PUT` | `/users/{id}/limit` | Actualizar límite de usuario |
| `PUT` | `/groups/{id}/limit` | Actualizar límite de grupo |

---

## 👤 Roles y permisos

| Rol | Permisos |
|------|-----------|
| **Admin** | CRUD de usuarios, grupos y configuración global |
| **User** | Subir y eliminar sus propios archivos |

---

## 🧪 Usuarios de prueba

| Rol | Email | Password |
|------|--------|----------|
| Admin | admin@example.com | password |
| User | user@example.com | password |

---

# 💻 Frontend (Vite + Tailwind + Vanilla JS)

## 🧰 Instalación paso a paso

### 1️⃣ Ir al directorio frontend
```bash
cd ../frontend
```

### 2️⃣ Instalar dependencias
```bash
npm install
```

### 3️⃣ Configurar el endpoint del backend
Edita `js/api.js`:
```js
export default {
  baseUrl: "http://localhost:8000/api",
  ...
};
```

### 4️⃣ Ejecutar el servidor de desarrollo
```bash
npm run dev
```

Frontend disponible en 👉 `http://localhost:5173`

---

## 🧠 Funcionalidades principales

✅ **Autenticación (login/logout)** con Laravel Sanctum  
✅ **Subida de archivos** y previsualización (PDF, imagen, DOCX, etc.)  
✅ **Gestión de extensiones prohibidas** (checkbox dinámico)  
✅ **Configuración de límites** globales, por grupo y usuario  
✅ **Mensajes claros y alertas visuales con Tailwind**  
✅ **Diseño responsive, moderno y accesible**

---

## 📸 Interfaz principal

```
📁 Panel de almacenamiento
-------------------------------------
[ Archivos ] [ Usuarios ] [ Grupos ]
[ Configuración ] [ Límites ]
-------------------------------------
- Listado de archivos subidos
- Botón para subir archivo
- Vista previa con íconos (📄 🖼️ 🗜️)
```

---

## 🧱 Docker (opcional)

Ejecuta ambos servicios con Docker Compose:

```bash
docker-compose up -d
```

Esto iniciará:
- `db` → MySQL

---

## 📄 Licencia
Proyecto de ejemplo educativo – libre para uso, modificación o extensión.  

---

## ✍️ Autor
**Ítalo Ramírez**
# edulabs
