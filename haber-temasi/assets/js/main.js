(function ($) {
    $(document).ready(function () {
        const header = $('.site-header');
        const toggleClass = 'is-scrolled';

        $(window).on('scroll', function () {
            if ($(this).scrollTop() > 20) {
                header.addClass(toggleClass);
            } else {
                header.removeClass(toggleClass);
            }
        });

        $('.primary-navigation').attr('role', 'navigation');

        const tickerItems = $('.breaking-news__item');

        if (tickerItems.length) {
            let currentIndex = 0;

            const setActiveItem = (index) => {
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
                setInterval(() => {
                    currentIndex = (currentIndex + 1) % tickerItems.length;
                    setActiveItem(currentIndex);
                }, 5000);
            }
        }
    });
})(jQuery);
