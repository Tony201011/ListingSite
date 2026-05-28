(function () {
    'use strict';

    var POLL_INTERVAL_MS = 60000;
    var LISTINGS_ID = 'listings-content';
    var COUNT_ENDPOINT = '/api/listings/online-count';

    var lastKnownCount = null;
    var pollTimer = null;

    function getListingsContainer() {
        return document.getElementById(LISTINGS_ID);
    }

    function refreshListings() {
        var container = getListingsContainer();
        if (!container) {
            return;
        }

        fetch(window.location.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (res) {
                return res.text();
            })
            .then(function (html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var fresh = doc.getElementById(LISTINGS_ID);
                if (!fresh) {
                    return;
                }
                container.innerHTML = fresh.innerHTML;
                if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                    window.Alpine.initTree(container);
                }
            })
            .catch(function () {
                /* silently ignore network errors */
            });
    }

    function fetchOnlineCount() {
        fetch(COUNT_ENDPOINT)
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                var count = data.online_count;
                if (lastKnownCount !== null && count !== lastKnownCount) {
                    refreshListings();
                }
                lastKnownCount = count;
            })
            .catch(function () {
                /* silently ignore network errors */
            });
    }

    function startPolling() {
        fetchOnlineCount();
        pollTimer = setInterval(fetchOnlineCount, POLL_INTERVAL_MS);
    }

    function stopPolling() {
        if (pollTimer !== null) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    // Subscribe to same-browser cross-tab sync events.
    if (window.profileOnlineSync && typeof window.profileOnlineSync.subscribe === 'function') {
        window.profileOnlineSync.subscribe(function () {
            refreshListings();
            // Re-sync our count baseline after the refresh.
            fetchOnlineCount();
        });
    }

    // Start polling once the DOM is ready.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startPolling);
    } else {
        startPolling();
    }

    // Pause polling when the tab is hidden, resume when visible.
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            stopPolling();
        } else {
            startPolling();
        }
    });
})();
