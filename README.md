# Sistema Integrado de Servicios Administrativos y Contables

## 1. Descripción del proyecto

Sistema web desarrollado para una pequeña empresa de consultoría contable y tributaria ubicada en Arequipa, Perú.

El sistema busca centralizar la información y facilitar la gestión de los procesos administrativos, contables, tributarios y de recursos humanos.

## 2. Funcionalidades y módulos

### Gestión administrativa
- Dashboard.
- Clientes.
- Servicios.
- Contratos.
- Usuarios y roles.
- Configuración del sistema.

### Gestión contable y comercial
- Compras.
- Ventas y facturación.
- Caja de clientes.
- Procesamiento contable.
- Operaciones.
- Reportes.

### Recursos humanos
- Empleados.
- Reclutamiento.
- Planillas.

### Gestión tributaria y documentaria
- Administración tributaria.
- Documentos de clientes.
- Registro y descarga de documentos mediante almacenamiento privado.

### Gestión de solicitudes
- Registro y consulta de solicitudes.
- Búsqueda y filtrado por diferentes criterios.
- Seguimiento de estados.
- Asignación de responsables.
- Aprobación y rechazo de solicitudes según los permisos establecidos.

Los permisos de acceso dependen de la autenticación y del rol asignado al usuario.

## 3. Tecnologías utilizadas

- Laravel 13.
- PHP 8.4.
- Livewire.
- Tailwind CSS 4.
- Vite.
- Node.js 22 para la compilación de recursos.
- MySQL 8.
- Docker y Docker Compose.
- Nginx.
- Git y GitHub Actions para el control de versiones y la integración continua.

## 4. Requisitos

### Para ejecutar con Docker

- Docker Desktop con Docker Compose.
- Git.
- Los archivos de configuración de entorno necesarios para la aplicación.

### Para desarrollo local

- PHP 8.4.
- Composer.
- Node.js y npm.
- MySQL 8, si no se utiliza el servicio de base de datos de Docker.

## 5. Configuración del entorno

### 5.1. Crear el archivo de entorno de Laravel

Desde la carpeta raíz del proyecto, en PowerShell:

```powershell
Copy-Item .env.example .env
Copy-Item README.md README.backup.md
