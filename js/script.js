document.addEventListener('DOMContentLoaded', () => {

  // 1. CARRITO DE COMPRAS DE LA TIENDA
  let cartCount = 0;
  const cartCounterEl = document.getElementById('cart-counter');
  const cartCountEl = document.getElementById('cart-count');
  const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');

  addToCartButtons.forEach(button => {
    button.addEventListener('click', (e) => {
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


  // 2. SISTEMA DE RESERVA EN 3 PASOS (STEPPER)
  let selectedBarber = 'Mateo';
  let selectedDate = 'Septiembre 23';
  let selectedTime = '09:00h';

  // Función para cambiar de paso
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

  // Botones de navegación (Siguiente / Volver)
  document.getElementById('btn-next-1')?.addEventListener('click', () => goToStep(2));
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
    });
  });

  // Selección de Fecha en el Calendario
  document.querySelectorAll('.calendar-days .day').forEach(dayEl => {
    dayEl.addEventListener('click', () => {
      document.querySelectorAll('.calendar-days .day').forEach(d => d.classList.remove('selected'));
      dayEl.classList.add('selected');
      selectedDate = `Septiembre ${dayEl.textContent}`;
      
      const summaryDate = document.getElementById('summary-date');
      if (summaryDate) summaryDate.textContent = `📅 ${selectedDate}`;
    });
  });

  // Selección de Horario
  document.querySelectorAll('.time-slot').forEach(slot => {
    slot.addEventListener('click', () => {
      document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
      slot.classList.add('selected');
      selectedTime = slot.textContent;
      
      const summaryTime = document.getElementById('summary-time');
      if (summaryTime) summaryTime.textContent = `⏰ ${selectedTime}`;
    });
  });

  // Envío del Formulario Final
  const finalForm = document.getElementById('final-booking-form');
  if (finalForm) {
    finalForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const name = document.getElementById('booking-name').value;
      const phone = document.getElementById('booking-phone').value;
      const service = document.getElementById('booking-service').value || 'Sin especificar';

      alert(`¡Reserva confirmada!\n\nCliente: ${name}\nBarbero: ${selectedBarber}\nFecha: ${selectedDate} a las ${selectedTime}\nServicio: ${service}\n\nTe enviamos la confirmación a tu WhatsApp (${phone}).`);
      finalForm.reset();
      goToStep(1);
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
    adminBtn.addEventListener('click', () => {
      const pass = prompt('Ingresá la contraseña de administrador:');
      if (pass === 'admin123') {
        alert('¡Bienvenido Dillan! Modo administrador activado.');
      } else if (pass !== null) {
        alert('Contraseña incorrecta.');
      }
    });
  }

});