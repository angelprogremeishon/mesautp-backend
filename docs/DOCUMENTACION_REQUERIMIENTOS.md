# MesaUTP — Documento de Requerimientos

**Proyecto:** MesaUTP — Marketplace de comida para estudiantes de UTP Ate
**Tipo:** Aplicación web responsive (PWA) + API REST
**Última actualización:** 11/06/2026

---

## 1. Descripción general

MesaUTP es una plataforma que conecta a la comunidad universitaria de **UTP Ate** con
opciones de comida económica. Resuelve dos necesidades:

1. **Locales externos:** restaurantes/menús cercanos al campus que el estudiante puede ver y reservar.
2. **Locales internos:** compañeros emprendedores que cocinan y venden dentro del campus.

El modelo es de **reserva sin delivery**: el estudiante reserva, paga por Yape/Plin o en
efectivo, y recoge su pedido en el punto acordado. No hay pasarela de pago integrada.

---

## 2. Actores del sistema

| Actor | Descripción | Acceso |
|-------|-------------|--------|
| **Estudiante (Consumidor)** | Busca, filtra, ve detalle y reserva comida. | App web, login con correo @utp.edu.pe |
| **Emprendedor Externo** | Dueño de local externo; gestiona su local y ofertas. | App web, panel emprendedor |
| **Emprendedor Interno** | Estudiante que cocina/vende en el campus. | App web, panel emprendedor |
| **Administrador** | Aprueba locales, modera contenido, gestiona usuarios. | Panel admin (Filament) en `/admin` |

> El **Estudiante** entra con **enlace mágico** (correo @utp.edu.pe) y el **Emprendedor** con **correo + contraseña**; el rol determina a qué secciones accede.

---

## 3. Requerimientos Funcionales (RF)

### 3.1 Autenticación y cuentas
- **RF-01** El **estudiante (consumidor)** ingresa mediante **enlace mágico** (magic link) enviado a su correo institucional **@utp.edu.pe** (validado en frontend y backend).
- **RF-02** El **emprendedor** ingresa con **correo + contraseña**: el **externo** (negocio no UTP) con cualquier correo; el **interno** (estudiante UTP) con su correo **@utp.edu.pe**. Crea su cuenta y los datos de su local en un mismo registro.
- **RF-03** La autenticación (enlace mágico o login de emprendedor) entrega un **token Bearer (Sanctum)** que se guarda en el dispositivo.
- **RF-04** El token no expira por inactividad: el usuario permanece logueado en su dispositivo hasta cerrar sesión.
- **RF-05** El usuario puede **cerrar sesión**, lo que invalida el token actual.
- **RF-06** El usuario elige su perfil de ingreso (**Estudiante** o **Emprendedor**); el rol determina sus secciones y su método de acceso.

### 3.2 Consumidor — Exploración
- **RF-07** Ver lista de **locales externos** cercanos al campus.
- **RF-08** Ver lista de **locales internos** (compañeros que cocinan hoy).
- **RF-09** **Buscar** por nombre de local, categoría, descripción, punto de entrega o nombre/descripción de un platillo. La búsqueda es **tolerante a acentos y mayúsculas**.
- **RF-10** **Filtrar** locales externos por: rango de precio (Hasta S/10, Hasta S/15), cercanía (< 5 min) y categoría (Veggie).
- **RF-11** **Filtrar** locales internos por: precio (Hasta S/8) y punto de entrega (Patio, Biblioteca).
- **RF-12** Ver el **detalle de un local externo**: foto, nombre, categoría, rating, distancia, horario, menú del día y reseñas.
- **RF-13** Ver el **detalle de una oferta interna**: foto, vendedor, platillo, precio, porciones disponibles, punto de encuentro, métodos de pago.
- **RF-14** Ver un **mapa** con la ubicación del usuario (geolocalización) y los locales con coordenadas registradas.

### 3.3 Consumidor — Pedidos
- **RF-15** **Reservar** un producto indicando cantidad y nota opcional.
- **RF-16** Recibir **confirmación de reserva** con resumen del pedido y enlace directo a **WhatsApp** del vendedor.
- **RF-17** Ver el historial **"Mis Pedidos"** separado por estado: Activos, Historial (entregados), Cancelados.
- **RF-18** **Calificar** un pedido entregado (reseña con estrellas y comentario).

### 3.4 Emprendedor — Panel
- **RF-19** **Registrar un local** (interno o externo) con datos básicos, contacto (WhatsApp), pagos (Yape/Plin), ubicación/horario y foto.
- **RF-20** Ver un **dashboard** con métricas del día: pedidos de hoy, ingresos, rating y oferta vigente.
- **RF-21** **Publicar la oferta del día** (producto/menú): nombre, descripción, precio y cantidad disponible.
- **RF-22** Ver los **pedidos recibidos** por estado: Pendientes, Confirmados, Completados.
- **RF-23** **Confirmar** un pedido pendiente.
- **RF-24** **Marcar como listo** un pedido confirmado (listo para recoger).
- **RF-25** Ver el **historial de ventas** y un resumen del día (ventas completadas, porciones, total recaudado).

### 3.5 Administrador
- **RF-26** **Aprobar/rechazar** locales antes de que sean visibles (estado `aprobado` + `activo`).
- **RF-27** Gestionar usuarios y locales desde el panel admin (Filament).

---

## 4. Requerimientos No Funcionales (RNF)

