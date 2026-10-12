# 💈 DKBZA Barber Studio

Sitio web multipágina, moderno y responsivo para barberías, desarrollado a partir de un diseño en Figma. Representa a **DKBZA Barber Studio** (Salta Capital, Argentina) y en la Unidad 4 se migró de HTML estático a **PHP**, con plantillas reutilizables y variables de entorno.

## 🚀 Características
- **Dark Theme:** Estilo oscuro con tipografías `Inter` y `JetBrains Mono`.
- **10 vistas PHP:** Inicio, Nosotros, Servicios, Staff, Galería, Reservas, Reseñas, Tienda, Contacto y Panel Admin (`admin123`).
- **Diseño responsivo:** Adaptado para móviles y escritorio con CSS Grid/Flexbox y Bootstrap.
- **Interactividad (JS):** Validación de acceso para administradores, flujo de reservas y carrito de la tienda.
- **Plantillas PHP (SSI):** header, nav y footer compartidos por todas las páginas.
- **Navegación dinámica:** título de pestaña por página y resaltado de la sección activa en el menú.

## 🔄 Refactorización a PHP 

El proyecto partió de la maqueta estática en HTML del Hito 1 y se migró a PHP con:

- **Migración a `.php`**: todas las pantallas `.html` se renombraron a `.php` y se ejecutan en un servidor local (XAMPP).
- **Modularización (SSI)**: las secciones repetidas se extrajeron a plantillas reutilizables en `/includes/`:
  - `header.php`: `<head>`, metadatos, hojas de estilo y fuentes.
  - `nav.php`: menú de navegación principal.
  - `footer.php`: pie de página y carga de `script.js`.
  
  Cada página las incorpora con `include`.
- **Renderizado server-side (SSR)**:
  - **Título dinámico**: cada página define `$titulo_pagina` antes de incluir `header.php`, y ese valor se imprime en `<title>`.
  - **Menú activo**: `nav.php` detecta la página actual con `basename($_SERVER['PHP_SELF'])` y le agrega la clase `active` al enlace correspondiente.
- **Variables de entorno**: el archivo `.env` guarda el nombre de la empresa, el email de contacto y el entorno (`APP_ENV=local`). `config/env.php` lo lee y expone la función `env()`, que se usa en el footer, en la página de contacto y en el título por defecto. El `.env` está excluido en `.gitignore`, y `.env.example` sirve de plantilla. Además, `.htaccess` bloquea el acceso al `.env` desde el navegador.

## 🎨 Prototipo en Figma

🔗 [Ver diseño en Figma] https://www.figma.com/make/Oa6Kg84rj2VwZ5zH7AlGY3/Wireframe-para-DKBZA-Barber-Studio?t=gSs6taOH4TZWwJRP-1

## 🛠️ Tecnologías utilizadas

- HTML5, CSS3, JavaScript (ES6+) y Bootstrap
- PHP 8.x
- XAMPP (Apache)
- Prettier (formateo)
- Git y GitHub (ramas, commits y Pull Requests)

## 📁 Estructura del proyecto

```
Barberias/
├── config/
│   └── env.php          
├── includes/
│   ├── header.php       
│   ├── nav.php          
│   └── footer.php       
├── css/styles.css
├── js/script.js
├── img/
├── capturas/           
├── .env                 
├── .env.example         
├── .gitignore
├── .htaccess
├── index.php
├── nosotros.php
├── servicios.php
├── staff.php
├── galeria.php
├── reservas.php
├── resenas.php
├── tienda.php
├── contacto.php
└── admin.php
```

## 🚀 Instalación y ejecución local

1. Instalar [XAMPP](https://www.apachefriends.org/) (PHP 8.x).
2. Clonar el repositorio dentro de `htdocs`:
   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/ArapaDillan10/Pagina-para-barberias Barberias
   ```
3. Crear el archivo de configuración a partir de la plantilla:
   ```bash
   cd Barberias
   copy .env.example .env
   ```
   y completar los valores en `.env`.
4. Abrir el **XAMPP Control Panel** e iniciar **Apache**.
5. Entrar desde el navegador a: <http://localhost/Barberias/>

## 📸 Evidencia de funcionamiento

### Servidor local ejecutándose
![Servidor local](capturas/01-servidor-local.png)

### Estructura modular (SSI)
![Páginas usando includes](capturas/02-estructura-ssi.png)
![Plantillas header, nav y footer](capturas/03-plantillas.png)

### Navegación dinámica (título + clase active)
![Título dinámico y menú activo](capturas/04-navegacion-dinamica.png)

### Configuración de variables de entorno
![Archivo .env.example](capturas/05-env-example.png)

## 📊 Auditoría y optimización (Lighthouse)

Se realizó una auditoría de calidad y accesibilidad con Chrome DevTools (Hito 1), con estas puntuaciones:

- Performance: 80/100
- Accessibility: 85/100
- Best Practices: 100/100
- SEO: 90/100

---

Desarrollado por **Dillan Arapa** · Programación Web · 2026
