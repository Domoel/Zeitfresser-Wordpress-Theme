document.addEventListener('DOMContentLoaded', function () {
    var toc = document.getElementById('zeitfresser-floating-toc');
    var title = document.querySelector('.zeitfresser-article-heading .page-title, .zeitfresser-article-heading .entry-title, .entry-header .entry-title');
    var progressBar = document.getElementById('zeitfresser-floating-toc-progress');
    var nav = toc ? toc.querySelector('.zeitfresser-floating-toc__nav') : null;

    if (!toc || !title) {
        return;
    }

    var links = Array.prototype.slice.call(toc.querySelectorAll('a[data-target]'));
    var desktopQuery = window.matchMedia('(min-width: 1500px)');
    var stickyTop = 100;
    var headingOffset = 88;
    var ticking = false;
    var cachedSidebar = null;
    var tocBottomOffset = null;

    function isDesktop() {
        return desktopQuery.matches;
    }

    function getTarget(link) {
        var id = link.getAttribute('data-target');
        return id ? document.getElementById(id) : null;
    }

    function getHeadings() {
        return links.map(function (link) {
            return {
                link: link,
                target: getTarget(link)
            };
        }).filter(function (item) {
            return !!item.target;
        });
    }

    function getArticleElement() {
        return document.querySelector(
            '.single-post .post-content article, ' +
            '.single-post .post-content, ' +
            'article.post, article, ' +
            '.entry-content'
        );
    }

    function getTocBottomOffset() {
        if (tocBottomOffset !== null) return tocBottomOffset;

        var value = getComputedStyle(document.documentElement)
            .getPropertyValue('--toc-bottom-offset')
            .trim();

        tocBottomOffset = parseInt(value, 10) || 12;
        return tocBottomOffset;
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
                return b.getBoundingClientRect().left - a.getBoundingClientRect().left;
            })[0] || null;

        return cachedSidebar;
    }

    function syncPosition() {
        if (!isDesktop()) {
            document.documentElement.style.setProperty('--zeitfresser-toc-top', stickyTop + 'px');
            document.documentElement.style.setProperty('--zeitfresser-toc-left', '24px');
            document.documentElement.style.setProperty('--zeitfresser-toc-width', '220px');
            return;
        }

        var scrollTop = window.scrollY || window.pageYOffset || 0;
        var titleRect = title.getBoundingClientRect();

        // 🔥 bessere Content-Erkennung
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

        // Toc Content Breite
        var maxWidth = Math.max(Math.round(contentRect.left - gap - 24), 180);
        var tocWidth = Math.max(220, Math.min(260, maxWidth));

        var tocLeft = Math.max(
            24,
            Math.round(contentRect.left - gap - tocWidth)
        );

        var tocTop = Math.max(
            stickyTop,
            Math.round(titleRect.top + scrollTop + 14)
        );

        document.documentElement.style.setProperty('--zeitfresser-toc-top', tocTop + 'px');
        document.documentElement.style.setProperty('--zeitfresser-toc-left', tocLeft + 'px');
        document.documentElement.style.setProperty('--zeitfresser-toc-width', tocWidth + 'px');
    }

    function handleFooterCollision() {
        if (!isDesktop()) {
            toc.style.transform = '';
            return;
        }

        var article = getArticleElement();

        if (!article) {
            toc.style.transform = '';
            return;
        }

        toc.style.transform = '';

        var scrollTop = window.scrollY || window.pageYOffset;

        var articleRect = article.getBoundingClientRect();
        var articleBottom = articleRect.top + scrollTop + articleRect.height;

        var tocRect = toc.getBoundingClientRect();
        var tocTop = tocRect.top + scrollTop;
        var tocHeight = tocRect.height;
        var tocBottom = tocTop + tocHeight;

        var offset = getTocBottomOffset();

        var maxBottom = articleBottom - offset;
        var overflow = Math.ceil(tocBottom - maxBottom);

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
            } else {
                link.removeAttribute('aria-current');
            }
        });
    }

    function updateProgress() {
        if (!progressBar) return;

        var article = getArticleElement();

        if (!article) {
            progressBar.style.width = '0%';
            return;
        }

        var rect = article.getBoundingClientRect();
        var total = Math.max(article.offsetHeight - window.innerHeight, 1);
        var progress = Math.min(Math.max((-rect.top / total) * 100, 0), 100);

        progressBar.style.width = progress + '%';
    }

    function updateActiveHeading() {
        var headings = getHeadings();
        if (!headings.length) return;

        var currentId = headings[0].target.id;
        var triggerY = headingOffset + 24;

        headings.forEach(function (item) {
            if (item.target.getBoundingClientRect().top <= triggerY) {
                currentId = item.target.id;
            }
        });

        setActiveLink(currentId);
    }

    function onViewportChange() {
        if (ticking) return;

        ticking = true;

        window.requestAnimationFrame(function () {
            syncPosition();
            handleFooterCollision();
            updateProgress();
            updateActiveHeading();
            ticking = false;
        });
    }

    links.forEach(function (link) {
        link.addEventListener('click', function (event) {
            var target = getTarget(link);
            if (!target) return;

            event.preventDefault();

            var top = target.getBoundingClientRect().top + window.scrollY - headingOffset;

            window.scrollTo({
                top: top,
                behavior: 'smooth'
            });

            setActiveLink(target.id);
        });
    });

    if (nav) {
        nav.addEventListener('wheel', function (event) {
            var canScroll = nav.scrollHeight > nav.clientHeight;
            if (!canScroll) return;

            var atTop = nav.scrollTop <= 0;
            var atBottom = Math.ceil(nav.scrollTop + nav.clientHeight) >= nav.scrollHeight;

            if ((event.deltaY < 0 && !atTop) || (event.deltaY > 0 && !atBottom)) {
                event.preventDefault();
                nav.scrollTop += event.deltaY;
            }
        }, { passive: false });
    }

    // Initial run
    syncPosition();
    handleFooterCollision();
    updateProgress();
    updateActiveHeading();

    requestAnimationFrame(function () {
        toc.classList.add('is-visible');
    });

    window.addEventListener('scroll', onViewportChange, { passive: true });
    window.addEventListener('resize', onViewportChange, { passive: true });
});
