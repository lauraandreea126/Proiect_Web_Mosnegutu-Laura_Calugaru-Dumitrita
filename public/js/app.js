/**
 * awa - actor awards visualizer
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // buton inapoi sus
    const backToTopBtn = document.getElementById('back-to-top');

    // arata/ascunde butonul la scroll
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    });

    // scroll lin catre inceput la click
    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    console.log('awa - design pregatit');
});
