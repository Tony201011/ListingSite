@if (request()->routeIs('filament.admin.*'))
    <script>
        (() => {
            const ROOT_ID = 'admin-top-subnav';

            const normalizePath = (urlValue) => {
                try {
                    const url = new URL(urlValue, window.location.origin);

                    return `${url.pathname}${url.search}`;
                } catch (error) {
                    return urlValue;
                }
            };

            const getSidebarSubmenuLinks = () => {
                const sidebar = document.querySelector('.fi-sidebar');

                if (! sidebar) {
                    return [];
                }

                const activeParent = sidebar.querySelector('.fi-sidebar-item.fi-sidebar-item-has-active-child-items');

                if (! activeParent) {
                    return [];
                }

                const candidates = Array.from(activeParent.querySelectorAll(
                    '.fi-sidebar-sub-group-items a[href], [class*="sub-group"] a[href], [class*="child"] a[href]',
                ));

                const seen = new Set();

                return candidates.filter((link) => {
                    const href = link.getAttribute('href');
                    const label = link.textContent?.trim();

                    if (! href || ! label) {
                        return false;
                    }

                    const key = `${label}::${href}`;

                    if (seen.has(key)) {
                        return false;
                    }

                    seen.add(key);

                    return true;
                });
            };

            const ensureRoot = () => {
                const topbar = document.querySelector('.fi-topbar');

                if (! topbar) {
                    return null;
                }

                let root = document.getElementById(ROOT_ID);

                if (! root) {
                    root = document.createElement('nav');
                    root.id = ROOT_ID;
                    root.className = 'admin-top-subnav';
                    root.setAttribute('aria-label', 'Section navigation');
                    topbar.insertAdjacentElement('afterend', root);
                }

                return root;
            };

            const renderTabs = () => {
                const root = ensureRoot();

                if (! root) {
                    return;
                }

                const links = getSidebarSubmenuLinks();

                if (! links.length) {
                    root.innerHTML = '';
                    root.classList.remove('is-visible');
                    document.body.classList.remove('admin-top-subnav-enabled');

                    return;
                }

                const currentPath = normalizePath(window.location.href);
                const tabsMarkup = links.map((link) => {
                    const label = link.textContent?.trim() ?? '';
                    const href = link.getAttribute('href') ?? '#';
                    const isActive = link.closest('.fi-active')
                        || normalizePath(href) === currentPath;

                    return `<a href="${href}" class="admin-top-subnav__tab${isActive ? ' is-active' : ''}">${label}</a>`;
                }).join('');

                root.innerHTML = `<div class="admin-top-subnav__tabs" role="tablist">${tabsMarkup}</div>`;
                root.classList.add('is-visible');
                document.body.classList.add('admin-top-subnav-enabled');
            };

            const scheduleRender = () => window.requestAnimationFrame(renderTabs);

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', scheduleRender, { once: true });
            } else {
                scheduleRender();
            }

            document.addEventListener('livewire:navigated', scheduleRender);
            document.addEventListener('filament:navigated', scheduleRender);
        })();
    </script>
@endif
