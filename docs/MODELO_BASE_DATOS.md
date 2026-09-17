# Modelo de Base de Datos del Sistema

## 1. Alcance

Este documento presenta el modelo de la base de datos `sistemas_consultoria` implementada en MySQL 8.0.46 para el sistema administrativo y contable.

Se incluyen las tablas de negocio, sus atributos con tipos de datos, claves primarias, claves foráneas, relaciones y cardinalidades.

Las tablas internas de Laravel (`cache`, `cache_locks`, `failed_jobs`, `job_batches`, `jobs`, `migrations`, `password_reset_tokens` y `sessions`) no forman parte del modelo de negocio.

## 2. Modelo de relaciones

```mermaid
erDiagram
    SERVICIOS ||--o{ CLIENTES : "ofrece"
    SERVICIOS ||--o{ CONTRATOS : "corresponde"
    CLIENTES ||--o{ COMPRAS : "registra"
    CLIENTES ||--o{ CONTRATOS : "firma"
    CLIENTES ||--o{ MOVIMIENTO_CAJAS : "genera"
    CLIENTES ||--o{ SERVICIO_PROCESOS : "solicita"
    CLIENTES ||--o{ VENTA_CLIENTES : "registra"
    CLIENTES ||--o{ VENTAS : "recibe"
    SERVICIOS ||--o{ SERVICIO_PROCESOS : "se_procesa"
    SERVICIOS ||--o{ VENTAS : "se_factura"
    CONTRATOS ||--o{ SERVICIO_PROCESOS : "origina"
    EMPLEADOS ||--o{ PLANILLAS : "genera"
    EMPLEADOS ||--o{ SERVICIO_PROCESOS : "atiende"
    ROLES ||--o{ USERS : "asigna"
    USERS ||--o{ PASSKEYS : "registra"

    CLIENTES {
        bigint id PK
        bigint servicio_id FK
    }
    SERVICIOS {
        bigint id PK
    }
    COMPRAS {
        bigint id PK
        bigint cliente_id FK
    }
    CONTRATOS {
        bigint id PK
        bigint cliente_id FK
        bigint servicio_id FK
    }
    MOVIMIENTO_CAJAS {
        bigint id PK
        bigint cliente_id FK
    }
    SERVICIO_PROCESOS {
        bigint id PK
        bigint cliente_id FK
        bigint contrato_id FK
        bigint servicio_id FK
        bigint empleado_id FK
    }
    VENTA_CLIENTES {
        bigint id PK
        bigint cliente_id FK
    }
    VENTAS {
        bigint id PK
        bigint cliente_id FK
        bigint servicio_id FK
    }
    EMPLEADOS {
        bigint id PK
    }
    PLANILLAS {
        bigint id PK
        bigint empleado_id FK
    }
    ROLES {
        bigint id PK
    }
    USERS {
        bigint id PK
        bigint role_id FK
    }
    PASSKEYS {
        bigint id PK
        bigint user_id FK
    }
}
```

## 3. Tablas y atributos

### 3.1 `clientes`

| Atributo | Tipo | Clave |
|---|---|---|
| id | bigint unsigned | PK |
| ruc | varchar(11) | UK |
| razon_social | varchar(255) | |
| nombre_comercial | varchar(255) | |
| direccion | varchar(255) | |
| telefono | varchar(20) | |
| correo | varchar(255) | |
| servicio_contratado | varchar(255) | |
| servicio_id | bigint unsigned | FK |
| estado | enum('Activo','Inactivo') | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 3.2 `servicios`

| Atributo | Tipo | Clave |
|---|---|---|
| id | bigint unsigned | PK |
| nombre | varchar(255) | |
| descripcion | text | |
| precio | decimal(10,2) | |
| estado | enum('Activo','Inactivo') | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 3.3 `contratos`

| Atributo | Tipo | Clave |
|---|---|---|
| id | bigint unsigned | PK |
| cliente_id | bigint unsigned | FK |
| servicio_id | bigint unsigned | FK |
| fecha_inicio | date | |
| fecha_fin | date | |
| monto_mensual | decimal(10,2) | |
| estado | enum('Activo','Finalizado','Suspendido') | |
| observaciones | text | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 3.4 `compras`

