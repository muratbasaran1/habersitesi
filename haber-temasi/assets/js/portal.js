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

    const commandWrapper = document.querySelector('[data-portal-command]');

    if (commandWrapper) {
        const commandInput = commandWrapper.querySelector('#haber-portal-command-input');
        const dismissButtons = commandWrapper.querySelectorAll('[data-command-dismiss]');
        const commandListItems = Array.from(commandWrapper.querySelectorAll('.haber-portal__command-list li'));
        const commandEntries = commandListItems
            .map((listItem) => {
                const element = listItem.querySelector('.haber-portal__command-item');
                if (!element) {
                    return null;
                }

                return {
                    element,
                    item: listItem,
                    text: (element.textContent || '').toLowerCase(),
                };
            })
            .filter(Boolean);

        let commandOpen = false;
        let activeIndex = -1;

        const getVisibleEntries = () => commandEntries.filter(({ item }) => !item.classList.contains('is-hidden'));

        const highlightEntry = (index, focusElement) => {
            const visible = getVisibleEntries();

            visible.forEach(({ element }) => {
                element.setAttribute('aria-selected', 'false');
            });

            if (!visible.length) {
                activeIndex = -1;
                return;
            }

            const safeIndex = ((index % visible.length) + visible.length) % visible.length;
            const activeEntry = visible[safeIndex];

            activeEntry.element.setAttribute('aria-selected', 'true');

            if (focusElement) {
                activeEntry.element.focus();
            }

            activeIndex = safeIndex;
        };

        const resetPalette = () => {
            commandEntries.forEach(({ item, element }) => {
                item.classList.remove('is-hidden');
                element.setAttribute('aria-selected', 'false');
            });
            activeIndex = -1;
            highlightEntry(0, false);
        };

        const filterPalette = (term) => {
            const query = term.trim().toLowerCase();

            commandEntries.forEach(({ item, element, text }) => {
                const matches = !query || text.indexOf(query) !== -1;
                item.classList.toggle('is-hidden', !matches);
                element.setAttribute('aria-selected', 'false');
            });

            const visible = getVisibleEntries();

            if (visible.length) {
                highlightEntry(0, false);
            } else {
                activeIndex = -1;
            }
        };

        const closePalette = () => {
            if (!commandOpen) {
                return;
            }

            commandWrapper.setAttribute('hidden', 'hidden');
            commandWrapper.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('portal-command-open');
            commandOpen = false;
            activeIndex = -1;
        };

        const triggerCommand = (element) => {
            if (!element) {
                return;
            }

            const target = element.getAttribute('data-command-target');

            closePalette();

            if (target) {
                smoothScrollTo(target);
                quickLinks.forEach((button) => {
                    if (button.getAttribute('data-target') === target) {
                        quickLinks.forEach((btn) => btn.classList.remove('is-active'));
                        button.classList.add('is-active');
                    }
                });
            }
        };

        const openPalette = () => {
            if (commandOpen) {
                return;
            }

            commandWrapper.removeAttribute('hidden');
            commandWrapper.setAttribute('aria-hidden', 'false');
            document.body.classList.add('portal-command-open');
            commandOpen = true;
            resetPalette();

            if (commandInput) {
                commandInput.value = '';
                window.requestAnimationFrame(() => {
                    commandInput.focus();
                });
            }
        };

        commandEntries.forEach(({ element }) => {
            element.addEventListener('click', (event) => {
                if (!element.hasAttribute('data-command-link')) {
                    event.preventDefault();
                }
                triggerCommand(element);
            });
        });

        if (commandInput) {
            commandInput.addEventListener('input', () => {
                filterPalette(commandInput.value);
            });

            commandInput.addEventListener('keydown', (event) => {
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    highlightEntry(activeIndex === -1 ? 0 : activeIndex + 1, true);
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    const visible = getVisibleEntries();
                    if (!visible.length) {
                        return;
                    }
                    const nextIndex = activeIndex === -1 ? visible.length - 1 : activeIndex - 1;
                    highlightEntry(nextIndex, true);
                } else if (event.key === 'Enter') {
                    const visible = getVisibleEntries();
                    if (activeIndex >= 0 && visible[activeIndex]) {
                        event.preventDefault();
                        triggerCommand(visible[activeIndex].element);
                    }
                }
            });
        }

        commandWrapper.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                event.preventDefault();
                closePalette();
            }
        });

        dismissButtons.forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                closePalette();
            });
        });

        commandWrapper.addEventListener('click', (event) => {
            const target = event.target;
            if (target instanceof Element && target.hasAttribute('data-command-dismiss')) {
                event.preventDefault();
                closePalette();
            }
        });

        const platform = typeof navigator !== 'undefined' && navigator.platform ? navigator.platform : '';

        document.addEventListener('keydown', (event) => {
            const isMac = platform.toUpperCase().indexOf('MAC') >= 0;
            const primaryKey = isMac ? event.metaKey : event.ctrlKey;

            if (primaryKey && event.key && event.key.toLowerCase() === 'k') {
                event.preventDefault();

                if (commandOpen) {
                    closePalette();
                } else {
                    openPalette();
                }
            }
        });

        commandWrapper.addEventListener('focusout', (event) => {
            if (!commandOpen) {
                return;
            }

            if (event.relatedTarget && commandWrapper.contains(event.relatedTarget)) {
                return;
            }

            closePalette();
        });
    }

    const urlParams = new URLSearchParams(window.location.search);
    const initialSection = urlParams.get('portal-section');
    if (initialSection) {
        smoothScrollTo(initialSection);
    }
})();
