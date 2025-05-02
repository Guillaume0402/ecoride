document.addEventListener('DOMContentLoaded', function () {
    const burgerButton = document.querySelector('.navbar-toggler');
    const navbarMenu = document.getElementById('navbarNavDropdown');

    if (burgerButton && navbarMenu) {
        burgerButton.addEventListener('click', function () {
            const isCollapsed = navbarMenu.classList.contains('show');

            if (isCollapsed) {
                // Le menu est ouvert → on ferme
                navbarMenu.classList.remove('show');
            } else {
                // Le menu est fermé → on ouvre
                navbarMenu.classList.add('show');
            }
        });
    }
});
