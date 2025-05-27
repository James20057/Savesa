(function() {
  'use strict';

  /**
   * Inicializa los listeners de la sidebar para marcar el item activo.
   */
  function initSidebar() {
    const items = document.querySelectorAll('.sidebar-item');
    items.forEach(item => {
      item.addEventListener('click', () => {
        items.forEach(i => i.classList.remove('active'));
        item.classList.add('active');
        // TODO: aquí podrías manejar navegación o carga de sección
      });
    });
  }

  /**
   * Maneja los clicks de los botones de Overview.
   * @param {MouseEvent} event 
   */
  function handleOverviewClick(event) {
    const btn = event.target.closest('button');
    if (!btn) return;
    const action = btn.dataset.action;
    switch(action) {
      case 'view-details':
        alert('Ver detalles de asistencia (pendiente)');
        break;
      case 'join-class':
        alert('Unirse a la clase (pendiente)');
        break;
      case 'view-tasks':
        alert('Ver tareas pendientes (pendiente)');
        break;
    }
  }

  /**
   * Punto de entrada: inicializa toda la lógica del Dashboard.
   */
  function initDashboard() {
    initSidebar();
    const cardsGrid = document.querySelector('.cards-grid');
    cardsGrid.addEventListener('click', handleOverviewClick);
  }

  // Arranca cuando el DOM esté listo
  document.addEventListener('DOMContentLoaded', initDashboard);
})();