| Atributo | Tipo | Clave |
|---|---|---|
| id | bigint unsigned | PK |
| cliente_id | bigint unsigned | FK |
| proveedor | varchar(255) | |
| ruc_proveedor | varchar(11) | |
| tipo_comprobante | varchar(255) | |
| serie | varchar(10) | |
| numero | varchar(20) | |
| fecha_emision | date | |
| fecha_vencimiento | date | |
| base_imponible | decimal(10,2) | |
| igv | decimal(10,2) | |
| total | decimal(10,2) | |
| forma_pago | enum('Contado','Credito') | |
| estado | enum('Registrada','Observada','Anulada') | |
| observacion | text | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 3.5 `ventas`

| Atributo | Tipo | Clave |
|---|---|---|
| id | bigint unsigned | PK |
| cliente_id | bigint unsigned | FK |
| servicio_id | bigint unsigned | FK |
| cantidad | decimal(10,2) | |
| precio_unitario | decimal(10,2) | |
| descripcion | varchar(255) | |
| tipo_comprobante | varchar(255) | |
| numero_comprobante | varchar(255) | |
| fecha_emision | date | |
| forma_pago | varchar(255) | |
| fecha_vencimiento | date | |
| base_imponible | decimal(10,2) | |
| igv | decimal(10,2) | |
| total | decimal(10,2) | |
| monto_pendiente | decimal(10,2) | |
| estado | varchar(255) | |
| observacion | text | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 3.6 `venta_clientes`

| Atributo | Tipo | Clave |
|---|---|---|
| id | bigint unsigned | PK |
| cliente_id | bigint unsigned | FK |
| comprador | varchar(255) | |
| ruc_comprador | varchar(11) | |
| tipo_comprobante | varchar(255) | |
| serie | varchar(10) | |
| numero | varchar(20) | |
| fecha_emision | date | |
| base_imponible | decimal(10,2) | |
| igv | decimal(10,2) | |
| total | decimal(10,2) | |
| forma_pago | varchar(255) | |
| estado | varchar(255) | |
| observacion | text | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 3.7 `movimiento_cajas`

| Atributo | Tipo | Clave |
|---|---|---|
| id | bigint unsigned | PK |
| cliente_id | bigint unsigned | FK |
| tipo_movimiento | enum('Ingreso','Egreso') | |
| concepto | varchar(255) | |
| categoria | varchar(255) | |
| fecha | date | |
| forma_pago | enum('Efectivo','Transferencia','Tarjeta','Yape/Plin','Otro') | |
| monto | decimal(10,2) | |
| numero_comprobante | varchar(50) | |
| observacion | text | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 3.8 `servicio_procesos`

| Atributo | Tipo | Clave |
|---|---|---|
| id | bigint unsigned | PK |
| cliente_id | bigint unsigned | FK |
| contrato_id | bigint unsigned | FK |
| servicio_id | bigint unsigned | FK |
| empleado_id | bigint unsigned | FK |
| periodo | varchar(20) | |
| fecha_inicio | date | |
| fecha_vencimiento | date | |
| prioridad | enum('Baja','Media','Alta') | |
| estado | enum('Pendiente','En proceso','Completado','Observado') | |
| observacion | text | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 3.9 `empleados`

| Atributo | Tipo | Clave |
|---|---|---|
| id | bigint unsigned | PK |
| nombres | varchar(255) | |
| apellidos | varchar(255) | |
| dni | varchar(8) | UK |
| cargo | varchar(255) | |
| area | varchar(255) | |
| fecha_ingreso | date | |
| salario | decimal(10,2) | |
| estado | enum('Activo','Inactivo') | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 3.10 `planillas`

| Atributo | Tipo | Clave |
|---|---|---|
| id | bigint unsigned | PK |
| empleado_id | bigint unsigned | FK |
| periodo | date | |
| sueldo_base | decimal(10,2) | |
| bonificaciones | decimal(10,2) | |
| descuentos | decimal(10,2) | |
| sueldo_neto | decimal(10,2) | |
| estado | varchar(255) | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 3.11 `postulantes`

| Atributo | Tipo | Clave |
|---|---|---|
| id | bigint unsigned | PK |
| nombres | varchar(255) | |
| apellidos | varchar(255) | |
| dni | varchar(8) | UK |
| telefono | varchar(9) | |
| email | varchar(255) | |
| puesto | varchar(255) | |
| fecha_postulacion | date | |
| estado | varchar(255) | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 3.12 `obligacion_tributarias`

