(function ($) {
    $(document).ready(function () {
        const body = $('body');
        body.addClass('js-enabled');

        const searchToggle = $('.mobile-header__search-toggle');
        const searchForm = $('#mobile-search');
        const searchField = $('#mobile-search-field');
        const i18n = window.haberSiteiInteract || {};

        const getMessage = function (key, fallback) {
            if (Object.prototype.hasOwnProperty.call(i18n, key) && i18n[key]) {
                return i18n[key];
            }

            return fallback;
        };

        const ensureLiveRegion = function () {
            let liveRegion = $('#mobile-live-region');

            if (!liveRegion.length) {
                liveRegion = $('<div>', {
                    id: 'mobile-live-region',
                    class: 'screen-reader-text',
                    'aria-live': 'polite',
                    'aria-atomic': 'true'
                });

                $('body').append(liveRegion);
            }

            return liveRegion;
        };

        const announce = function (message) {
            if (!message) {
                return;
            }

            const region = ensureLiveRegion();
            region.text('');

            window.requestAnimationFrame(function () {
                region.text(message);
            });
        };

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
            const currentUrl = new URL(window.location.href);
            const normalizePath = (url) => url.pathname.replace(/\/$/, '');

            bottomNavLinks.each(function () {
                try {
                    const linkUrl = new URL(this.href);
                    const samePath = normalizePath(linkUrl) === normalizePath(currentUrl);
                    const hashMatches = linkUrl.hash ? linkUrl.hash === currentUrl.hash : currentUrl.hash === '';

                    if (samePath && hashMatches) {
                        $(this).addClass('is-active');
                    }
                } catch (error) {
                    // URL parse edilemezse bağlantı aktif bırakılmaz.
                }
            });

            if (!bottomNavLinks.filter('.is-active').length) {
                bottomNavLinks.first().addClass('is-active');
            }

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

        const shareButtons = $('.js-share-button');

        if (shareButtons.length) {
            shareButtons.on('click', function (event) {
                event.preventDefault();

                const button = $(this);
                const shareTitle = button.data('shareTitle') || document.title;
                const shareUrl = button.data('shareUrl') || window.location.href;

                const shareData = {
                    title: shareTitle,
                    text: shareTitle,
                    url: shareUrl
                };

                if (navigator.share) {
                    navigator
                        .share(shareData)
                        .catch(function () {
                            // kullanıcı paylaşımı iptal etti, mesaj göstermeye gerek yok.
                        });
                    return;
                }

                const handleResult = function (success) {
                    if (success) {
                        announce(getMessage('shareCopied', 'Bağlantı panoya kopyalandı.'));
                    } else {
                        announce(getMessage('shareCopyFallback', 'Bağlantı kopyalanamadı. Lütfen paylaşım menüsünü kullanın.'));
                    }
                };

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(shareUrl).then(function () {
                        handleResult(true);
                    }).catch(function () {
                        handleResult(false);
                    });

                    return;
                }

                const tempInput = $('<input type="text" aria-hidden="true" />');
                $('body').append(tempInput);
                tempInput.val(shareUrl).trigger('focus').trigger('select');

                let didCopy = false;

                try {
                    didCopy = document.execCommand('copy');
                } catch (error) {
                    didCopy = false;
                }

                tempInput.remove();
                button.trigger('focus');
                handleResult(didCopy);
            });
        }

        const saveButtons = $('.js-save-button');

        if (saveButtons.length) {
            const storageKey = 'haberSavedPosts';

            const readSaved = function () {
                try {
                    const raw = window.localStorage.getItem(storageKey);

                    if (!raw) {
                        return [];
                    }

                    const parsed = JSON.parse(raw);

                    if (Array.isArray(parsed)) {
                        return parsed
                            .map(function (value) { return parseInt(value, 10); })
                            .filter(function (value) { return !Number.isNaN(value); });
                    }
                } catch (error) {
                    return [];
                }

                return [];
            };

            const writeSaved = function (ids) {
                try {
                    window.localStorage.setItem(storageKey, JSON.stringify(ids));
                } catch (error) {
                    // depolama sınırına ulaşıldı, sessizce yoksay.
                }
            };

            const updateButtonState = function (button, isSaved) {
                button.toggleClass('is-saved', isSaved);
                button.attr('aria-pressed', isSaved ? 'true' : 'false');

                const labelSaved = button.data('labelSaved');
                const labelDefault = button.data('labelSave');
                const labelElement = button.find('.mobile-hero__action-text, .single-share__link-text');

                if (labelElement.length && labelSaved && labelDefault) {
                    labelElement.text(isSaved ? labelSaved : labelDefault);
                } else if (labelSaved && labelDefault) {
                    button.text(isSaved ? labelSaved : labelDefault);
                }
            };

            let savedIds = readSaved();

            saveButtons.each(function () {
                const button = $(this);
                const postId = parseInt(button.data('postId'), 10);

                if (Number.isNaN(postId)) {
                    return;
                }

                if (savedIds.includes(postId)) {
                    updateButtonState(button, true);
                }

                button.on('click', function (event) {
                    event.preventDefault();

                    savedIds = readSaved();
                    const isSaved = savedIds.includes(postId);

                    if (isSaved) {
                        savedIds = savedIds.filter(function (value) { return value !== postId; });
                        updateButtonState(button, false);
                    } else {
                        savedIds.push(postId);
                        updateButtonState(button, true);
                        announce(getMessage('savedLabel', 'Kaydedildi'));
                    }

                    writeSaved(savedIds);
                });
            });
        }
    });
})(jQuery);
