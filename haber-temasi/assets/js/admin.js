(function () {
    'use strict';

    function normalize(value) {
        if (!value) {
            return '';
        }

        let normalized = value.toString().trim();

        if (!normalized) {
            return '';
        }

        try {
            normalized = normalized.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        } catch (error) {
            // Tarayıcı desteği yoksa normalleştirme adımı atlanır.
        }

        return normalized.toLowerCase();
    }

    function formatNumber(value) {
        const numberValue = Number(value) || 0;

        try {
            const locale = document.documentElement.lang || 'tr-TR';
            return new Intl.NumberFormat(locale).format(numberValue);
        } catch (error) {
            return numberValue.toString();
        }
    }

    function updateResultsText(element, count) {
        if (!element) {
            return;
        }

        const singular = element.dataset.templateSingular || '%s';
        const plural = element.dataset.templatePlural || singular;
        const template = count === 1 ? singular : plural;
        element.textContent = template.replace('%s', formatNumber(count));
    }

    document.addEventListener('DOMContentLoaded', function () {
        const adminWrap = document.querySelector('.haber-sitesi-admin');

        if (!adminWrap) {
            return;
        }

        const searchInput = adminWrap.querySelector('[data-staff-search]');
        const roleButtons = Array.from(adminWrap.querySelectorAll('.haber-sitesi-role-filter .button'));
        const sections = Array.from(adminWrap.querySelectorAll('[data-role-section]'));
        const resultsText = adminWrap.querySelector('[data-staff-results]');
        let activeRole = 'all';

        function applyFilters() {
            const query = normalize(searchInput ? searchInput.value : '');
            let visibleTotal = 0;

            sections.forEach(function (section) {
                const list = section.querySelector('[data-role-list]');
                const emptyMessage = section.querySelector('.haber-sitesi-admin__empty');
                const badge = section.querySelector('[data-role-count]');

                if (!list) {
                    section.classList.remove('is-muted');

                    if (emptyMessage) {
                        emptyMessage.hidden = false;
                    }

                    return;
                }

                const items = Array.from(list.querySelectorAll('li'));
                let visibleInSection = 0;

                items.forEach(function (item) {
                    const role = item.dataset.role || '';
                    const searchValue = item.dataset.search || '';
                    const matchesRole = activeRole === 'all' || role === activeRole;
                    const matchesSearch = !query || searchValue.indexOf(query) !== -1;
                    const shouldShow = matchesRole && matchesSearch;

                    item.classList.toggle('is-hidden', !shouldShow);

                    if (shouldShow) {
                        visibleInSection += 1;
                    }
                });

                if (badge) {
                    badge.textContent = formatNumber(visibleInSection);
                }

                if (emptyMessage) {
                    emptyMessage.hidden = visibleInSection > 0;
                }

                section.classList.toggle('is-muted', visibleInSection === 0 && items.length > 0);
                visibleTotal += visibleInSection;
            });

            updateResultsText(resultsText, visibleTotal);
        }

        roleButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const role = button.dataset.role || 'all';

                if (role === activeRole) {
                    return;
                }

                activeRole = role;

                roleButtons.forEach(function (btn) {
                    btn.classList.toggle('is-active', btn === button);
                });

                applyFilters();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                window.requestAnimationFrame(applyFilters);
            });
        }

        applyFilters();

        const activityChart = adminWrap.querySelector('[data-activity-chart]');

        if (activityChart) {
            const revealChart = function () {
                activityChart.classList.add('is-visible');
            };

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver(
                    function (entries, obs) {
                        entries.forEach(function (entry) {
                            if (entry.isIntersecting) {
                                revealChart();
                                obs.disconnect();
                            }
                        });
                    },
                    { threshold: 0.35 }
                );

                observer.observe(activityChart);
            } else {
                window.setTimeout(revealChart, 240);
            }
        }
    });
})();
