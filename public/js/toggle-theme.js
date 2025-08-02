// À l'initialisation de la page
if (localStorage.getItem('theme') === 'alt') {
  document.body.classList.add('theme-alt');
  themeToggleBtn.innerHTML = '<i class="bi bi-brightness-high"></i>';
}

// Au click
themeToggleBtn.addEventListener('click', () => {
  document.body.classList.toggle('theme-alt');
  const isAlt = document.body.classList.contains('theme-alt');
  localStorage.setItem('theme', isAlt ? 'alt' : 'default');
  themeToggleBtn.innerHTML = isAlt
    ? '<i class="bi bi-brightness-high"></i>'
    : '<i class="bi bi-moon-stars"></i>';
});
