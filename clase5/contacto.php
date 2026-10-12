<?php
  $titulo_pagina = 'DKBZA Barber Studio | Contacto';
  include('includes/header.php');
  include('includes/nav.php');
?>

  <!-- CONTACTO -->

  <section class="section-card">

    <div class="container grid-contact">


      <!-- INFORMACIÓN -->

      <div>

        <span class="section-tag">
          UBICACIÓN Y CONTACTO
        </span>

        <h1>
          Encontranos en Salta
        </h1>

        <p class="text-subtle">
          calle siempre viva 123
        </p>

        <p class="text-subtle">
          Tel: +54 387 1111 111
        </p>
        
        <p class="text-subtle">
          Email: <?php echo htmlspecialchars(env('APP_EMAIL')); ?>
        </p>

      </div>


      <!-- FORMULARIO -->

      <form
        id="contact-form"
        class="contact-form"
      >

        <label>
          Nombre
        </label>

        <input
          type="text"
          class="wireframe-input"
          required
        >


        <label>
          Email
        </label>

        <input
          type="email"
          class="wireframe-input"
          required
        >


        <label>
          Mensaje
        </label>

        <textarea
          class="wireframe-input"
          rows="4"
          required
        ></textarea>


        <button
          type="submit"
          class="btn-blue"
        >
          Enviar Mensaje
        </button>

      </form>

    </div>

  </section>

<?php include('includes/footer.php'); ?>