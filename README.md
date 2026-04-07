![PHP](https://img.shields.io/badge/PHP-8-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-Markup-E34F26?logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-Styles-1572B6?logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?logo=javascript&logoColor=black)
![Status](https://img.shields.io/badge/Status-In%20Development-yellow)

# 🚀 WebDev Academy

Plataforma de formación online desarrollada en **PHP y MySQL**, que permite a los usuarios inscribirse en cursos, completar lecciones en vídeo, realizar valoraciones y obtener un certificado automáticamente al finalizar.

Proyecto desarrollado como trabajo práctico del ciclo **Desarrollo de Aplicaciones Web (DAW)**.

---

## 📌 Descripción

WebDev Academy simula el funcionamiento básico de plataformas e-learning como Udemy.

La aplicación permite:

- Registro e inicio de sesión de usuarios
- Solicitud de inscripción a cursos
- Gestión de solicitudes por parte del administrador
- Seguimiento del progreso por lección
- Obtención automática de certificado al completar el curso
- Sistema de valoraciones con estrellas y comentarios
- Panel de administración completo

El sistema está diseñado utilizando gestión por estados en lugar de eliminaciones físicas, garantizando coherencia y trazabilidad en la base de datos.

---

## 🛠 Tecnologías utilizadas

- PHP 8 (mysqli)
- MySQL
- HTML5
- CSS3
- JavaScript básico
- XAMPP (entorno local)
- Git & GitHub

---

## ✨ Funcionalidades

### 👤 Usuarios

- Registro y autenticación
- Gestión de perfil con imagen
- Roles diferenciados (usuario y administrador)
- Control de acceso mediante sesiones
- Dashboard personal (`misCursos.php`)
- Visualización del progreso por curso
- Descarga de certificado al completar el 100%

---

### 🎓 Cursos

- CRUD completo de cursos (admin)
- Activación / desactivación de cursos
- Cursos gratuitos y de pago (estructura preparada)
- Buscador por nombre
- Filtro por precio
- Orden por:
  - ⭐ Mejor valorados
  - 🔥 Más inscritos
  - 🆕 Más recientes
- Sistema de paginación

---

### 📚 Lecciones

- Visualización tipo plataforma e-learning (sidebar + reproductor estilo Udemy)
- Lista lateral con todas las lecciones del curso
- Indicador visual de lecciones completadas
- Sistema toggle:
  - Marcar como completada
  - Marcar como no completada
- Cálculo automático del progreso

---

### 📊 Progreso y certificación

- Seguimiento individual por usuario
- Barra de progreso dinámica
- Generación automática de certificado cuando se alcanza el 100%
- Acceso directo al certificado desde el dashboard

---

### ⭐ Sistema de valoraciones

- Valoración de cursos (1–5 estrellas)
- Comentarios asociados
- Edición y eliminación por parte del usuario
- Gestión de valoraciones desde el panel de administración

---

## 🛠 Panel de administración

- Gestión completa de cursos
- Gestión de lecciones
- Gestión de inscripciones:
  - Pendiente
  - Aprobado
  - Rechazado
- Reversión de estados sin eliminación física
- Gestión de valoraciones
- Gestión de usuarios

---

## 🧠 Arquitectura del proyecto

```
/public        → Área de usuario
/admin         → Área de administración
/includes      → Conexión y lógica común
/uploads       → Imágenes de perfiles y cursos
```

Separación clara entre:

- Vista general del curso (`curso.php`)
- Vista de consumo de contenido (`leccion.php`)
- Panel administrativo independiente

---

## 🔐 Gestión de estados

El sistema evita el uso de `DELETE` en entidades críticas como inscripciones, utilizando en su lugar estados:

- pendiente
- aprobado
- rechazado

Esto mejora la consistencia y permite revertir acciones sin pérdida de datos.

---

## 🌍 Deployment (Próximamente)

Actualmente el proyecto se ejecuta en entorno local (XAMPP).

Está previsto su despliegue en un dominio real una vez finalizado el desarrollo y realizadas las optimizaciones finales de seguridad y rendimiento.

---

## ▶ Instalación

1. Clonar el repositorio:

```bash
git clone https://github.com/tu_usuario/webdevacademy.git
```

2. Importar la base de datos en MySQL.
3. Configurar credenciales en:

```
/includes/bd.php
```

4. Ejecutar en entorno local (XAMPP).

---

## 📈 Posibles mejoras futuras

- Sistema de notificaciones
- Guardado automático de última lección vista
- Mejora visual con framework CSS
- Sistema de pagos real
- Estadísticas avanzadas en panel admin
- Optimización de consultas SQL
- Protección contra CSRF

---

## 👨‍💻 Autor

Nombre: *[Manuel Rabal]*  
Ciclo: Desarrollo de Aplicaciones Web (DAW)  
Año: 2026  
