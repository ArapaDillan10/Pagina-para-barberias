document.addEventListener('DOMContentLoaded', () => {

  // 1. CARRITO DE COMPRAS DE LA TIENDA
  let cartCount = 0;
  const cartCounterEl = document.getElementById('cart-counter');
  const cartCountEl = document.getElementById('cart-count');
  const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');

  addToCartButtons.forEach(button => {
    button.addEventListener('click', () => {
      cartCount++;
      if (cartCountEl) cartCountEl.textContent = cartCount;
      if (cartCounterEl && cartCounterEl.classList.contains('hidden')) {
        cartCounterEl.classList.remove('hidden');
      }

      const originalText = button.textContent;
      button.textContent = '¡Agregado!';
      button.style.backgroundColor = '#10B981';
      button.style.borderColor = '#10B981';

      setTimeout(() => {
        button.textContent = originalText;
        button.style.backgroundColor = '';
        button.style.borderColor = '';
      }, 1500);
    });
  });


  // 2. RESERVAS EN 3 PASOS: calendario real y horarios ocupados desde la base
  const formReserva = document.getElementById('form-reserva');
  if (formReserva) {
    const MESES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
      'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

    const inputFecha = document.getElementById('fecha');
    const gridDias = document.getElementById('calendar-days');
    const tituloMes = document.getElementById('month-title');
    const btnAnterior = document.getElementById('mes-anterior');
    const btnSiguiente = document.getElementById('mes-siguiente');
    const estadoHorarios = document.getElementById('horarios-estado');
    const radiosHora = formReserva.querySelectorAll('input[name="hora"]');

    const aFecha = (texto) => {
      const [a, m, d] = texto.split('-').map(Number);
      return new Date(a, m - 1, d);
    };
    const aTexto = (f) =>
      `${f.getFullYear()}-${String(f.getMonth() + 1).padStart(2, '0')}-${String(f.getDate()).padStart(2, '0')}`;

    const hoy = aFecha(formReserva.dataset.hoy);
    const limite = aFecha(formReserva.dataset.limite);
    const cerrados = formReserva.dataset.cerrados.split(',').map(Number);

    let mesVisible = inputFecha.value ? aFecha(inputFecha.value) : new Date(hoy);
    mesVisible.setDate(1);

    const barberoElegido = () => formReserva.querySelector('input[name="barbero"]:checked');
    const horaElegida = () => formReserva.querySelector('input[name="hora"]:checked');

    const marcarSeleccion = () => {
      formReserva.querySelectorAll('.barber-card, .time-slot').forEach(label => {
        label.classList.toggle('selected', label.querySelector('input').checked);
      });
    };

    const porcentajeSena = Number(formReserva.dataset.sena) || 50;
    const totalServicios = () =>
      [...formReserva.querySelectorAll('input[name="servicios[]"]:checked')]
        .reduce((suma, c) => suma + Number(c.dataset.precio || 0), 0);
    const aDinero = (n) => '$' + n.toLocaleString('es-AR', { maximumFractionDigits: 0 });

    const actualizarResumen = () => {
      const barbero = barberoElegido();
      const hora = horaElegida();
      const fecha = inputFecha.value ? aFecha(inputFecha.value) : null;
      document.getElementById('summary-barber').textContent = `✂ ${barbero ? barbero.dataset.nombre : '—'}`;
      document.getElementById('summary-date').textContent =
        `📅 ${fecha ? `${MESES[fecha.getMonth()]} ${fecha.getDate()}` : '—'}`;
      document.getElementById('summary-time').textContent = `⏰ ${hora ? hora.value + 'h' : '—'}`;
      document.getElementById('sena-monto').textContent =
        totalServicios() ? `${aDinero(totalServicios() * porcentajeSena / 100)} (${porcentajeSena}% de ${aDinero(totalServicios())})` : '—';
    };

    const actualizarHorarios = async () => {
      const barbero = barberoElegido();
      if (!barbero || !inputFecha.value) {
        estadoHorarios.textContent = 'ELEGÍ UN DÍA';
        radiosHora.forEach(radio => {
          radio.disabled = true;
          radio.parentElement.classList.add('disabled');
        });
        return;
      }

      estadoHorarios.textContent = 'BUSCANDO HORARIOS...';
      const params = new URLSearchParams({ barbero: barbero.value, fecha: inputFecha.value });

      try {
        const respuesta = await fetch(`horarios.php?${params}`);
        const datos = await respuesta.json();
        const ocupados = datos.ocupados || [];

        radiosHora.forEach(radio => {
          const ocupado = ocupados.includes(radio.value);
          radio.disabled = ocupado;
          radio.parentElement.classList.toggle('disabled', ocupado);
          if (ocupado) radio.checked = false;
        });

        estadoHorarios.textContent = datos.cerrado ? 'ESE DÍA NO ATENDEMOS' : 'HORARIOS DISPONIBLES';
      } catch (error) {
        estadoHorarios.textContent = 'NO SE PUDIERON CARGAR LOS HORARIOS';
      }
      marcarSeleccion();
      actualizarResumen();
    };

    const renderCalendario = () => {
      const anio = mesVisible.getFullYear();
      const mes = mesVisible.getMonth();
      tituloMes.textContent = `${MESES[mes].toUpperCase()} ${anio}`;

      let html = ['D', 'L', 'M', 'X', 'J', 'V', 'S'].map(d => `<span>${d}</span>`).join('');
      const primerDia = new Date(anio, mes, 1).getDay();
      const totalDias = new Date(anio, mes + 1, 0).getDate();

      for (let i = 0; i < primerDia; i++) html += '<span class="empty"></span>';

      for (let dia = 1; dia <= totalDias; dia++) {
        const fecha = new Date(anio, mes, dia);
        const texto = aTexto(fecha);
        const habilitado = fecha >= hoy && fecha <= limite && !cerrados.includes(fecha.getDay());
        const elegido = texto === inputFecha.value ? ' selected' : '';

        html += habilitado
          ? `<button type="button" class="day${elegido}" data-fecha="${texto}">${dia}</button>`
          : `<span class="day past-day">${dia}</span>`;
      }

      gridDias.innerHTML = html;
      btnAnterior.disabled = new Date(anio, mes, 0) < hoy;
      btnSiguiente.disabled = new Date(anio, mes + 1, 1) > limite;
    };

    gridDias.addEventListener('click', (e) => {
      const dia = e.target.closest('button.day');
      if (!dia) return;
      inputFecha.value = dia.dataset.fecha;
      renderCalendario();
      actualizarHorarios();
    });

    btnAnterior.addEventListener('click', () => {
      mesVisible.setMonth(mesVisible.getMonth() - 1);
      renderCalendario();
    });

    btnSiguiente.addEventListener('click', () => {
      mesVisible.setMonth(mesVisible.getMonth() + 1);
      renderCalendario();
    });

    const irAPaso = (paso) => {
      for (let i = 1; i <= 3; i++) {
        document.getElementById(`step-${i}`).classList.toggle('active', i === paso);
        document.getElementById(`step-dot-${i}`).className =
          'step-indicator' + (i === paso ? ' active' : i < paso ? ' completed' : '');
        document.getElementById(`step-lbl-${i}`).classList.toggle('active', i === paso);
      }
      actualizarResumen();
    };

    formReserva.querySelectorAll('[data-ir]').forEach(boton => {
      boton.addEventListener('click', () => {
        const destino = Number(boton.dataset.ir);
        document.getElementById('aviso-1').textContent = '';
        document.getElementById('aviso-2').textContent = '';

        if (destino >= 2 && !barberoElegido()) {
          document.getElementById('aviso-1').textContent = 'Elegí un barbero para continuar.';
          return irAPaso(1);
        }
        if (destino === 3 && (!inputFecha.value || !horaElegida())) {
          document.getElementById('aviso-2').textContent = 'Elegí un día y un horario para continuar.';
          return;
        }
        irAPaso(destino);
      });
    });

    formReserva.addEventListener('change', (e) => {
      marcarSeleccion();
      actualizarResumen();
      if (e.target.name === 'barbero') actualizarHorarios();
    });

    renderCalendario();
    marcarSeleccion();
    actualizarHorarios();
    irAPaso(Number(formReserva.dataset.paso) || 1);
  }

  // 3. FORMULARIO DE CONTACTO
  const contactForm = document.getElementById('contact-form');
  if (contactForm) {
    contactForm.addEventListener('submit', (e) => {
      e.preventDefault();
      alert('¡Gracias por tu mensaje! Nos pondremos en contacto a la brevedad.');
      contactForm.reset();
    });
  }

});