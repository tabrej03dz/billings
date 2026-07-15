@if(auth()->check() && !empty($currentUserActivityId))
    <script>
        (() => {
            /*
            |--------------------------------------------------------------------------
            | Current activity configuration
            |--------------------------------------------------------------------------
            */

            const activityId = @json($currentUserActivityId);

            const heartbeatUrl = @json(
                route('activity.heartbeat')
            );

            const endUrl = @json(
                route('activity.end')
            );

            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content');

            if (!activityId || !csrfToken) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Tracking settings
            |--------------------------------------------------------------------------
            |
            | 15 seconds ka active usage hone ke baad server par save hoga.
            | User 60 seconds tak kuchh nahi karega to idle maana jayega.
            |
            */

            const SEND_EVERY_SECONDS = 15;
            const IDLE_AFTER_MILLISECONDS = 60 * 1000;

            let unsavedActiveSeconds = 0;
            let lastInteractionAt = Date.now();
            let activityEnded = false;
            let heartbeatSending = false;

            /*
            |--------------------------------------------------------------------------
            | User interaction record karna
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
            | Check user actual page use kar raha hai ya nahi
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
            | Active seconds server par send karna
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
                    Math.min(unsavedActiveSeconds, 60);

                unsavedActiveSeconds -= secondsToSend;

                try {
                    const response = await fetch(
                        heartbeatUrl,
                        {
                            method: 'POST',

                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },

                            credentials: 'same-origin',

                            keepalive: true,

                            body: JSON.stringify({
                                activity_id: activityId,
                                seconds: secondsToSend,
                                page_title: document.title,
                            }),
                        }
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Request fail ho to seconds wapas pending mein add
                    |--------------------------------------------------------------------------
                    */

                    if (!response.ok) {
                        unsavedActiveSeconds += secondsToSend;
                    }
                } catch (error) {
                    unsavedActiveSeconds += secondsToSend;
                } finally {
                    heartbeatSending = false;
                }
            };

            /*
            |--------------------------------------------------------------------------
            | Har second check karna
            |--------------------------------------------------------------------------
            */

            const activityTimer = window.setInterval(
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
            | Page hide hone par pending time save
            |--------------------------------------------------------------------------
            */

            const endActivity = () => {
                if (activityEnded) {
                    return;
                }

                activityEnded = true;

                window.clearInterval(activityTimer);

                const remainingSeconds =
                    Math.min(unsavedActiveSeconds, 60);

                /*
                |--------------------------------------------------------------------------
                | sendBeacon page close ke time reliable hota hai
                |--------------------------------------------------------------------------
                */

                if (navigator.sendBeacon) {
                    const formData = new FormData();

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

                /*
                |--------------------------------------------------------------------------
                | Browser sendBeacon support na kare to fetch fallback
                |--------------------------------------------------------------------------
                */

                fetch(endUrl, {
                    method: 'POST',

                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },

                    credentials: 'same-origin',

                    keepalive: true,

                    body: JSON.stringify({
                        activity_id: activityId,
                        seconds: remainingSeconds,
                        page_title: document.title,
                    }),
                }).catch(() => {
                    // Page closing ke waqt error ignore karenge.
                });
            };

            /*
            |--------------------------------------------------------------------------
            | Page switch, close, reload
            |--------------------------------------------------------------------------
            */

            window.addEventListener(
                'pagehide',
                endActivity
            );
        })();
    </script>
@endif