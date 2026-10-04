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


  // 2. SISTEMA DE RESERVA EN 3 PASOS (CALENDARIO AUTOMÁTICO REAL)
  const mesesNombres = [
    "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
    "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
  ];

  // Fecha real actual (Octubre 2026)
  const realNow = new Date(2026, 9, 3); 
  let viewingYear = realNow.getFullYear();
  let viewingMonth = realNow.getMonth(); // 9 (Octubre)

  let selectedBarber = 'Mateo';
  let selectedDate = `\({mesesNombres[realNow.getMonth()]}\){realNow.getDate()}`;
  let selectedTime = '09:00h';

  // Manejo de Turnos Ocupados con localStorage
  function obtenerTurnosOcupados() {
    let turnos = localStorage.getItem('dkbza_turnos_ocupados');
    return turnos ? JSON.parse(turnos) : {};
  }

  function guardarTurnoOcupado(barbero, fecha, hora) {
    let turnos = obtenerTurnosOcupados();
    let clave = `\({barbero}-\){fecha}-${hora}`;
    turnos[clave] = true;
    localStorage.setItem('dkbza_turnos_ocupados', JSON.stringify(turnos));
  }

  function actualizarHorasDisponibles() {
    const timeSlots = document.querySelectorAll('.time-slot');
    const turnosOcupados = obtenerTurnosOcupados();

    timeSlots.forEach(slot => {
      const hora = slot.textContent.trim();
      let clave = `\({selectedBarber}-\){selectedDate}-${hora}`;

      slot.classList.remove('disabled', 'selected');
      slot.style.pointerEvents = 'auto';
      slot.style.opacity = '1';

      if (turnosOcupados[clave]) {
        slot.classList.add('disabled');
        slot.style.pointerEvents = 'none';
        slot.style.opacity = '0.3';
      }
    });

    const primerLibre = document.querySelector('.time-slot:not(.disabled)');
    if (primerLibre && !document.querySelector('.time-slot.selected:not(.disabled)')) {
      document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
      primerLibre.classList.add('selected');
      selectedTime = primerLibre.textContent.trim();
      const summaryTime = document.getElementById('summary-time');
      if (summaryTime) summaryTime.textContent = `⏰ ${selectedTime}`;
    }
  }

  // Generador Automático del Calendario
  function renderCalendar() {
    const calendarGrid = document.getElementById('calendar-days-grid');
    const monthTitle = document.getElementById('month-title');
    if (!calendarGrid || !monthTitle) return;

    monthTitle.textContent = `\({mesesNombres[viewingMonth].toUpperCase()}\){viewingYear}`;

    const firstDayIndex = new Date(viewingYear, viewingMonth, 1).getDay();
    const totalDays = new Date(viewingYear, viewingMonth + 1, 0).getDate();

    let daysHTML = `
      D
      L
      M
      X
      J
      V
      S
    `;

    // Espacios vacíos antes del día 1
    for (let i = 0; i < firstDayIndex; i++) {
      daysHTML += ``;
    }

    const isCurrentMonthYear = (viewingYear === realNow.getFullYear() && viewingMonth === realNow.getMonth());

    for (let day = 1; day <= totalDays; day++) {
      let isPast = isCurrentMonthYear && day < realNow.getDate();
      
      if (isPast) {
        daysHTML += `${day}`;
      } else {
        let isToday = (isCurrentMonthYear && day === realNow.getDate()) || (!isCurrentMonthYear && day === 1);
        let selectedClass = isToday ? "selected" : "";
        daysHTML += `${day}`;
      }
    }

    calendarGrid.innerHTML = daysHTML;

    // Seleccionar por defecto el primer día válido
    const firstValidDay = calendarGrid.querySelector('.day:not(.past-day):not(.empty)');
    if (firstValidDay) {
      calendarGrid.querySelectorAll('.day').forEach(d => d.classList.remove('selected'));
      firstValidDay.classList.add('selected');
      selectedDate = `\({mesesNombres[viewingMonth]}\){firstValidDay.getAttribute('data-day')}`;
      const summaryDate = document.getElementById('summary-date');
      if (summaryDate) summaryDate.textContent = `📅 ${selectedDate}`;
    }

    // Eventos de click en los días válidos
    calendarGrid.querySelectorAll('.day:not(.past-day)').forEach(dayEl => {
      dayEl.addEventListener('click', () => {
        calendarGrid.querySelectorAll('.day').forEach(d => d.classList.remove('selected'));
        dayEl.classList.add('selected');
        
        const dayNum = dayEl.getAttribute('data-day');
        selectedDate = `\({mesesNombres[viewingMonth]}\){dayNum}`;
        
        const summaryDate = document.getElementById('summary-date');
        if (summaryDate) summaryDate.textContent = `📅 ${selectedDate}`;

        actualizarHorasDisponibles();
      });
    });
  }

  // Controles para cambiar de mes (Flechas en el título)
  const monthBox = document.querySelector('.calendar-box');
  if (monthBox && !document.getElementById('prev-month')) {
    const titleContainer = document.getElementById('month-title');
    if (titleContainer) {
      const wrapper = document.createElement('div');
      wrapper.style.display = 'flex';
      wrapper.style.justifyContent = 'space-between';
      wrapper.style.alignItems = 'center';
      wrapper.style.width = '100%';
      wrapper.style.marginBottom = '10px';

      const btnPrev = document.createElement('button');
      btnPrev.innerHTML = '◄';
      btnPrev.id = 'prev-month';
      btnPrev.style.cssText = 'background:none; border:none; color:#f3f4f6; cursor:pointer; font-size:16px;';

      const btnNext = document.createElement('button');
      btnNext.innerHTML = '►';
      btnNext.id = 'next-month';
      btnNext.style.cssText = 'background:none; border:none; color:#f3f4f6; cursor:pointer; font-size:16px;';

      titleContainer.parentNode.replaceChild(wrapper, titleContainer);
      wrapper.appendChild(btnPrev);
      wrapper.appendChild(titleContainer);
      wrapper.appendChild(btnNext);

      btnPrev.addEventListener('click', () => {
        if (viewingYear === realNow.getFullYear() && viewingMonth <= realNow.getMonth()) return;

        viewingMonth--;
        if (viewingMonth < 0) {
          viewingMonth = 11;
          viewingYear--;
        }
        renderCalendar();
        actualizarHorasDisponibles();
      });

      btnNext.addEventListener('click', () => {
        viewingMonth++;
        if (viewingMonth > 11) {
          viewingMonth = 0;
          viewingYear++;
        }
        renderCalendar();
        actualizarHorasDisponibles();
      });
    }
  }

  // Inicializar calendario
  renderCalendar();

  // Stepper (Pasos)
  const goToStep = (stepNumber) => {
    document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
    const targetStep = document.getElementById(`step-${stepNumber}`);
    if (targetStep) targetStep.classList.add('active');

    for (let i = 1; i <= 3; i++) {
      const dot = document.getElementById(`step-dot-${i}`);
      if (dot) {
        if (i === stepNumber) {
          dot.className = 'step-indicator active';
        } else if (i < stepNumber) {
          dot.className = 'step-indicator completed';
        } else {
          dot.className = 'step-indicator';
        }
      }
    }
  };

  document.getElementById('btn-next-1')?.addEventListener('click', () => {
    goToStep(2);
    actualizarHorasDisponibles();
  });
  document.getElementById('btn-back-2')?.addEventListener('click', () => goToStep(1));
  document.getElementById('btn-next-2')?.addEventListener('click', () => goToStep(3));
  document.getElementById('btn-back-3')?.addEventListener('click', () => goToStep(2));

  // Selección de Barbero
  document.querySelectorAll('.barber-card').forEach(card => {
    card.addEventListener('click', () => {
      document.querySelectorAll('.barber-card').forEach(c => c.classList.remove('selected'));
      card.classList.add('selected');
      selectedBarber = card.getAttribute('data-barber') || 'Mateo';
      
      const summaryBarber = document.getElementById('summary-barber');
      if (summaryBarber) summaryBarber.textContent = `✂ ${selectedBarber}`;

      actualizarHorasDisponibles();
    });
  });

  // Selección de Horario
  document.querySelectorAll('.time-slot').forEach(slot => {
    slot.addEventListener('click', () => {
      if (slot.classList.contains('disabled')) return;
      document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
      slot.classList.add('selected');
      selectedTime = slot.textContent.trim();
      
      const summaryTime = document.getElementById('summary-time');
      if (summaryTime) summaryTime.textContent = `⏰ ${selectedTime}`;
    });
  });

  // Envío del Formulario Final (Corregido)
  const finalForm = document.getElementById('final-booking-form');
  if (finalForm) {
    finalForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const name = document.getElementById('booking-name').value;
      const phone = document.getElementById('booking-phone').value;
      const service = document.getElementById('booking-service').value || 'Sin especificar';

      guardarTurnoOcupado(selectedBarber, selectedDate, selectedTime);

      alert(`¡Reserva confirmada!\n\nCliente: \({name}\nBarbero:\){selectedBarber}\nFecha: \({selectedDate} a las\){selectedTime}\nServicio: ${service}`);
      
      finalForm.reset();
      goToStep(1);
      renderCalendar();
      actualizarHorasDisponibles();
    });
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

  // 4. ACCESO ADMIN
  const adminBtn = document.getElementById('admin-login-btn');
  if (adminBtn) {
    adminBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const pass = prompt('Ingresá la contraseña de administrador:');
      if (pass === 'admin123') {
        alert('¡Bienvenido Dillan! Modo administrador activado.');
        // 👈 ESTO ES LO QUE FALTABA: Acá lo mandamos al panel
        window.location.href = 'admin.html';
      } else if (pass !== null) {
        alert('Contraseña incorrecta.');
      }
    });
  }

});