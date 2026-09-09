import Chart from 'chart.js/auto';
import Alpine from 'alpinejs';

window.Chart = Chart;

if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();
}

document.addEventListener('DOMContentLoaded', () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.reveal').forEach((el) => observer.observe(el));
});
