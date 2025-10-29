(function () {
    const quickLinks = document.querySelectorAll('.haber-portal__quick-link[data-target]');
    const notice = document.querySelector('.haber-portal__notice');
    const chartCard = document.querySelector('.haber-portal__chart-card');

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function smoothScrollTo(targetId) {
        const section = document.getElementById(targetId);
        if (!section) {
            return;
        }

        const offsetTop = section.getBoundingClientRect().top + window.pageYOffset - 80;

        if (prefersReducedMotion) {
            window.location.hash = '#' + targetId;
            return;
        }

        window.scrollTo({
            top: offsetTop,
            behavior: 'smooth',
        });
    }

    quickLinks.forEach((button) => {
        button.addEventListener('click', () => {
            const target = button.getAttribute('data-target');
            smoothScrollTo(target);
            quickLinks.forEach((btn) => btn.classList.remove('is-active'));
            button.classList.add('is-active');
        });
    });

    if (notice) {
        setTimeout(() => {
            notice.setAttribute('aria-hidden', 'true');
            notice.classList.add('is-dismissed');
        }, 6000);
    }

    function renderChart() {
        if (!chartCard) {
            return;
        }

        const canvas = chartCard.querySelector('canvas');
        if (!canvas) {
            return;
        }

        const points = chartCard.getAttribute('data-chart-points');
        if (!points) {
            return;
        }

        let values;
        try {
            values = JSON.parse(points);
        } catch (err) {
            values = [];
        }

        if (!Array.isArray(values) || values.length === 0) {
            return;
        }

        const ctx = canvas.getContext('2d');
        const width = canvas.width = canvas.offsetWidth;
        const height = canvas.height = canvas.offsetHeight;

        ctx.clearRect(0, 0, width, height);
        ctx.lineWidth = 3;
        ctx.lineJoin = 'round';
        ctx.lineCap = 'round';

        const max = Math.max.apply(null, values);
        const min = Math.min.apply(null, values);
        const range = max - min || 1;

        const padding = 16;
        const step = (width - padding * 2) / Math.max(values.length - 1, 1);

        ctx.beginPath();
        ctx.strokeStyle = 'rgba(255,255,255,0.35)';
        ctx.moveTo(padding, height - padding);
        ctx.lineTo(width - padding, height - padding);
        ctx.stroke();

        const gradient = ctx.createLinearGradient(0, 0, width, height);
        gradient.addColorStop(0, 'rgba(217, 32, 55, 0.7)');
        gradient.addColorStop(1, 'rgba(63, 140, 255, 0.65)');

        ctx.beginPath();
        values.forEach((value, index) => {
            const x = padding + index * step;
            const y = height - padding - ((value - min) / range) * (height - padding * 2);
            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        });
        ctx.strokeStyle = gradient;
        ctx.stroke();

        values.forEach((value, index) => {
            const x = padding + index * step;
            const y = height - padding - ((value - min) / range) * (height - padding * 2);
            ctx.beginPath();
            ctx.arc(x, y, 4, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(255,255,255,0.9)';
            ctx.fill();
            ctx.beginPath();
            ctx.arc(x, y, 8, 0, Math.PI * 2);
            ctx.strokeStyle = 'rgba(217, 32, 55, 0.3)';
            ctx.stroke();
        });
    }

    renderChart();
    window.addEventListener('resize', renderChart);

    const urlParams = new URLSearchParams(window.location.search);
    const initialSection = urlParams.get('portal-section');
    if (initialSection) {
        smoothScrollTo(initialSection);
    }
})();
