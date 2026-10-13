<?php
  $titulo_pagina = 'DKBZA Barber Studio | Tienda';
  include('includes/header.php');
  include('includes/nav.php');
?>

  <!-- TIENDA -->

  <section id="tienda" class="section-dark">

    <div class="container">


      <div class="shop-header">

        <div>

          <span class="section-tag">
            TIENDA ONLINE
          </span>

          <h1>
            Productos para barberias.
          </h1>

        </div>


        <div id="cart-counter" class="cart-counter hidden">

          <span id="cart-count">
            0
          </span>

          productos

        </div>

      </div>


      <div class="grid-products">


        <!-- PRODUCTO 1 -->

        <div class="card-product">

          <img src="./img/producto1.png" alt="Pomada Mate Level3" class="product-img" loading="lazy">

          <h3>
            Pomada Mate Level3
          </h3>

          <p>
            Fijación fuerte y acabado natural.
          </p>

          <span class="price">
            $13.000
          </span>

          <button
            class="btn-blue add-to-cart-btn"
            data-product="Pomada Mate Level3"
          >
            Agregar al carrito
          </button>

        </div>


        <!-- PRODUCTO 2 -->

        <div class="card-product">

          <img src="./img/producto2.png" alt="Aftershave Cologne Level3" class="product-img" loading="lazy">

          <h3>
            Aftershave Cologne Level3
          </h3>

          <p>
            Colonia para después del corte, cierra poros.
          </p>

          <span class="price">
            $14.500
          </span>

          <button
            class="btn-blue add-to-cart-btn"
            data-product="Aftershave Cologne Level3"
          >
            Agregar al carrito
          </button>

        </div>


      </div>

    </div>

  </section>

<?php include('includes/footer.php'); ?>