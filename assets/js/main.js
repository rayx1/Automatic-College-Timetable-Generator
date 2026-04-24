document.addEventListener('DOMContentLoaded', function () {
    const confirms = document.querySelectorAll('[data-confirm]');

    confirms.forEach(function (element) {
        element.addEventListener('click', function (event) {
            const message = element.getAttribute('data-confirm') || 'Are you sure?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
});

