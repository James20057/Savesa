/**
 * Maneja el cambio de rol cuando se hace click en las pestañas.
 */
function initRoleTabs() {
  const tabs      = document.querySelectorAll('.tab');
  const roleInput = document.getElementById('role');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      roleInput.value = tab.dataset.role;
    });
  });
}

/**
 * Valida el formulario y lo envía por AJAX usando fetch.
 */
function initFormSubmission() {
  const form = document.getElementById('registerForm');

  form.addEventListener('submit', event => {
    event.preventDefault();

    const fullName        = document.getElementById('fullName').value.trim();
    const email           = document.getElementById('email').value.trim();
    const idCard          = document.getElementById('idCard').value.trim();
    const password        = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    // Validaciones básicas
    if (!fullName || !email || !idCard || !password || !confirmPassword) {
      alert('Por favor, completa todos los campos.');
      return;
    }

    if (password !== confirmPassword) {
      alert('Las contraseñas no coinciden.');
      return;
    }

    // Envío de datos al servidor
    fetch(form.action, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        role: document.getElementById('role').value,
        fullName,
        email,
        idCard,
        password
      })
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert('Registro exitoso. Ahora puedes iniciar sesión.');
          window.location.href = 'login.html';
        } else {
          alert(data.message || 'Error en el registro.');
        }
      })
      .catch(err => {
        console.error(err);
        alert('Ocurrió un error. Intenta de nuevo.');
      });
  });
}

/**
 * Punto de entrada: inicializa la lógica del registro.
 */
document.addEventListener('DOMContentLoaded', () => {
  initRoleTabs();
  initFormSubmission();
});
