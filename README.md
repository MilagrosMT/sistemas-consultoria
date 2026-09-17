\# Sistema Integrado de Servicios Administrativos y Contables



\## 1. Descripción del proyecto



Sistema web desarrollado para una pequeña empresa de consultoría contable y tributaria ubicada en Arequipa.



La empresa brinda servicios administrativos y contables a otras pequeñas empresas y actualmente puede gestionar parte de su información mediante herramientas como Excel y WhatsApp.



El sistema busca centralizar la información y facilitar la gestión de los principales procesos administrativos.



\## 2. Objetivo



Desarrollar un sistema web que permita centralizar y gestionar los principales procesos administrativos, contables, tributarios y de recursos humanos de la empresa.



\## 3. Módulos implementados



\- Dashboard

\- Clientes

\- Servicios

\- Contratos

\- Empleados

\- Reclutamiento

\- Planillas

\- Administración Tributaria

\- Reportes

\- SLA y KPI

\- Usuarios

\- Roles



\## 4. Tecnologías utilizadas



\- Laravel 13

\- PHP 8.4

\- Livewire

\- Tailwind CSS

\- Vite

\- MySQL 8

\- Git



\## 5. Requisitos



Para ejecutar el proyecto se requiere:



\- PHP 8.3 o superior

\- Composer

\- Node.js y npm

\- MySQL 8

\- Laravel Herd o un servidor local compatible



\## 6. Instalación



\### 6.1 Instalar dependencias de PHP



Desde la carpeta del proyecto ejecutar:



&#x20;   composer install



\### 6.2 Instalar dependencias de Front-End



&#x20;   npm install



\### 6.3 Configurar el archivo de entorno



Copiar `.env.example` como `.env`:



&#x20;   copy .env.example .env



Configurar en `.env` los datos de conexión a MySQL.



Ejemplo:



&#x20;   DB\_CONNECTION=mysql

&#x20;   DB\_HOST=127.0.0.1

&#x20;   DB\_PORT=3306

&#x20;   DB\_DATABASE=sistemas\_consultoria

&#x20;   DB\_USERNAME=root

&#x20;   DB\_PASSWORD=



\### 6.4 Generar la clave de la aplicación



&#x20;   php artisan key:generate



\### 6.5 Ejecutar migraciones



&#x20;   php artisan migrate



\### 6.6 Ejecutar los seeders



&#x20;   php artisan db:seed



\### 6.7 Compilar los recursos Front-End



Para desarrollo:



&#x20;   npm run dev



Para producción:



&#x20;   npm run build



\## 7. Ejecución



Con Laravel Herd, el proyecto puede ejecutarse mediante el dominio local configurado para el proyecto.



También puede utilizarse:



&#x20;   php artisan serve



\## 8. Optimización y WPO



Para la optimización del Front-End se utilizó Vite.



Se ejecutó:



&#x20;   npm run build



para generar los recursos optimizados para producción.



La aplicación fue evaluada mediante Lighthouse.



Resultado obtenido:



\- Performance: 100/100

\- Accessibility: 94/100

\- Best Practices: 78/100

\- SEO: 91/100



\## 9. SLA y KPI



El sistema incorpora indicadores para evaluar la calidad del servicio.



Se implementaron los siguientes indicadores:



\- Cumplimiento tributario: meta 95%

\- Cumplimiento de planillas: meta 95%



Cuando el indicador alcanza o supera la meta establecida, el sistema muestra que cumple el SLA. Si se encuentra por debajo de la meta, muestra que requiere atención.



\## 10. Seguridad y control de acceso



El sistema utiliza autenticación y control de acceso mediante roles.



Los roles implementados son:



\- Administrador

\- Contabilidad

\- Recursos Humanos

\- Consulta



El acceso a determinadas funcionalidades se restringe según el rol del usuario.



\## 11. Base de datos



El sistema utiliza MySQL para almacenar la información de clientes, servicios, contratos, empleados, planillas, postulantes, obligaciones tributarias, usuarios y roles.



\## 12. Entrega



La carpeta de entrega contiene el código fuente del proyecto y los archivos necesarios para instalar sus dependencias.



Por razones de tamaño, las carpetas `vendor` y `node\_modules` no forman parte de la entrega. Estas pueden ser reconstruidas mediante:



&#x20;   composer install



y



&#x20;   npm install



El archivo `.env` real tampoco se incluye por seguridad. Se debe utilizar `.env.example` como referencia para crear la configuración local.

