(function () {
    const storageKey = 'listing-site:profile-online-event';
    const channelName = 'listing-site-profile-online';
    const handledIds = new Set();
    const listeners = new Set();
    const broadcastChannel = 'BroadcastChannel' in window ? new BroadcastChannel(channelName) : null;

    const isValidPayload = (payload) => {
        return payload
            && typeof payload === 'object'
            && payload.id
            && Number(payload.profileId) > 0
            && ['online', 'offline'].includes(payload.status);
    };

    const handle = (payload) => {
        if (!isValidPayload(payload) || handledIds.has(payload.id)) {
            return;
        }

        handledIds.add(payload.id);

        listeners.forEach((listener) => {
            try {
                listener(payload);
            } catch (error) {}
        });
    };

    if (broadcastChannel) {
        broadcastChannel.addEventListener('message', (event) => {
            handle(event.data);
        });
    }

    window.addEventListener('storage', (event) => {
        if (event.key !== storageKey || !event.newValue) {
            return;
        }

        try {
            handle(JSON.parse(event.newValue));
        } catch (error) {}
    });

    window.profileOnlineSync = Object.freeze({
        notify(payload = {}) {
            const eventPayload = {
                id: payload.id || `${Date.now()}-${Math.random().toString(36).slice(2)}`,
                profileId: Number(payload.profileId),
                status: payload.status,
                timestamp: payload.timestamp || Date.now(),
            };

            if (!isValidPayload(eventPayload)) {
                return;
            }

            try {
                window.localStorage.setItem(storageKey, JSON.stringify(eventPayload));
            } catch (error) {}

            if (broadcastChannel) {
                try {
                    broadcastChannel.postMessage(eventPayload);
                } catch (error) {}
            }

            handle(eventPayload);
        },

        subscribe(listener) {
            if (typeof listener !== 'function') {
                return () => {};
            }

            listeners.add(listener);

            return () => {
                listeners.delete(listener);
            };
        },
    });
})();
