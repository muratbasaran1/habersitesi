(function ($) {
    $(document).ready(function () {
        const body = $('body');
        body.addClass('js-enabled');

        const searchToggle = $('.mobile-header__search-toggle');
        const searchForm = $('#mobile-search');
        const searchField = $('#mobile-search-field');
        const i18n = window.haberSitesiInteract || {};
        const prefersReducedMotionGlobal = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

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

        const tickerGroups = $('[data-breaking-ticker]');

        tickerGroups.each(function () {
            const group = $(this);
            const items = group.find('[data-breaking-item]');

            if (!items.length) {
                return;
            }

            let currentIndex = items.index(items.filter('.is-active').first());

            if (currentIndex < 0) {
                currentIndex = 0;
            }

            const setActiveItem = function (index) {
                if (!items.length) {
                    return;
                }

                const safeIndex = ((index % items.length) + items.length) % items.length;

                items
                    .removeClass('is-active')
                    .attr('aria-hidden', 'true')
                    .attr('tabindex', '-1');

                const activeItem = items.eq(safeIndex);

                activeItem
                    .addClass('is-active')
                    .attr('aria-hidden', 'false')
                    .attr('tabindex', '0');

                currentIndex = safeIndex;
            };

            setActiveItem(currentIndex);

            if (items.length > 1) {
                setInterval(function () {
                    setActiveItem(currentIndex + 1);
                }, 6000);
            }
        });

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

        const quickDockContainers = $('[data-quick-dock]');

        quickDockContainers.each(function (index) {
            const dock = $(this);
            const toggle = dock.find('[data-quick-dock-toggle]');
            const panel = dock.find('[data-quick-dock-panel]');

            if (!toggle.length || !panel.length) {
                return;
            }

            const namespace = '.quickDock' + index;
            const focusableSelector = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';

            const closeDock = function (shouldFocusToggle) {
                dock.removeClass('is-open');
                toggle.attr('aria-expanded', 'false');
                panel.attr('aria-hidden', 'true');

                if (shouldFocusToggle) {
                    toggle.trigger('focus');
                }
            };

            const openDock = function () {
                dock.addClass('is-open');
                toggle.attr('aria-expanded', 'true');
                panel.attr('aria-hidden', 'false');

                window.requestAnimationFrame(function () {
                    const focusable = panel.find(focusableSelector);

                    if (focusable.length) {
                        focusable.first().trigger('focus');
                    }
                });
            };

            toggle.on('click', function () {
                if (dock.hasClass('is-open')) {
                    closeDock(false);
                } else {
                    openDock();
                }
            });

            dock.on('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeDock(true);
                }
            });

            $(document).on('click' + namespace, function (event) {
                if (!$(event.target).closest('[data-quick-dock]').length && dock.hasClass('is-open')) {
                    closeDock(false);
                }
            });

            $(window).on('unload' + namespace, function () {
                $(document).off('click' + namespace);
            });
        });

        const briefingContainers = $('[data-briefing]');

        briefingContainers.each(function () {
            const container = $(this);
            const tabs = container.find('[data-briefing-tab]');
            const panels = container.find('[data-briefing-panel]');

            if (!tabs.length || !panels.length) {
                return;
            }

            let currentIndex = tabs.index(tabs.filter('.is-active').first());

            if (currentIndex < 0) {
                currentIndex = 0;
            }

            let rotateTimer = null;

            const setActive = function (index, shouldAnnounce = true) {
                if (!tabs.length) {
                    return;
                }

                const safeIndex = ((index % tabs.length) + tabs.length) % tabs.length;

                tabs
                    .removeClass('is-active')
                    .attr('aria-selected', 'false');

                panels.each(function (panelIndex, panelElement) {
                    const panel = $(panelElement);

                    if (panelIndex === safeIndex) {
                        panel.addClass('is-active').removeAttr('hidden').attr('tabindex', '0');
                    } else {
                        panel.removeClass('is-active').attr('hidden', 'hidden').attr('tabindex', '-1');
                    }
                });

                const activeTab = tabs.eq(safeIndex);
                activeTab.addClass('is-active').attr('aria-selected', 'true');

                currentIndex = safeIndex;

                if (shouldAnnounce) {
                    const label = activeTab.find('.front-briefing__tab-name').text() || activeTab.text();
                    if (label) {
                        const template = getMessage('briefing_switched', 'Ajanda bölümü güncellendi:');
                        announce(template + ' ' + label.trim());
                    }
                }
            };

            setActive(currentIndex, false);

            const stopRotation = function () {
                if (rotateTimer) {
                    window.clearInterval(rotateTimer);
                    rotateTimer = null;
                }
            };

            const startRotation = function () {
                if (prefersReducedMotionGlobal || tabs.length < 2) {
                    return;
                }

                stopRotation();

                rotateTimer = window.setInterval(function () {
                    setActive(currentIndex + 1, false);
                }, 9000);
            };

            tabs.on('click', function (event) {
                event.preventDefault();
                const index = tabs.index($(this));
                setActive(index);
                stopRotation();
                window.requestAnimationFrame(startRotation);
            });

            tabs.on('keydown', function (event) {
                let targetIndex = null;

                switch (event.key) {
                    case 'ArrowRight':
                    case 'ArrowDown':
                        targetIndex = currentIndex + 1;
                        break;
                    case 'ArrowLeft':
                    case 'ArrowUp':
                        targetIndex = currentIndex - 1;
                        break;
                    case 'Home':
                        targetIndex = 0;
                        break;
                    case 'End':
                        targetIndex = tabs.length - 1;
                        break;
                    default:
                        break;
                }

                if (targetIndex !== null) {
                    event.preventDefault();
                    setActive(targetIndex);
                    tabs.eq(currentIndex).trigger('focus');
                    stopRotation();
                }
            });

            container.on('mouseenter focusin', stopRotation);
            container.on('mouseleave focusout', function (event) {
                if (!container.has(event.relatedTarget).length) {
                    startRotation();
                }
            });

            if (!prefersReducedMotionGlobal && tabs.length > 1) {
                const handleVisibility = function () {
                    if (document.hidden) {
                        stopRotation();
                    } else {
                        startRotation();
                    }
                };

                document.addEventListener('visibilitychange', handleVisibility);

                $(window).on('unload', function () {
                    document.removeEventListener('visibilitychange', handleVisibility);
                });
            }

            startRotation();
        });

        const sliderContainers = $('[data-front-slider]');

        if (sliderContainers.length) {
            const motionQuery = typeof window.matchMedia === 'function'
                ? window.matchMedia('(prefers-reduced-motion: reduce)')
                : null;

            const prefersReducedMotion = function () {
                return motionQuery ? motionQuery.matches : false;
            };

            sliderContainers.each(function () {
                const container = $(this);
                const tabs = container.find('.front-slider__thumb');
                const panels = container.find('.front-slider__panel');

                if (!tabs.length || !panels.length) {
                    return;
                }

                let activeIndex = tabs.filter('.is-active').first().data('index');

                if (typeof activeIndex === 'undefined') {
                    activeIndex = 0;
                }

                const setActive = function (index, shouldAnnounce) {
                    if (!panels.length) {
                        return;
                    }

                    const safeIndex = ((index % panels.length) + panels.length) % panels.length;

                    tabs
                        .removeClass('is-active')
                        .attr('aria-selected', 'false');

                    panels
                        .removeClass('is-active')
                        .attr('aria-hidden', 'true')
                        .attr('tabindex', '-1');

                    const currentTab = tabs.eq(safeIndex);
                    const currentPanel = panels.eq(safeIndex);

                    currentTab
                        .addClass('is-active')
                        .attr('aria-selected', 'true');

                    currentPanel
                        .addClass('is-active')
                        .attr('aria-hidden', 'false')
                        .attr('tabindex', '0');

                    activeIndex = safeIndex;

                    if (shouldAnnounce) {
                        const headline = currentTab.find('.front-slider__thumb-title').text().trim();

                        if (headline) {
                            announce(headline);
                        }
                    }
                };

                let rotationTimer = null;

                const canAutoRotate = function () {
                    if (panels.length <= 1) {
                        return false;
                    }

                    if (document.hidden) {
                        return false;
                    }

                    return !prefersReducedMotion();
                };

                const startAuto = function () {
                    if (!canAutoRotate()) {
                        return;
                    }

                    stopAuto();

                    rotationTimer = window.setInterval(function () {
                        setActive(activeIndex + 1, false);
                    }, 8000);
                };

                const stopAuto = function () {
                    if (rotationTimer) {
                        window.clearInterval(rotationTimer);
                        rotationTimer = null;
                    }
                };

                tabs.on('click', function (event) {
                    event.preventDefault();

                    const nextIndex = parseInt($(this).data('index'), 10);

                    if (!Number.isNaN(nextIndex)) {
                        setActive(nextIndex, true);
                        stopAuto();
                        startAuto();
                    }
                });

                tabs.on('keydown', function (event) {
                    if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
                        event.preventDefault();
                        setActive(activeIndex + 1, true);
                        stopAuto();
                        startAuto();
                    }

                    if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
                        event.preventDefault();
                        setActive(activeIndex - 1, true);
                        stopAuto();
                        startAuto();
                    }
                });

                container.on('mouseenter focusin', stopAuto);
                container.on('mouseleave focusout', startAuto);

                const handleVisibilityChange = function () {
                    if (document.hidden) {
                        stopAuto();
                    } else {
                        startAuto();
                    }
                };

                document.addEventListener('visibilitychange', handleVisibilityChange);

                if (motionQuery) {
                    const handleMotionPreference = function () {
                        if (prefersReducedMotion()) {
                            stopAuto();
                        } else {
                            startAuto();
                        }
                    };

                    if (typeof motionQuery.addEventListener === 'function') {
                        motionQuery.addEventListener('change', handleMotionPreference);
                    } else if (typeof motionQuery.addListener === 'function') {
                        motionQuery.addListener(handleMotionPreference);
                    }
                }

                setActive(activeIndex, false);
                startAuto();
            });
        }

        const proCarousels = $('[data-pro-carousel]');

        if (proCarousels.length) {
            proCarousels.each(function () {
                const carousel = $(this);
                const track = carousel.find('[data-pro-track]');
                const prev = carousel.find('[data-pro-prev]');
                const next = carousel.find('[data-pro-next]');
                const items = track.children();

                if (!track.length || !items.length) {
                    prev.prop('disabled', true);
                    next.prop('disabled', true);
                    return;
                }

                if (items.length <= 1) {
                    prev.prop('disabled', true);
                    next.prop('disabled', true);
                }

                const trackNode = track.get(0);

                const getStep = function () {
                    if (!items.length) {
                        return track.outerWidth() || 0;
                    }

                    const firstItem = items.first();
                    const width = firstItem.outerWidth(true);

                    if (!width) {
                        return track.outerWidth() || 0;
                    }

                    return width;
                };

                const scrollBy = function (direction) {
                    if (!trackNode) {
                        return;
                    }

                    const step = getStep();

                    if (!step) {
                        return;
                    }

                    trackNode.scrollBy({
                        left: step * direction,
                        behavior: 'smooth'
                    });
                };

                prev.on('click', function () {
                    scrollBy(-1);
                });

                next.on('click', function () {
                    scrollBy(1);
                });

                track.on('keydown', function (event) {
                    if (event.key === 'ArrowRight') {
                        event.preventDefault();
                        scrollBy(1);
                    } else if (event.key === 'ArrowLeft') {
                        event.preventDefault();
                        scrollBy(-1);
                    }
                });
            });
        }

        const spotlightGroups = $('[data-spotlight-scroll]');

        if (spotlightGroups.length) {
            spotlightGroups.each(function () {
                const group = $(this);
                const list = group.find('[data-spotlight-list]');

                if (!list.length) {
                    return;
                }

                const node = list.get(0);
                const prev = group.find('[data-spotlight-prev]');
                const next = group.find('[data-spotlight-next]');

                const setDisabled = function (button, disabled) {
                    if (!button.length) {
                        return;
                    }

                    button.toggleClass('is-disabled', disabled);
                    button.prop('disabled', disabled);
                    button.attr('aria-disabled', disabled ? 'true' : 'false');
                };

                const updateState = function () {
                    if (!node) {
                        return;
                    }

                    const maxScroll = node.scrollWidth - node.clientWidth;
                    const current = node.scrollLeft;
                    const hasOverflow = maxScroll > 4;

                    group.toggleClass('has-overflow', hasOverflow);

                    if (!hasOverflow) {
                        setDisabled(prev, true);
                        setDisabled(next, true);
                        return;
                    }

                    setDisabled(prev, current <= 4);
                    setDisabled(next, current >= (maxScroll - 4));
                };

                const scrollByAmount = function (direction) {
                    if (!node) {
                        return;
                    }

                    const delta = node.clientWidth * 0.9 * direction;
                    const maxScroll = Math.max(0, node.scrollWidth - node.clientWidth);
                    const target = Math.max(0, Math.min(node.scrollLeft + delta, maxScroll));

                    if (typeof node.scrollTo === 'function') {
                        node.scrollTo({ left: target, behavior: 'smooth' });
                    } else {
                        node.scrollLeft = target;
                    }

                    window.requestAnimationFrame(updateState);
                };

                prev.on('click', function (event) {
                    event.preventDefault();
                    scrollByAmount(-1);
                });

                next.on('click', function (event) {
                    event.preventDefault();
                    scrollByAmount(1);
                });

                list.on('scroll', function () {
                    window.requestAnimationFrame(updateState);
                });

                $(window).on('resize', updateState);

                updateState();
            });
        }

        const interactiveCards = $('article');
        interactiveCards.on('mousedown keydown', function () {
            $(this).addClass('is-pressed');
        });

        interactiveCards.on('mouseup mouseleave keyup', function () {
            $(this).removeClass('is-pressed');
        });

        const progressBars = $('[data-progress-bar]');

        if (progressBars.length) {
            $('body').addClass('has-reading-progress');

            const updateProgress = function () {
                const scrollTop = $(window).scrollTop();
                const docHeight = $(document).height() - $(window).height();
                const ratio = docHeight > 0 ? Math.min(1, Math.max(0, scrollTop / docHeight)) : 0;
                const percent = Math.round(ratio * 100);

                progressBars.each(function () {
                    this.style.setProperty('--progress-ratio', ratio);
                    $(this)
                        .attr('aria-valuenow', percent)
                        .attr('aria-valuetext', percent + '%');
                });
            };

            $(window).on('scroll.haberProgress resize.haberProgress', updateProgress);
            updateProgress();
        }

        const liveCenters = $('[data-live-center]');

        if (liveCenters.length) {
            const templateMessage = getMessage('liveUpdated', 'Canlı yayın güncellendi: %s');

            const formatMessage = function (template, value) {
                if (!template || template.indexOf('%s') === -1) {
                    return value || '';
                }

                return template.replace('%s', value || '');
            };

            liveCenters.each(function () {
                const center = $(this);
                const triggers = center.find('[data-live-trigger]');

                if (!triggers.length) {
                    return;
                }

                const setTargetText = function (target, value, hideWhenEmpty) {
                    const node = center.find('[data-live-target="' + target + '"]');

                    if (!node.length) {
                        return;
                    }

                    const output = value || '';
                    node.text(output);

                    if (hideWhenEmpty) {
                        node.toggleClass('is-hidden', output === '');
                    }
                };

                const setMetric = function (target, prefix, value) {
                    const node = center.find('[data-live-target="' + target + '"]');

                    if (!node.length) {
                        return;
                    }

                    const output = value ? prefix + value : '';
                    node.text(output);
                    node.toggleClass('is-hidden', output === '');
                };

                const setLink = function (target, url, text) {
                    const node = center.find('[data-live-target="' + target + '"]');

                    if (!node.length) {
                        return;
                    }

                    if (url && url !== '#') {
                        node.attr('href', url);
                        node.removeClass('is-hidden');
                    } else {
                        node.attr('href', '#');
                        node.addClass('is-hidden');
                    }

                    if (typeof text !== 'undefined') {
                        node.text(text || '');
                    }
                };

                const setVisual = function (url) {
                    const image = center.find('[data-live-target="visual"]');
                    const placeholder = center.find('[data-live-target="placeholder"]');

                    if (!image.length) {
                        return;
                    }

                    if (url) {
                        image.attr('src', url);
                        image.removeClass('is-hidden');
                        if (placeholder.length) {
                            placeholder.addClass('is-hidden');
                        }
                    } else {
                        image.attr('src', '');
                        image.addClass('is-hidden');
                        if (placeholder.length) {
                            placeholder.removeClass('is-hidden');
                        }
                    }
                };

                const setActiveTrigger = function (trigger) {
                    triggers.removeClass('is-active').attr('aria-pressed', 'false');
                    trigger.addClass('is-active').attr('aria-pressed', 'true');
                };

                const updateStage = function (trigger, shouldAnnounce = true) {
                    if (!trigger || !trigger.length) {
                        return;
                    }

                    const title = trigger.data('liveTitle') || '';
                    const url = trigger.data('liveUrl') || '';
                    const excerpt = trigger.data('liveExcerpt') || '';
                    const category = trigger.data('liveCategory') || '';
                    const clock = trigger.data('liveClock') || '';
                    const author = trigger.data('liveAuthor') || '';
                    const views = trigger.data('liveViews') || '';
                    const comments = trigger.data('liveComments') || '';
                    const reading = trigger.data('liveReading') || '';
                    const ctaText = trigger.data('liveCta');
                    const thumb = trigger.data('liveThumb') || '';

                    setLink('headline', url, title);
                    setLink('cta', url, (typeof ctaText === 'string' && ctaText.length) ? ctaText : undefined);
                    setTargetText('excerpt', excerpt, true);
                    setTargetText('category', category, true);
                    setTargetText('clock', clock, true);
                    setTargetText('author', author, true);
                    setMetric('views', '👁️ ', views);
                    setMetric('comments', '💬 ', comments);
                    setMetric('reading', '⏱️ ', reading);
                    setVisual(thumb);

                    setActiveTrigger(trigger);

                    if (shouldAnnounce && title) {
                        announce(formatMessage(templateMessage, title));
                    }
                };

                triggers.on('click', function (event) {
                    event.preventDefault();
                    updateStage($(this));
                });

                triggers.on('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        updateStage($(this));
                    }
                });

                const initialActive = triggers.filter('.is-active').first();
                const starter = initialActive.length ? initialActive : triggers.first();

                if (starter.length) {
                    updateStage(starter, false);
                }
            });
        }

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
