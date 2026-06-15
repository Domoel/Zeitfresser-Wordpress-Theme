document.addEventListener('DOMContentLoaded', function () {
    var scrollToTop = document.querySelector('.scroll-to-top');
    var navToggle = document.getElementById('nav-icon3');

    function updateScrollButton() {
        if (!scrollToTop) {
            return;
        }

        if (window.scrollY > 1) {
            scrollToTop.classList.add('show');
        } else {
            scrollToTop.classList.remove('show');
        }
    }

    if (scrollToTop) {
        updateScrollButton();

        window.addEventListener('scroll', updateScrollButton, { passive: true });

        scrollToTop.addEventListener('click', function (event) {
            event.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    if (navToggle) {
        navToggle.addEventListener('click', function () {
            navToggle.classList.toggle('open');
        });
    }
});
