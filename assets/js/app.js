/**
 * SisReserva IFRO — Scripts Vanilla JavaScript
 * Totalmente Offline - Sem dependências de bibliotecas externas
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Menu Mobile Toggle
  const toggleBtn = document.querySelector('.menu-toggle');
  const navMenu = document.querySelector('.navbar-menu');
  if (toggleBtn && navMenu) {
    toggleBtn.addEventListener('click', () => {
      navMenu.classList.toggle('open');
    });
  }

  // 2. Confirmações com data-confirm
  const confirmElements = document.querySelectorAll('[data-confirm]');
  confirmElements.forEach((el) => {
    el.addEventListener('click', (e) => {
      const msg = el.getAttribute('data-confirm') || 'Deseja realmente prosseguir com esta ação?';
      if (!confirm(msg)) {
        e.preventDefault();
      }
    });
  });

  // 3. Validação auxiliar de formulário de recusa de reserva (mínimo 10 caracteres)
  const rejectForms = document.querySelectorAll('.form-rejeitar-reserva');
  rejectForms.forEach((form) => {
    form.addEventListener('submit', (e) => {
      const reasonInput = form.querySelector('[name="rejection_reason"]');
      if (reasonInput) {
        const val = reasonInput.value.trim();
        if (val.length < 10) {
          e.preventDefault();
          alert('Atenção: A justificativa de recusa deve possuir no mínimo 10 caracteres explicativos.');
          reasonInput.focus();
        }
      }
    });
  });
});
