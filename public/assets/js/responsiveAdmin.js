
    document.addEventListener("DOMContentLoaded", () => {

        const toggle = document.querySelector('.admin-toggle');
        const menu = document.querySelector('.admin-menu');

        if (toggle && menu) {
            toggle.addEventListener('click', () => {
                menu.classList.toggle('active');
            });
        }

    });
