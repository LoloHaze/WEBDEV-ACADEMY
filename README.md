````md
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-Markup-E34F26?logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-Responsive-1572B6?logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?logo=javascript&logoColor=black)
![Responsive](https://img.shields.io/badge/Responsive-Mobile%20Ready-0ea5e9)
![Architecture](https://img.shields.io/badge/Architecture-Modular-purple)
![Status](https://img.shields.io/badge/Status-Completed-success)

# 🚀 WebDev Academy

Plataforma e-learning desarrollada en **PHP y MySQL**, inspirada en plataformas modernas como **Udemy**, enfocada en la formación online, el seguimiento del progreso y una experiencia de usuario moderna e interactiva.

Proyecto desarrollado como plataforma full-stack dentro del ciclo **Desarrollo de Aplicaciones Web (DAW)**.

---

# ✨ Características principales

- 🔐 Sistema completo de autenticación y roles
- 🎓 Plataforma de cursos estilo Udemy
- 📚 Sistema de lecciones con reproductor integrado
- 📈 Seguimiento de progreso en tiempo real
- ⭐ Valoraciones y comentarios
- 🧠 Exámenes finales por curso
- 🏆 Generación automática de certificados PDF
- 👨‍💼 Panel administrativo completo
- 🔍 Buscador, filtros y ordenación avanzada
- 📱 Diseño responsive moderno
- ⚡ Arquitectura modular escalable
- 🛡️ Gestión segura mediante sesiones y estados
- 🖼️ Gestión de imágenes de perfil y cursos

---

# 📌 Descripción

WebDev Academy simula el funcionamiento de una plataforma real de aprendizaje online.

La aplicación permite:

- Registro e inicio de sesión
- Gestión de usuarios y roles
- Solicitud de inscripción a cursos
- Gestión administrativa de solicitudes
- Seguimiento del progreso por lección
- Realización de exámenes finales
- Descarga automática de certificados PDF
- Valoraciones mediante estrellas y comentarios
- Dashboard personalizado para alumnos
- Gestión modular de cursos y lecciones

El sistema utiliza una arquitectura basada en estados para evitar eliminaciones físicas innecesarias y mejorar la integridad de los datos.

---

# 🛠 Tecnologías utilizadas

## Backend

- PHP 8 (mysqli)
- MySQL
- Prepared Statements
- Gestión de sesiones

## Frontend

- HTML5
- CSS3
- JavaScript ES6
- Diseño responsive

## Herramientas

- XAMPP
- Git & GitHub
- FPDF (certificados PDF)

---

# ✨ Funcionalidades

## 👤 Usuarios

- Registro y autenticación
- Gestión de perfil con avatar
- Roles diferenciados:
  - Usuario
  - Administrador
- Protección de rutas privadas
- Dashboard personal (`misCursos.php`)
- Visualización del progreso por curso
- Examen final por curso
- Descarga automática de certificados

---

## 🎓 Cursos

- CRUD completo de cursos
- Activación y desactivación de cursos
- Cursos gratuitos y estructura preparada para cursos premium
- Buscador por nombre
- Filtros dinámicos
- Ordenación por:
  - ⭐ Mejor valorados
  - 🔥 Más inscritos
  - 🆕 Más recientes
- Sistema de paginación

---

## 📚 Lecciones

- Vista tipo plataforma e-learning
- Sidebar interactiva
- Reproductor de vídeo integrado
- Lista lateral de lecciones
- Indicador visual de progreso
- Sistema toggle:
  - Marcar como completada
  - Marcar como no completada
- Cálculo automático del progreso

---

## 📊 Progreso y certificación

- Seguimiento individual por usuario
- Barra de progreso dinámica
- Cálculo automático del porcentaje completado
- Generación automática de certificados PDF
- Acceso directo al certificado desde el dashboard

---

## ⭐ Sistema de valoraciones

- Valoraciones de 1–5 estrellas
- Comentarios asociados
- Edición y eliminación de valoraciones
- Media automática por curso
- Gestión desde panel administrativo

---

# 🛠 Panel de administración

Sistema administrativo completo para la gestión total de la plataforma:

- Gestión de usuarios
- Gestión de cursos
- Gestión de lecciones
- Gestión de valoraciones
- Gestión de inscripciones
- Moderación mediante estados:
  - Pendiente
  - Aprobado
  - Rechazado

---

# 🧠 Arquitectura del proyecto

```bash
/public
│── index.php
│── curso.php
│── leccion.php
│── login.php
│── registro.php
│── perfil.php

/admin
│── panel.php
│── gestionCursos.php
│── gestionUsuarios.php
│── gestionInscripciones.php

/includes
│── bd.php
│── funciones.php
│── proteccion.php

/uploads
│── perfiles/
│── cursos/
````

Separación modular entre:

* Área pública
* Área administrativa
* Sistema de autenticación
* Lógica reutilizable
* Gestión multimedia

---

# 🔐 Seguridad implementada

* Prepared Statements
* Validación y sanitización de datos
* Hash seguro de contraseñas (`password_hash`)
* Protección mediante sesiones
* Control de acceso por roles
* Gestión segura mediante estados

---

# 📱 Diseño y experiencia de usuario

La interfaz está inspirada en plataformas modernas SaaS y e-learning:

* Diseño responsive
* UI oscura moderna
* Cards dinámicas
* Sidebar interactiva
* Barras de progreso
* Feedback visual
* Sistema visual de estrellas

---

```md
# 🌍 Deployment

La plataforma se encuentra desplegada y funcionando en producción mediante hosting en IONOS.

El proyecto ha sido adaptado para entorno real, incluyendo:

- Configuración de rutas y estructura pública
- Gestión de archivos y uploads
- Configuración de base de datos en producción
- Adaptación de includes y paths para servidor
- Optimización para hosting compartido
- Configuración de dominio y despliegue web

El despliegue se realizó utilizando:

- Hosting Linux + PHP en IONOS
- MySQL en producción
- Git/GitHub para control de versiones
```


# 📈 Mejoras futuras

* 💳 Sistema de pagos real
* 📩 Notificaciones
* 🧠 Recomendaciones inteligentes
* 📊 Estadísticas avanzadas
* 🔒 Protección CSRF
* ⚡ Optimización SQL
* 🌐 API REST
* 🤖 Integración IA para aprendizaje personalizado

---

# 👨‍💻 Autor

**Manuel Rabal Bueno**
Desarrollo de Aplicaciones Web (DAW) — 2026

Proyecto desarrollado como plataforma full-stack orientada a portfolio profesional y simulación de una plataforma real de formación online.

```
```
