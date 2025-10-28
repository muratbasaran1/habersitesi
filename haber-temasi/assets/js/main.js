(function ($) {
    $(document).ready(function () {
        const body = $('body');
        body.addClass('js-enabled');

        const searchToggle = $('.mobile-header__search-toggle');
        const searchForm = $('#mobile-search');
        const searchField = $('#mobile-search-field');

        searchToggle.on('click', function () {
            const isExpanded = $(this).attr('aria-expanded') === 'true';
            $(this).attr('aria-expanded', !isExpanded);
            searchForm.toggleClass('is-open', !isExpanded);
            if (!isExpanded) {
                window.requestAnimationFrame(function () {
                    searchField.trigger('focus');
                });
            }
        });

        searchField.on('keydown', function (event) {
            if (event.key === 'Escape') {
                searchToggle.trigger('click');
                searchToggle.trigger('focus');
            }
        });

        $(document).on('click', function (event) {
            if (!$(event.target).closest('.mobile-header__search-toggle, #mobile-search').length) {
                if (searchForm.hasClass('is-open')) {
                    searchToggle.attr('aria-expanded', 'false');
                    searchForm.removeClass('is-open');
                }
            }
        });

        const categoryLinks = $('.mobile-category-nav__link');
        if (categoryLinks.length) {
            const currentUrl = window.location.href.replace(/#.+$/, '');
            let hasActive = false;
            categoryLinks.each(function () {
                const linkUrl = this.href.replace(/#.+$/, '');
                if (currentUrl === linkUrl) {
                    $(this).addClass('is-active');
                    hasActive = true;
                }
            });

            if (!hasActive) {
                categoryLinks.first().addClass('is-active');
            }

            categoryLinks.on('click', function () {
                categoryLinks.removeClass('is-active');
                $(this).addClass('is-active');
            });
        }

        const tickerItems = $('.mobile-breaking-news__item');

        if (tickerItems.length) {
            let currentIndex = 0;

            const setActiveItem = function (index) {
                tickerItems
                    .removeClass('is-active')
                    .attr('aria-hidden', 'true')
                    .attr('tabindex', '-1');

                tickerItems
                    .eq(index)
                    .addClass('is-active')
                    .attr('aria-hidden', 'false')
                    .attr('tabindex', '0');
            };

            setActiveItem(currentIndex);

            if (tickerItems.length > 1) {
                setInterval(function () {
                    currentIndex = (currentIndex + 1) % tickerItems.length;
                    setActiveItem(currentIndex);
                }, 6000);
            }
        }

        const bottomNavLinks = $('.mobile-bottom-nav__link');
        if (bottomNavLinks.length) {
            const currentUrl = window.location.href.replace(/#.+$/, '');
            bottomNavLinks.each(function () {
                const linkUrl = this.href.replace(/#.+$/, '');
                if (currentUrl === linkUrl) {
                    $(this).addClass('is-active');
                }
            });

            bottomNavLinks.on('click', function () {
                bottomNavLinks.removeClass('is-active');
                $(this).addClass('is-active');
            });
        }

        const interactiveCards = $('article');
        interactiveCards.on('mousedown keydown', function () {
            $(this).addClass('is-pressed');
        });

        interactiveCards.on('mouseup mouseleave keyup', function () {
            $(this).removeClass('is-pressed');
        });
    });
})(jQuery);
