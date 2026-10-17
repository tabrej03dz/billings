@if(auth()->check() && !empty($currentUserActivityId))
    <script>
        (() => {
            /*
            |--------------------------------------------------------------------------
            | Current Activity Configuration
            |--------------------------------------------------------------------------
            */

            const activityId = @json($currentUserActivityId);

            const heartbeatUrl = @json(
                route('activity.heartbeat')
            );

            const endUrl = @json(
                route('activity.end')
            );

            const errorUrl = @json(
                route('activity.error')
            );

            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content');

            if (!activityId || !csrfToken) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Tracking Settings
            |--------------------------------------------------------------------------
            */

            const SEND_EVERY_SECONDS = 15;
            const IDLE_AFTER_MILLISECONDS = 60 * 1000;

            let unsavedActiveSeconds = 0;
            let lastInteractionAt = Date.now();
            let activityEnded = false;
            let heartbeatSending = false;

            /*
            |--------------------------------------------------------------------------
            | Error Tracking Variables
            |--------------------------------------------------------------------------
            */

            const recentErrors = new Map();

            /*
            |--------------------------------------------------------------------------
            | User Interaction Record
            |--------------------------------------------------------------------------
            */

            const markUserInteraction = () => {
                lastInteractionAt = Date.now();
            };

            const interactionEvents = [
                'mousemove',
                'mousedown',
                'keydown',
                'touchstart',
                'scroll',
                'click',
            ];

            interactionEvents.forEach((eventName) => {
                window.addEventListener(
                    eventName,
                    markUserInteraction,
                    {
                        passive: true,
                    }
                );
            });

            /*
            |--------------------------------------------------------------------------
            | Check User Is Actually Active
            |--------------------------------------------------------------------------
            */

            const userIsActive = () => {
                const pageIsVisible =
                    document.visibilityState === 'visible';

                const idleTime =
                    Date.now() - lastInteractionAt;

                const userRecentlyInteracted =
                    idleTime <= IDLE_AFTER_MILLISECONDS;

                return pageIsVisible && userRecentlyInteracted;
            };

            /*
            |--------------------------------------------------------------------------
            | Save Activity Error
            |--------------------------------------------------------------------------
            */

            const saveActivityError = async (payload) => {
                if (!payload || !payload.message) {
                    return;
                }

                const fingerprint = [
                    payload.error_type || '',
                    payload.message || '',
                    payload.source_file || '',
                    payload.source_line || '',
                    payload.http_status || '',
                    payload.request_url || '',
                ].join('|');

                const lastSentAt =
                    recentErrors.get(fingerprint);

                const now = Date.now();

                /*
                | Same error ko 30 seconds ke andar repeat save nahi karna.
                */

                if (
                    lastSentAt &&
                    now - lastSentAt < 30000
                ) {
                    return;
                }

                recentErrors.set(
                    fingerprint,
                    now
                );

                /*
                | Map bahut bada na ho.
                */

                if (recentErrors.size > 100) {
                    const firstKey =
                        recentErrors.keys().next().value;

                    recentErrors.delete(firstKey);
                }

                try {
                    await fetch(errorUrl, {
                        method: 'POST',

                        headers: {
                            'Content-Type':
                                'application/json',

                            'Accept':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken,

                            'X-Requested-With':
                                'XMLHttpRequest',
                        },

                        credentials: 'same-origin',

                        keepalive: true,

                        body: JSON.stringify({
                            activity_id:
                                activityId,

                            error_type:
                                payload.error_type
                                || 'javascript',

                            message:
                                String(payload.message)
                                    .slice(0, 5000),

                            source_file:
                                payload.source_file
                                || null,

                            source_line:
                                payload.source_line
                                || null,

                            source_column:
                                payload.source_column
                                || null,

                            stack_trace:
                                payload.stack_trace
                                    ? String(
                                        payload.stack_trace
                                    ).slice(0, 20000)
                                    : null,

                            request_url:
                                payload.request_url
                                || null,

                            request_method:
                                payload.request_method
                                || null,

                            http_status:
                                payload.http_status
                                || null,
                        }),
                    });
                } catch (error) {
                    /*
                    | Error logger khud fail ho to ignore karenge.
                    */
                }
            };

            /*
            |--------------------------------------------------------------------------
            | JavaScript Error Capture
            |--------------------------------------------------------------------------
            */

            window.addEventListener(
                'error',
                (event) => {
                    /*
                    |--------------------------------------------------------------------------
                    | Resource Load Error
                    |--------------------------------------------------------------------------
                    */

                    if (
                        event.target &&
                        event.target !== window
                    ) {
                        const element = event.target;

                        /*
                        |--------------------------------------------------------------------------
                        | Manually ignored resources
                        |--------------------------------------------------------------------------
                        */

                        if (
                            element instanceof HTMLElement &&
                            element.dataset?.ignoreResourceError === 'true'
                        ) {
                            return;
                        }

                        const resourceUrl =
                            element.currentSrc
                            || element.src
                            || element.href
                            || null;

                        /*
                        |--------------------------------------------------------------------------
                        | Loaded image ko false error na maanein
                        |--------------------------------------------------------------------------
                        */

                        if (
                            element instanceof HTMLImageElement &&
                            element.complete &&
                            element.naturalWidth > 0
                        ) {
                            return;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Empty ya invalid resource ignore karein
                        |--------------------------------------------------------------------------
                        */

                        if (!resourceUrl) {
                            return;
                        }

                        saveActivityError({
                            error_type:
                                'resource_error',

                            message:
                                'Resource failed to load: '
                                + resourceUrl,

                            source_file:
                                resourceUrl,

                            request_url:
                                window.location.href,

                            request_method:
                                'GET',
                        });

                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Normal JavaScript Error
                    |--------------------------------------------------------------------------
                    */

                    saveActivityError({
                        error_type:
                            'javascript',

                        message:
                            event.message
                            || 'Unknown JavaScript error',

                        source_file:
                            event.filename
                            || null,

                        source_line:
                            event.lineno
                            || null,

                        source_column:
                            event.colno
                            || null,

                        stack_trace:
                            event.error?.stack
                            || null,

                        request_url:
                            window.location.href,
                    });
                },
                true
            );

            /*
            |--------------------------------------------------------------------------
            | Unhandled Promise Error Capture
            |--------------------------------------------------------------------------
            */

            window.addEventListener(
                'unhandledrejection',
                (event) => {
                    const reason = event.reason;

                    saveActivityError({
                        error_type:
                            'unhandled_promise',

                        message:
                            reason?.message
                            || String(reason)
                            || 'Unhandled promise rejection',

                        stack_trace:
                            reason?.stack
                            || null,
                    });
                }
            );

            /*
            |--------------------------------------------------------------------------
            | Active Seconds Server Par Send
            |--------------------------------------------------------------------------
            */

            const sendHeartbeat = async () => {
                if (
                    unsavedActiveSeconds <= 0 ||
                    activityEnded ||
                    heartbeatSending
                ) {
                    return;
                }

                heartbeatSending = true;

                const secondsToSend =
                    Math.min(
                        unsavedActiveSeconds,
                        60
                    );

                unsavedActiveSeconds -=
                    secondsToSend;

                try {
                    const response = await fetch(
                        heartbeatUrl,
                        {
                            method: 'POST',

                            headers: {
                                'Content-Type':
                                    'application/json',

                                'Accept':
                                    'application/json',

                                'X-CSRF-TOKEN':
                                    csrfToken,

                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },

                            credentials:
                                'same-origin',

                            keepalive: true,

                            body: JSON.stringify({
                                activity_id:
                                    activityId,

                                seconds:
                                    secondsToSend,

                                page_title:
                                    document.title,
                            }),
                        }
                    );

                    if (!response.ok) {
                        unsavedActiveSeconds +=
                            secondsToSend;

                        /*
                        | Heartbeat error bhi activity mein save karo.
                        */

                        saveActivityError({
                            error_type:
                                'http_error',

                            message:
                                `Heartbeat failed with status ${response.status}`,

                            request_url:
                                heartbeatUrl,

                            request_method:
                                'POST',

                            http_status:
                                response.status,
                        });
                    }
                } catch (error) {
                    unsavedActiveSeconds +=
                        secondsToSend;

                    saveActivityError({
                        error_type:
                            'network_error',

                        message:
                            error?.message
                            || 'Heartbeat network request failed',

                        request_url:
                            heartbeatUrl,

                        request_method:
                            'POST',

                        stack_trace:
                            error?.stack
                            || null,
                    });
                } finally {
                    heartbeatSending = false;
                }
            };

            /*
            |--------------------------------------------------------------------------
            | Har Second Active Time Count
            |--------------------------------------------------------------------------
            */

            const activityTimer =
                window.setInterval(
                    () => {
                        if (userIsActive()) {
                            unsavedActiveSeconds += 1;
                        }

                        if (
                            unsavedActiveSeconds >=
                            SEND_EVERY_SECONDS
                        ) {
                            sendHeartbeat();
                        }
                    },
                    1000
                );

            /*
            |--------------------------------------------------------------------------
            | Page Close Ya Change Hone Par Time Save
            |--------------------------------------------------------------------------
            */

            const endActivity = () => {
                if (activityEnded) {
                    return;
                }

                activityEnded = true;

                window.clearInterval(
                    activityTimer
                );

                const remainingSeconds =
                    Math.min(
                        unsavedActiveSeconds,
                        60
                    );

                if (navigator.sendBeacon) {
                    const formData =
                        new FormData();

                    formData.append(
                        '_token',
                        csrfToken
                    );

                    formData.append(
                        'activity_id',
                        activityId
                    );

                    formData.append(
                        'seconds',
                        remainingSeconds
                    );

                    formData.append(
                        'page_title',
                        document.title
                    );

                    navigator.sendBeacon(
                        endUrl,
                        formData
                    );

                    return;
                }

                fetch(endUrl, {
                    method: 'POST',

                    headers: {
                        'Content-Type':
                            'application/json',

                        'Accept':
                            'application/json',

                        'X-CSRF-TOKEN':
                            csrfToken,

                        'X-Requested-With':
                            'XMLHttpRequest',
                    },

                    credentials:
                        'same-origin',

                    keepalive: true,

                    body: JSON.stringify({
                        activity_id:
                            activityId,

                        seconds:
                            remainingSeconds,

                        page_title:
                            document.title,
                    }),
                }).catch((error) => {
                    saveActivityError({
                        error_type:
                            'network_error',

                        message:
                            error?.message
                            || 'Activity end request failed',

                        request_url:
                            endUrl,

                        request_method:
                            'POST',

                        stack_trace:
                            error?.stack
                            || null,
                    });
                });
            };

            /*
            |--------------------------------------------------------------------------
            | Page Switch, Close, Reload
            |--------------------------------------------------------------------------
            */

            window.addEventListener(
                'pagehide',
                endActivity
            );
        })();
    </script>
@endif