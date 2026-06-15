/**
 * Floating TOC (Optimierte, Scroll-Driven Implementation)
 */
 
document.addEventListener('DOMContentLoaded', function () {
    var toc = document.getElementById('zeitfresser-floating-toc');
    var title = document.querySelector(
        '.zeitfresser-article-heading .page-title, ' +
        '.zeitfresser-article-heading .entry-title, ' +
        '.entry-header .entry-title'
    );
    var progressBar = document.getElementById('zeitfresser-floating-toc-progress');
    var nav = toc ? toc.querySelector('.zeitfresser-floating-toc__nav') : null;

    if (!toc || !title) {
        return;
    }

    var article = document.querySelector(
        '.single-post .post-content article, ' +
        '.single-post .post-content, ' +
        'article.post, article, ' +
        '.entry-content'
    );
    
    var links = Array.prototype.slice.call(toc.querySelectorAll('a[data-target]'));
    var desktopQuery = window.matchMedia('(min-width: 1500px)');
    var stickyTop = 100;
    var ticking = false;
    var cachedSidebar = null;
    var headings = getHeadings();
    var calculatedTocTop = stickyTop;
    var lastActiveId = null;

    function isDesktop() {
        return desktopQuery.matches;
    }

    function getTarget(link) {
        var id = link.getAttribute('data-target');
        return id ? document.getElementById(id) : null;
    }

    function getHeadings() {
        return links
            .map(function (link) {
                return { link: link, target: getTarget(link) };
            })
            .filter(function (item) {
                return !!item.target;
            });
    }

    function getTocBottomOffset() {
        return 12;
    }

    function getRealSidebar() {
        if (cachedSidebar) return cachedSidebar;
        var candidates = Array.prototype.slice.call(
            document.querySelectorAll('aside, .sidebar, #secondary')
        );
        cachedSidebar = candidates
            .filter(function (el) {
                var rect = el.getBoundingClientRect();
                return rect.width > 200 && rect.height > 200;
            })
            .sort(function (a, b) {
                var rectA = a.getBoundingClientRect();
                var rectB = b.getBoundingClientRect();
                return rectB.left - rectA.left;
            })[0] || null;
        return cachedSidebar;
    }

    function syncPosition() {
        if (!isDesktop()) {
            calculatedTocTop = stickyTop;
            document.documentElement.style.setProperty('--zeitfresser-toc-top', stickyTop + 'px');
            document.documentElement.style.setProperty('--zeitfresser-toc-left', '24px');
            document.documentElement.style.setProperty('--zeitfresser-toc-width', '220px');
            toc.classList.add('is-colliding'); 
            return;
        }

        var scrollTop = window.scrollY || window.pageYOffset || 0;
        var titleRect = title.getBoundingClientRect();

        var contentColumn =
            document.querySelector('.inside-page .main-wrapper > section') ||
            document.querySelector('#primary') ||
            document.querySelector('.content-area') ||
            title;

        if (!contentColumn) return;

        var sidebar = getRealSidebar();
        var contentRect = contentColumn.getBoundingClientRect();
        var sidebarRect = sidebar ? sidebar.getBoundingClientRect() : null;
        var gap = 48;

        if (sidebarRect) {
            gap = Math.abs(sidebarRect.left - contentRect.right);
            gap = Math.max(32, Math.min(gap, 120));
        }

        var minTocWidth = 220; // Deine minimale TOC-Breite
        var minLeftMargin = 24; // Dein minimaler Abstand zum linken Rand
        var requiredSpace = minTocWidth + 10 + minLeftMargin; // Default: var requiredSpace = minTocWidth + gap + minLeftMargin;

        if (contentRect.left < requiredSpace) {
            toc.classList.add('is-colliding');
        } else {
            toc.classList.remove('is-colliding');
        }

        var maxWidth = Math.max(Math.round(contentRect.left - gap - 24), 180);
        var tocWidth = Math.max(220, Math.min(260, maxWidth));
        var tocLeft = Math.max(24, Math.round(contentRect.left - gap - tocWidth));

        calculatedTocTop = Math.max(
            stickyTop,
            Math.round(titleRect.top + scrollTop + 14)
        );

        document.documentElement.style.setProperty('--zeitfresser-toc-top', calculatedTocTop + 'px');
        document.documentElement.style.setProperty('--zeitfresser-toc-left', tocLeft + 'px');
        document.documentElement.style.setProperty('--zeitfresser-toc-width', tocWidth + 'px');
    }

    function handleFooterCollision() {
        if (!isDesktop() || !article) {
            toc.style.transform = '';
            return;
        }

        toc.style.transform = '';

        var scrollTop = window.scrollY || window.pageYOffset;
        var articleRect = article.getBoundingClientRect();
        var articleBottom = articleRect.top + scrollTop + articleRect.height;
        
        var tocRect = toc.getBoundingClientRect();
        var tocTop = tocRect.top + scrollTop;
        var maxBottom = articleBottom - getTocBottomOffset();
        
        var overflow = Math.ceil((tocTop + tocRect.height) - maxBottom);

        if (overflow > 0) {
            toc.style.transform = 'translateY(-' + overflow + 'px)';
        }
    }

    function setActiveLink(id) {
        links.forEach(function (link) {
            var active = link.getAttribute('data-target') === id;
            link.classList.toggle('is-active', active);
            if (active) {
                link.setAttribute('aria-current', 'true');
                link.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                link.removeAttribute('aria-current');
            }
        });
    }

    function updateProgress() {
        if (!progressBar || !article) return;

        var rect = article.getBoundingClientRect();
        var total = Math.max(
            Math.max(article.scrollHeight, article.offsetHeight) - window.innerHeight,
            1
        );
        var progress = Math.min(Math.max((-rect.top / total) * 100, 0), 100);
        progressBar.style.width = progress + '%';
    }

    function updateActiveHeading() {
        if (!headings.length) return;
        
        var triggerY = calculatedTocTop + 40; 
        
        var documentHeight = Math.max(
            document.body.scrollHeight, 
            document.documentElement.scrollHeight,
            document.body.offsetHeight, 
            document.documentElement.offsetHeight
        );
        var scrollPosition = window.innerHeight + window.scrollY;
        var isMaxScrolled = (documentHeight - scrollPosition) <= 50;

        var currentId = headings[0].target.id;
        
        for (var i = 0; i < headings.length; i++) {
            if (headings[i].target.getBoundingClientRect().top <= triggerY) {
                currentId = headings[i].target.id;
            } else { 
                break; 
            }
        }

        if (isMaxScrolled && headings.length > 0) {
            currentId = headings[headings.length - 1].target.id;
        }

        if (currentId !== lastActiveId) {
            lastActiveId = currentId;
            setActiveLink(currentId);
        }
    }

    // syncPosition() is scroll-invariant (its inputs depend only on layout), so
    // it only runs on resize/load. Scroll frames do the scroll-dependent work.
    function update(includeLayout) {
        if (ticking) return;
        ticking = true;

        window.requestAnimationFrame(function () {
            if (includeLayout) {
                syncPosition();
            }
            handleFooterCollision();
            updateProgress();
            updateActiveHeading();
            ticking = false;
        });
    }

    function onScroll() {
        update(false);
    }

    function onResize() {
        update(true);
    }

    links.forEach(function (link) {
        link.addEventListener('click', function (event) {
            var target = getTarget(link);
            if (!target) return;
            event.preventDefault();

            var headerOffset = calculatedTocTop;
            var elementPosition = target.getBoundingClientRect().top;
            var offsetPosition = elementPosition + window.scrollY - headerOffset;

            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });

            if (history.pushState) {
                history.pushState(null, null, '#' + target.id);
            }
        });
    });

    syncPosition();
    handleFooterCollision();
    updateProgress();
    updateActiveHeading();

    requestAnimationFrame(function () {
        toc.classList.add('is-visible');
    });

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onResize, { passive: true });
    window.addEventListener('load', onResize);
});