### 4.1 Usabilidad / UX
- **RNF-01** Diseño **responsive** con 3 breakpoints: móvil (<768px), tablet (768–1024px) y desktop (>1024px).
- **RNF-02** En móvil/tablet la navegación es una **barra inferior flotante**; en desktop es un **sidebar lateral fijo** con grids que aprovechan el ancho.
- **RNF-03** Notificaciones mediante **toasts** no intrusivos (librería Sonner). No se usan `alert()` nativos.
- **RNF-04** Iconografía **exclusivamente vectorial** (Lucide). **Prohibido el uso de emojis** como iconos.
- **RNF-05** Las imágenes rotas o ausentes muestran un **placeholder** con icono, nunca un espacio en blanco.
- **RNF-06** Validación de formularios con mensajes claros (sin popups nativos del navegador).

### 4.2 Rendimiento
- **RNF-07** Carga diferida (`lazy`) de imágenes de listados.
- **RNF-08** La SPA se sirve como build estático optimizado (Vite); listados paginados desde el backend.

### 4.3 Seguridad
- **RNF-08** Autenticación por token **Bearer (Laravel Sanctum)**.
- **RNF-09** Restricción de acceso por **dominio institucional** (@utp.edu.pe).
- **RNF-10** Endpoints sensibles protegidos por middleware `auth:sanctum`.
- **RNF-11** CORS configurado para permitir solo el origen del frontend.

### 4.4 Compatibilidad / Plataforma
- **RNF-12** PWA instalable (manifest + service worker) para uso tipo app en el teléfono.
- **RNF-13** Compatible con navegadores modernos (Chrome, Edge, Brave, Safari móvil).

### 4.5 Mantenibilidad
- **RNF-14** Frontend y backend en **repositorios separados**, comunicados por API REST.
- **RNF-15** Lógica de búsqueda/filtros centralizada (`src/lib/filtros.js`) y componentes reutilizables.

---

## 5. Modelo de datos (resumen)

| Entidad | Campos principales | Relaciones |
|---------|--------------------|------------|
| **User** | name, email, role, password (nullable) | tiene muchos Pedido; tiene un Local |
| **Local** | nombre, tipo (interno/externo), categoria_id, descripcion, foto, direccion, punto_entrega, distancia_metros, horario, precio_min, precio_max, yape, plin, whatsapp, estado, activo, rating_promedio, total_resenas | pertenece a User y Categoria; tiene Productos, Pedidos, Reseñas |
| **Categoria** | nombre | tiene muchos Local |
| **Producto** | local_id, nombre, descripcion, precio, cantidad_disponible, es_menu_dia | pertenece a Local |
| **Pedido** | user_id, local_id, producto_id, cantidad, total, estado, nota, hora_recojo | pertenece a User, Local, Producto; tiene una Reseña |
| **Resena** | pedido_id, user_id, local_id, estrellas, comentario | pertenece a Pedido, User, Local |
| **MagicLink** | email, token, expiración | — |

**Estados de Pedido:** `pendiente` → `confirmado` → `listo` → `entregado` (o `cancelado`).
**Estados de Local:** `pendiente` / `aprobado` (+ flag `activo`).

---

## 6. API REST (endpoints)

Base URL: `http://localhost:8000/api`

### Públicos
| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | `/auth/send-link` | Envía el enlace mágico al correo |
| POST | `/auth/verify` | Verifica el token y devuelve usuario + token Bearer |
| GET | `/locales/externos` | Lista paginada de locales externos |
| GET | `/locales/externos/{id}` | Detalle de local externo |
| GET | `/locales/internos` | Lista paginada de locales internos |
| GET | `/locales/internos/{id}` | Detalle de oferta interna |

### Autenticados (`Authorization: Bearer <token>`)
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/auth/me` | Usuario autenticado |
| POST | `/auth/logout` | Cierra sesión |
| GET | `/pedidos` | Pedidos del usuario |
| POST | `/pedidos` | Crear reserva |
| POST | `/pedidos/{id}/calificar` | Calificar pedido entregado |
| GET | `/emprendedor` | Dashboard del emprendedor |
| POST | `/emprendedor/registro` | Registrar local |
| POST | `/emprendedor/local` | Actualizar local |
| POST | `/emprendedor/productos` | Publicar/editar oferta |
| POST | `/emprendedor/pedidos/{id}/confirmar` | Confirmar pedido |
| POST | `/emprendedor/pedidos/{id}/listo` | Marcar pedido listo |

> Las listas (`/locales/...`) devuelven **paginado Laravel**: el array está en `response.data.data`.

---

## 7. Pendientes / Próximos pasos sugeridos

- [ ] Persistir **coordenadas (latitud/longitud)** en los locales para que aparezcan en el mapa.
- [ ] Implementar la pantalla de **reseña/calificación** en el frontend (endpoint ya existe).
- [ ] Filtros de chip avanzados (categorías reales desde BD en vez de etiquetas fijas).
- [ ] Notificaciones push para el emprendedor cuando llega un pedido.
- [ ] Subida real de imágenes de producto (hoy el campo foto del producto no se sube en "Publicar").
- [ ] Tests automatizados (PHPUnit en backend, Vitest en frontend).

---

## 8. Stack tecnológico

**Backend:** Laravel 12 · PHP 8.2+ · MySQL · Sanctum · Filament (admin) · Mail (magic link)
**Frontend:** React 19 · Vite · React Router v7 · Tailwind CSS v4 · Sonner · Lucide · Leaflet · Axios · vite-plugin-pwa
