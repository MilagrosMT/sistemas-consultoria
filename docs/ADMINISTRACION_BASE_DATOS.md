# Administración de la Base de Datos

## 1. Sistema Gestor de Base de Datos

El sistema utiliza MySQL Community Server 8.0.46 como Sistema Gestor de Base de Datos (SGBD).

La aplicación desarrollada en Laravel se conecta a la base de datos `sistemas_consultoria` mediante una conexión MySQL.

La instancia principal utiliza:

- Host: 127.0.0.1
- Puerto: 3306
- Motor: MySQL
- Base de datos: sistemas_consultoria

La aplicación mantiene la conexión con la instancia principal para realizar las operaciones de registro, consulta, actualización y procesamiento de información.

---

## 2. Integración de la aplicación con la base de datos

La arquitectura utilizada se organiza en tres componentes:

### Frontend

La interfaz web está desarrollada con Laravel, Livewire y Tailwind CSS.

Permite que los usuarios interactúen con los diferentes módulos del sistema.

### Backend

Laravel gestiona:

- rutas;
- autenticación;
- autorización mediante roles;
- lógica de negocio;
- validación de información;
- acceso a la base de datos.

### Base de datos

MySQL almacena la información de los diferentes módulos del sistema.

El flujo principal es:

**Usuario → Interfaz → Laravel/Livewire → MySQL → Respuesta al usuario**

---

## 3. Diseño y organización de la base de datos

La base de datos contiene tablas relacionadas con los diferentes procesos administrativos, contables y de gestión del sistema.

Las principales tablas de negocio son:

- `clientes`
- `servicios`
- `contratos`
- `compras`
- `ventas`
- `venta_clientes`
- `movimiento_cajas`
- `servicio_procesos`
- `empleados`
- `planillas`
- `postulantes`
- `obligacion_tributarias`
- `roles`
- `users`
- `passkeys`
- `configuracion_sistemas`

Además, existen tablas internas utilizadas por Laravel para funciones del framework, como sesiones, caché, trabajos y migraciones.

### Relaciones mediante claves foráneas

Las relaciones principales implementadas en MySQL son:

- `clientes.servicio_id` → `servicios.id`
- `compras.cliente_id` → `clientes.id`
- `contratos.cliente_id` → `clientes.id`
- `contratos.servicio_id` → `servicios.id`
- `movimiento_cajas.cliente_id` → `clientes.id`
- `planillas.empleado_id` → `empleados.id`
- `servicio_procesos.cliente_id` → `clientes.id`
- `servicio_procesos.contrato_id` → `contratos.id`
- `servicio_procesos.empleado_id` → `empleados.id`
- `servicio_procesos.servicio_id` → `servicios.id`
- `users.role_id` → `roles.id`
- `venta_clientes.cliente_id` → `clientes.id`
- `ventas.cliente_id` → `clientes.id`
- `ventas.servicio_id` → `servicios.id`
- `passkeys.user_id` → `users.id`

Las relaciones de las tablas dependientes se estructuran principalmente como relaciones de uno a muchos (1:N).

Por ejemplo:

**Clientes (1) → Compras (N)**

Un cliente puede tener varias compras registradas y cada compra pertenece a un cliente.

Otro ejemplo:

**Servicios (1) → Ventas (N)**

Un servicio puede aparecer en diferentes ventas y cada venta corresponde a un servicio.

---

## 4. Optimización mediante índices

Se implementaron índices adicionales en campos utilizados frecuentemente para búsquedas, filtros y consultas.

Entre ellos se encuentran:

- estados;
- fechas de emisión;
- fechas de vencimiento;
- fechas de inicio;
- periodos;
- prioridades;
- fechas de postulación;
- tipos de movimiento.

La implementación se realizó mediante la migración:

`2026_09_17_172002_add_performance_indexes_to_business_tables`

Los índices fueron ejecutados mediante Laravel y posteriormente verificados directamente en MySQL.

---

## 5. Copias de seguridad

El sistema incorpora un comando de Laravel para generar copias de seguridad de la base de datos:

```text
php artisan db:backup