| Atributo | Tipo | Clave |
|---|---|---|
| id | bigint unsigned | PK |
| cliente | varchar(255) | |
| ruc | varchar(11) | |
| tipo_obligacion | varchar(255) | |
| periodo | varchar(255) | |
| fecha_vencimiento | date | |
| monto | decimal(10,2) | |
| estado | varchar(255) | |
| created_at | timestamp | |
| updated_at | timestamp | |

**Nota:** esta tabla actualmente no tiene una clave foránea `cliente_id` hacia `clientes`.

### 3.13 `roles`

| Atributo | Tipo | Clave |
|---|---|---|
| id | bigint unsigned | PK |
| nombre | varchar(255) | |
| descripcion | varchar(255) | |
| activo | tinyint(1) | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 3.14 `users`

| Atributo | Tipo | Clave |
|---|---|---|
| id | bigint unsigned | PK |
| role_id | bigint unsigned | FK |
| name | varchar(255) | |
| email | varchar(255) | UK |
| email_verified_at | timestamp | |
| password | varchar(255) | |
| activo | tinyint(1) | |
| two_factor_secret | text | |
| two_factor_recovery_codes | text | |
| two_factor_confirmed_at | timestamp | |
| remember_token | varchar(100) | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 3.15 `passkeys`

| Atributo | Tipo | Clave |
|---|---|---|
| id | bigint unsigned | PK |
| user_id | bigint unsigned | FK |
| name | varchar(255) | |
| credential_id | varchar(255) | UK |
| credential | json | |
| last_used_at | timestamp | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 3.16 `configuracion_sistemas`

| Atributo | Tipo | Clave |
|---|---|---|
| id | bigint unsigned | PK |
| created_at | timestamp | |
| updated_at | timestamp | |
| razon_social | varchar(255) | |
| nombre_comercial | varchar(255) | |
| ruc | varchar(11) | |
| direccion | varchar(255) | |
| telefono | varchar(20) | |
| correo | varchar(255) | |
| logo | varchar(255) | |
| serie_factura | varchar(4) | |
| correlativo_factura | bigint unsigned | |
| serie_boleta | varchar(4) | |
| correlativo_boleta | bigint unsigned | |
| digitos_correlativo | tinyint unsigned | |
| igv | decimal(5,2) | |
| moneda | varchar(3) | |
| simbolo_moneda | varchar(5) | |
| formato_fecha | varchar(255) | |
| zona_horaria | varchar(255) | |

## 4. Relaciones y cardinalidades

| Tabla origen | Tabla destino | FK | Cardinalidad |
|---|---|---|---|
| servicios | clientes | clientes.servicio_id | 1:N |
| clientes | compras | compras.cliente_id | 1:N |
| clientes | contratos | contratos.cliente_id | 1:N |
| servicios | contratos | contratos.servicio_id | 1:N |
| clientes | movimiento_cajas | movimiento_cajas.cliente_id | 1:N |
| clientes | servicio_procesos | servicio_procesos.cliente_id | 1:N |
| contratos | servicio_procesos | servicio_procesos.contrato_id | 1:N |
| empleados | servicio_procesos | servicio_procesos.empleado_id | 1:N |
| servicios | servicio_procesos | servicio_procesos.servicio_id | 1:N |
| empleados | planillas | planillas.empleado_id | 1:N |
| roles | users | users.role_id | 1:N |
| users | passkeys | passkeys.user_id | 1:N |
| clientes | venta_clientes | venta_clientes.cliente_id | 1:N |
| clientes | ventas | ventas.cliente_id | 1:N |
| servicios | ventas | ventas.servicio_id | 1:N |

## 5. Observaciones de diseño

- Las relaciones se implementan mediante claves primarias y foráneas.
- La mayor parte de las relaciones funcionales del sistema son de tipo 1:N.
- `obligacion_tributarias` está actualmente diseñada con datos identificativos del cliente (`cliente`, `ruc`), pero sin FK a `clientes`.
- `configuracion_sistemas` funciona como tabla de configuración general del sistema y no mantiene una FK con otra tabla de negocio.
- Los campos `created_at` y `updated_at` corresponden al control de fechas de Laravel.

## 6. Evidencia técnica

El esquema SQL completo se encuentra en `database/schema_proyecto.sql` y contiene la estructura generada desde la base de datos real del proyecto.
