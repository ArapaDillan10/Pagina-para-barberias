# 💈 DKBZA Barber Studio

Sitio web multipágina, moderno y responsivo para barberías, desarrollado a partir de un diseño en Figma. Representa a **DKBZA Barber Studio** (Salta Capital, Argentina) y en la Unidad 4 se migró de HTML estático a **PHP**, con plantillas reutilizables, variables de entorno y procesamiento seguro de formularios.

## 🚀 Características
- **Dark Theme:** Estilo oscuro con tipografías `Inter` y `JetBrains Mono`.
- **10 vistas PHP:** Inicio, Nosotros, Servicios, Staff, Galería, Reservas, Reseñas, Tienda, Contacto y Panel Admin.
- **Diseño responsivo:** Adaptado para móviles y escritorio con CSS Grid/Flexbox y Bootstrap.
- **Interactividad (JS):** Validación de acceso para administradores, flujo de reservas y carrito de la tienda.
- **Plantillas PHP (SSI):** header, nav y footer compartidos por todas las páginas.
- **Navegación dinámica:** título de pestaña por página y resaltado de la sección activa en el menú.
- **Formularios procesados en el servidor (Clase 5):** contacto por `POST` y buscador de servicios por `GET`, con sanitización contra XSS, validación y mensajes al usuario.

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

🔗 [Ver diseño en Figma](https://www.figma.com/make/Oa6Kg84rj2VwZ5zH7AlGY3/Wireframe-para-DKBZA-Barber-Studio?t=gSs6taOH4TZWwJRP-1)

## 🛠️ Tecnologías utilizadas

- HTML5, CSS3, JavaScript (ES6+) y Bootstrap
- PHP 8.x
- XAMPP (Apache)
- Prettier (formateo)
- Git y GitHub (ramas, commits y Pull Requests)

## 📁 Estructura del proyecto

```
Barberias/
├── clase5/
│   ├── config/
│   ├── includes/
│   ├── css/styles.css
│   ├── js/script.js
│   ├── img/
│   ├── .env.example
│   ├── .htaccess
│   ├── contacto.php     ← Formulario POST
│   ├── servicios.php    ← Formulario GET (buscador)
│   └── ... (resto de las páginas .php)
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
3. Crear los archivos de configuración a partir de la plantilla (uno en la raíz y otro en `clase5`):
   ```bash
   cd Barberias
   copy .env.example .env
   copy clase5\.env.example clase5\.env
   ```
   y completar los valores en cada `.env`.
4. Abrir el **XAMPP Control Panel** e iniciar **Apache**.
5. Entrar desde el navegador a:
   - Sitio (TP4): <http://localhost/Barberias/>
   - Entrega de la Clase 5: <http://localhost/Barberias/clase5/>

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

---

## 📝 Clase 5: Procesamiento de Formularios y Seguridad Web

Todo el desarrollo de esta entrega está en la carpeta [`clase5/`](clase5/), que contiene una copia completa y funcional del sitio con los formularios procesados en el servidor con PHP.

🔗 **Probar localmente:** <http://localhost/Barberias/clase5/>

### 📂 Archivos modificados

```text
clase5/
├── contacto.php     ← Formulario POST (contacto)
├── servicios.php    ← Formulario GET (buscador de servicios)
└── css/styles.css   ← Estilos de alertas y buscador
```

### 📨 Formulario POST: Contacto (`clase5/contacto.php`)

Formulario de contacto con los campos **nombre**, **email** y **mensaje**. Los datos se envían por `POST` a la misma página y se procesan en el servidor antes de mostrar el HTML.

- **Sanitización:** cada campo pasa por la función `limpiar()`, que aplica `trim()` y `htmlspecialchars()` para prevenir ataques XSS.
- **Validación en el servidor:**
  - `isset()` y `empty()` comprueban que los campos obligatorios no lleguen vacíos.
  - `filter_var($email, FILTER_VALIDATE_EMAIL)` valida el formato del correo.
  - El mensaje debe tener al menos 10 caracteres.
- **Mensajes al usuario:** un cuadro rojo lista todos los errores de validación y un cuadro verde confirma el envío exitoso.
- **Persistencia:** si hay errores, los campos conservan los valores ingresados (`value="<?php echo $nombre; ?>"`). Si el envío es exitoso, el formulario se vacía.
- El formulario usa `novalidate` para que la validación la haga el servidor (PHP) y no solo el navegador.

**Errores de validación (campos vacíos):**

![Errores de validación](capturas/06-post-errores.png)

**Persistencia de datos (email inválido y mensaje corto):**

![Persistencia de datos](capturas/07-post-persistencia.png)

**Protección contra XSS:** el código `<script>alert(1)</script>` se muestra como texto y no se ejecuta.

![Protección XSS](capturas/08-post-xss.png)

**Envío exitoso:**

![Envío exitoso](capturas/09-post-exito.png)

### 🔍 Formulario GET: Buscador de Servicios (`clase5/servicios.php`)

Buscador que filtra los servicios de la barbería por nombre o descripción. El término viaja en la URL (por ejemplo, `servicios.php?buscar=barba`), así la búsqueda se puede compartir o guardar.

- Los servicios se almacenan en un array de PHP (`$servicios`) y las tarjetas se generan con `foreach`.
- **Sanitización:** el término se recibe con `filter_input(INPUT_GET, 'buscar', ...)` y se limpia con `trim()` y `htmlspecialchars()` antes de usarlo o mostrarlo.
- **Validación:** `isset()` y `empty()` determinan si se realizó una búsqueda. Si el campo está vacío, se muestran todos los servicios.
- **Filtrado:** `stripos()` busca coincidencias sin distinguir mayúsculas de minúsculas.
- **Mensajes al usuario:** se informa la cantidad de resultados o que no hubo coincidencias, con un enlace para volver a ver todos.
- **Persistencia:** el término buscado se mantiene en el campo de búsqueda.

**Resultados de la búsqueda:**

![Resultados de búsqueda](capturas/10-get-resultados.png)

**Búsqueda sin resultados:**

![Búsqueda sin resultados](capturas/11-get-sin-resultado.png)

---

## 📊 Auditoría y optimización (Lighthouse)

Se realizó una auditoría de calidad y accesibilidad con Chrome DevTools (Hito 1), con estas puntuaciones:

- Performance: 80/100
- Accessibility: 85/100
- Best Practices: 100/100
- SEO: 90/100

---

Desarrollado por **Dillan Arapa** · Programación Web · 2026
