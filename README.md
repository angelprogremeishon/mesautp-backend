# MesaUTP — API Backend (Laravel 12)

API REST y panel de administración de **MesaUTP**, el marketplace de comida para
estudiantes de **UTP Ate**. Sirve los datos al frontend (SPA React) y provee un
panel admin con Filament.

> Frontend (SPA React): repositorio **mesautp-frontend**.
> Documento de requerimientos: `../DOCUMENTACION_REQUERIMIENTOS.md`.

---

## Stack

- **Laravel 12** (PHP 8.2+)
- **MySQL**
- **Laravel Sanctum** — autenticación por token Bearer
- **Filament** — panel de administración (`/admin`)
- **Mail** — envío del enlace mágico (magic link)

---

## Requisitos previos

- PHP 8.2 o superior
- Composer
- MySQL (o MariaDB)
- Node.js (solo si vas a compilar assets de Filament/Vite del backend)

---

## Instalación

```bash
# 1. Instalar dependencias
composer install

# 2. Copiar variables de entorno
copy .env.example .env        # Windows
# cp .env.example .env        # Linux/Mac

# 3. Generar la APP_KEY
php artisan key:generate

# 4. Configurar la base de datos en .env (ver más abajo)

# 5. Migrar y poblar datos de ejemplo
php artisan migrate --seed

# 6. Enlazar el almacenamiento público (para las fotos)
php artisan storage:link

# 7. Levantar el servidor
php artisan serve
# API disponible en http://localhost:8000
```

---

## Variables de entorno clave (`.env`)

```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mesautp
DB_USERNAME=root
DB_PASSWORD=

# Correo (magic link). En desarrollo puedes usar "log" para ver el enlace en storage/logs
MAIL_MAILER=log
MAIL_FROM_ADDRESS="no-reply@mesautp.pe"
MAIL_FROM_NAME="MesaUTP"

# Sanctum / CORS — orígenes permitidos del frontend
SANCTUM_STATEFUL_DOMAINS=localhost:5173
```

> Con `MAIL_MAILER=log`, el enlace mágico se escribe en `storage/logs/laravel.log`
> en vez de enviarse por correo — útil para desarrollo.

---

## Estructura relevante

```
app/
├── Http/Controllers/Api/
│   ├── AuthController.php          # send-link, verify, me, logout
│   ├── LocalesController.php       # externos / internos (lista + detalle)
│   ├── PedidoController.php        # crear, listar, calificar
│   └── EmprendedorController.php   # dashboard, registro, productos, confirmar/listo
├── Filament/Resources/             # panel admin (Local, User)
├── Mail/MagicLinkMail.php          # correo del enlace mágico
└── Models/                         # User, Local, Categoria, Producto, Pedido, Resena, MagicLink
database/
├── migrations/                     # esquema
└── seeders/                        # CategoriaSeeder, LocalSeeder, DatabaseSeeder
routes/
├── api.php                         # API REST (ver tabla abajo)
└── web.php                         # rutas web / admin
```

---

## Endpoints

### Públicos
| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | `/api/auth/send-link` | Envía el enlace mágico al correo @utp.edu.pe |
| POST | `/api/auth/verify` | Verifica el token → devuelve `{ user, token }` |
| GET | `/api/locales/externos` | Lista paginada de locales externos |
| GET | `/api/locales/externos/{id}` | Detalle de local externo |
| GET | `/api/locales/internos` | Lista paginada de locales internos |
| GET | `/api/locales/internos/{id}` | Detalle de oferta interna |

### Autenticados — `Authorization: Bearer <token>`
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/auth/me` | Usuario autenticado |
| POST | `/api/auth/logout` | Cerrar sesión |
| GET | `/api/pedidos` | Pedidos del usuario |
| POST | `/api/pedidos` | Crear reserva |
| POST | `/api/pedidos/{id}/calificar` | Calificar pedido entregado |
| GET | `/api/emprendedor` | Dashboard del emprendedor |
| POST | `/api/emprendedor/registro` | Registrar local |
| POST | `/api/emprendedor/local` | Actualizar local |
| POST | `/api/emprendedor/productos` | Publicar/editar oferta |
| POST | `/api/emprendedor/pedidos/{id}/confirmar` | Confirmar pedido |
| POST | `/api/emprendedor/pedidos/{id}/listo` | Marcar pedido listo |

> **Importante:** las listas de locales devuelven paginación de Laravel.
> El array de resultados está en `response.data.data`.

---

## Modelo de datos

- **User** — usuario (estudiante / emprendedor / admin). `password` es nullable (login por magic link).
- **Local** — `tipo`: `interno` | `externo`; `estado`: `pendiente` | `aprobado`; flag `activo`. Expone `foto_url`.
- **Categoria** — categoría del local.
- **Producto** — oferta/menú del local (`es_menu_dia`, `cantidad_disponible`).
- **Pedido** — `estado`: `pendiente` → `confirmado` → `listo` → `entregado` | `cancelado`.
- **Resena** — calificación (estrellas + comentario) ligada a un pedido.
- **MagicLink** — token temporal para el login sin contraseña.

Scopes útiles en `Local`: `aprobados()`, `externos()`, `internos()`.

---

## Panel de administración

Disponible en `http://localhost:8000/admin` (Filament). Permite aprobar locales,
gestionar usuarios y revisar contenido.

---

## Autenticación (flujo magic link)

1. El frontend llama `POST /api/auth/send-link` con el correo @utp.edu.pe.
2. El backend genera un `MagicLink` y envía el enlace al correo (o al log en dev).
3. El usuario abre el enlace → el frontend llama `POST /api/auth/verify` con el token.
4. El backend valida y responde `{ user, token }` (token Sanctum).
5. El frontend guarda el token y lo envía como `Authorization: Bearer` en cada request.

---

## Comandos útiles

```bash
php artisan migrate:fresh --seed   # recrea la BD con datos de ejemplo
php artisan storage:link           # enlaza storage público (fotos)
php artisan route:list             # lista todas las rutas
php artisan tinker                 # consola interactiva
```
