(function () {
    const root = document.body;

    if (!root) {
        return;
    }

    const logoutUrl = root.dataset.logoutUrl || '/logout';
    const signinUrl = root.dataset.signinUrl || '/signin';
    const isProtectedRoute = root.dataset.authProtected === '1';
    const storageKey = 'listing-site:auth-event';
    const channelName = 'listing-site-auth';
    const handledEventIds = new Set();
    const logoutPath = new URL(logoutUrl, window.location.origin).pathname;
    const nativeFetch = typeof window.fetch === 'function' ? window.fetch.bind(window) : null;
    let broadcastChannel = null;
    let redirecting = false;
    let logoutRequest = null;

    const redirectAfterLogout = () => {
        if (redirecting) {
            return;
        }

        redirecting = true;

        if (isProtectedRoute) {
            window.location.assign(signinUrl);

            return;
        }

        window.location.reload();
    };

    const handleAuthFailure = (status) => {
        if (![401, 419].includes(Number(status))) {
            return;
        }

        const payload = createPayload('session-expired');
        broadcast(payload);
        handleIncomingEvent(payload);
    };

    const createPayload = (type) => ({
        id: `${Date.now()}-${Math.random().toString(36).slice(2)}`,
        type,
        timestamp: Date.now(),
    });

    const handleIncomingEvent = (payload) => {
        if (!payload || typeof payload !== 'object' || !payload.id || handledEventIds.has(payload.id)) {
            return;
        }

        if (!['logout', 'session-expired'].includes(payload.type)) {
            return;
        }

        handledEventIds.add(payload.id);
        redirectAfterLogout();
    };

    const broadcast = (payload) => {
        try {
            window.localStorage.setItem(storageKey, JSON.stringify(payload));
        } catch (error) {}

        if (!broadcastChannel) {
            return;
        }

        try {
            broadcastChannel.postMessage(payload);
        } catch (error) {}
    };

    const isLogoutForm = (form) => {
        if (!(form instanceof HTMLFormElement)) {
            return false;
        }

        try {
            return new URL(form.action, window.location.origin).pathname === logoutPath;
        } catch (error) {
            return false;
        }
    };

    const submitLogout = async (form) => {
        if (!isLogoutForm(form)) {
            if (form) {
                form.submit();
            }

            return;
        }

        if (logoutRequest) {
            return logoutRequest;
        }

        if (!nativeFetch) {
            form.submit();

            return;
        }

        const formData = new FormData(form);

        logoutRequest = nativeFetch(form.action, {
            method: form.method || 'POST',
            body: formData,
            headers: {
                'Accept': 'text/html,application/xhtml+xml',
                'X-Requested-With': 'XMLHttpRequest',
            },
        }).then((response) => {
            if (!response.ok) {
                handleAuthFailure(response.status);

                return response;
            }

            broadcast(createPayload('logout'));
            window.location.assign(response.redirected && response.url ? response.url : signinUrl);

            return response;
        }).catch(() => {
            form.submit();
        }).finally(() => {
            logoutRequest = null;
        });

        return logoutRequest;
    };

    if ('BroadcastChannel' in window) {
        broadcastChannel = new BroadcastChannel(channelName);
        broadcastChannel.addEventListener('message', (event) => {
            handleIncomingEvent(event.data);
        });
    }

    window.addEventListener('storage', (event) => {
        if (event.key !== storageKey || !event.newValue) {
            return;
        }

        try {
            handleIncomingEvent(JSON.parse(event.newValue));
        } catch (error) {}
    });

    document.addEventListener('submit', (event) => {
        if (isLogoutForm(event.target)) {
            event.preventDefault();
            submitLogout(event.target);
        }
    }, true);

    if (nativeFetch) {
        window.fetch = async (...args) => {
            const response = await nativeFetch(...args);

            handleAuthFailure(response?.status);

            return response;
        };
    }

    if (window.axios?.interceptors?.response) {
        window.axios.interceptors.response.use(
            (response) => {
                handleAuthFailure(response?.status);

                return response;
            },
            (error) => {
                handleAuthFailure(error?.response?.status);

                return Promise.reject(error);
            }
        );
    }

    window.authSessionSync = Object.freeze({
        notifySessionExpired() {
            handleAuthFailure(401);
        },
        submitLogout,
    });
})();
