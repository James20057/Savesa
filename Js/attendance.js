// Alterna las pestañas Mark / Justify
document.querySelectorAll('.tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    document.querySelectorAll('.tab-content')
      .forEach(sec => sec.classList.add('hidden'));
    document.getElementById(tab.dataset.tab)
      .classList.remove('hidden');
  });
});
