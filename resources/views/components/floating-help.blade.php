@php
    $supportPhone = '919151989905';
    $supportDisplayPhone = '+91 9151989905';

    $whatsappMessage = rawurlencode(
        'Hello, mujhe billing software me help chahiye.'
    );

    $whatsappUrl = 'https://wa.me/'
        . $supportPhone
        . '?text='
        . $whatsappMessage;

    $phoneUrl = 'tel:+' . $supportPhone;

    /* Apna poora Google Meet URL yahan daalein. */
    $videoCallUrl = 'https://meet.google.com/';
@endphp

<div
    id="billing-support-widget"
    class="billing-support-widget"
    aria-label="Billing support"
>
    <div
        id="billing-support-options"
        class="billing-support-options"
        aria-hidden="true"
    >
        <a
            href="{{ $phoneUrl }}"
            class="billing-support-item"
            aria-label="Call support on {{ $supportDisplayPhone }}"
        >
            <span class="billing-support-item-icon billing-support-phone-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M3 5.25A2.25 2.25 0 015.25 3h2.086c.51 0 .955.343 1.086.836l.938 3.518a1.125 1.125 0 01-.417 1.173l-1.293.97a11.04 11.04 0 005.353 5.353l.97-1.293a1.125 1.125 0 011.173-.417l3.518.938c.493.131.836.576.836 1.086v2.086A2.25 2.25 0 0117.25 19.5H16.5C9.044 19.5 3 13.456 3 6v-.75z"
                    />
                </svg>
            </span>

            <span class="billing-support-text">
                <strong>Talk to our Experts</strong>
                <small>{{ $supportDisplayPhone }}</small>
            </span>
        </a>

        <a
            href="{{ $videoCallUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            class="billing-support-item"
            aria-label="Open live video support"
        >
            <span class="billing-support-item-icon billing-support-video-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15.75 10.5l4.72-2.36a.75.75 0 011.08.67v6.38a.75.75 0 01-1.08.67l-4.72-2.36m-9 3.75h6.75A2.25 2.25 0 0015.75 15V9a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 9v6a2.25 2.25 0 002.25 2.25z"
                    />
                </svg>
            </span>

            <span class="billing-support-text">
                <strong>Live Video Call</strong>
                <small>Connect with support</small>
            </span>
        </a>

        <a
            href="{{ $whatsappUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            class="billing-support-item"
            aria-label="Open WhatsApp support"
        >
            <span class="billing-support-item-icon billing-support-whatsapp-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M12.04 2a9.84 9.84 0 00-8.42 14.91L2 22l5.22-1.58A9.96 9.96 0 0012.04 22C17.53 22 22 17.52 22 12S17.53 2 12.04 2zm0 18.32a8.2 8.2 0 01-4.19-1.15l-.3-.18-3.1.94.95-3.02-.2-.31a8.14 8.14 0 01-1.26-4.38 8.1 8.1 0 018.1-8.12 8.12 8.12 0 010 16.22zm4.45-6.08c-.24-.12-1.44-.71-1.66-.79-.23-.08-.39-.12-.56.12-.16.25-.64.79-.78.95-.15.16-.29.18-.54.06-.24-.12-1.03-.38-1.96-1.21-.72-.64-1.21-1.44-1.35-1.68-.14-.25-.01-.38.11-.5.11-.11.24-.29.36-.43.12-.14.16-.25.24-.41.08-.16.04-.3-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.43h-.48c-.16 0-.43.06-.66.31-.22.25-.86.84-.86 2.05s.88 2.38 1 2.54c.12.16 1.73 2.65 4.2 3.71.59.25 1.05.41 1.41.52.59.19 1.13.16 1.55.1.47-.07 1.44-.59 1.65-1.16.2-.57.2-1.06.14-1.16-.06-.1-.22-.16-.46-.28z"
                    />
                </svg>
            </span>

            <span class="billing-support-text">
                <strong>Chat on WhatsApp</strong>
                <small>Quick billing support</small>
            </span>
        </a>
    </div>

    <div class="billing-support-button-row">
        <span id="billing-support-label" aria-hidden="true">
            Need Help?
        </span>

        <button
            type="button"
            id="billing-support-toggle"
            aria-label="Open help menu"
            aria-expanded="false"
            aria-controls="billing-support-options"
        >
            <span class="billing-support-pulse" aria-hidden="true"></span>

            <svg
                id="billing-support-phone"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.2"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M3 5.25A2.25 2.25 0 015.25 3h2.086c.51 0 .955.343 1.086.836l.938 3.518a1.125 1.125 0 01-.417 1.173l-1.293.97a11.04 11.04 0 005.353 5.353l.97-1.293a1.125 1.125 0 011.173-.417l3.518.938c.493.131.836.576.836 1.086v2.086A2.25 2.25 0 0117.25 19.5H16.5C9.044 19.5 3 13.456 3 6v-.75z"
                />
            </svg>

            <svg
                id="billing-support-close"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.2"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6 18L18 6M6 6l12 12"
                />
            </svg>
        </button>
    </div>
</div>

<style>
    #billing-support-widget {
        position: fixed !important;
        right: 24px !important;
        bottom: 24px !important;
        z-index: 10000 !important;

        width: 58px !important;
        height: 58px !important;
        margin: 0 !important;
        padding: 0 !important;

        font-family: Arial, Helvetica, sans-serif !important;
        pointer-events: none !important;
        isolation: isolate !important;
    }

    #billing-support-options {
        position: absolute !important;
        right: 0 !important;
        bottom: 72px !important;

        display: flex !important;
        flex-direction: column !important;
        align-items: flex-end !important;
        gap: 10px !important;

        width: max-content !important;
        max-width: calc(100vw - 32px) !important;
        margin: 0 !important;
        padding: 0 !important;

        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
        transform: translateY(14px) scale(0.97) !important;
        transform-origin: bottom right !important;

        transition:
            opacity 180ms ease,
            transform 180ms ease,
            visibility 180ms ease !important;
    }

    #billing-support-widget.support-open #billing-support-options {
        opacity: 1 !important;
        visibility: visible !important;
        pointer-events: auto !important;
        transform: translateY(0) scale(1) !important;
    }

    .billing-support-item {
        display: flex !important;
        align-items: center !important;
        gap: 11px !important;

        width: 225px !important;
        min-height: 58px !important;
        padding: 9px 13px !important;
        margin: 0 !important;

        color: #374151 !important;
        background: #ffffff !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 13px !important;

        text-decoration: none !important;
        box-sizing: border-box !important;
        cursor: pointer !important;
        pointer-events: auto !important;
        touch-action: manipulation !important;
        -webkit-tap-highlight-color: transparent !important;

        box-shadow:
            0 12px 28px rgba(15, 23, 42, 0.15),
            0 3px 8px rgba(15, 23, 42, 0.08) !important;

        transition:
            transform 160ms ease,
            border-color 160ms ease,
            box-shadow 160ms ease !important;
    }

    .billing-support-item:hover {
        transform: translateY(-2px) !important;
        border-color: #d4a900 !important;
        box-shadow:
            0 16px 35px rgba(15, 23, 42, 0.20),
            0 4px 10px rgba(15, 23, 42, 0.10) !important;
    }

    .billing-support-item:focus-visible,
    #billing-support-toggle:focus-visible {
        outline: 3px solid rgba(37, 99, 235, 0.35) !important;
        outline-offset: 3px !important;
    }

    .billing-support-item-icon {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;

        width: 38px !important;
        height: 38px !important;
        min-width: 38px !important;
        border-radius: 10px !important;
        pointer-events: none !important;
    }

    .billing-support-item-icon svg {
        width: 21px !important;
        height: 21px !important;
        pointer-events: none !important;
    }

    .billing-support-phone-icon {
        color: #b88700 !important;
        background: #fff8d8 !important;
    }

    .billing-support-video-icon {
        color: #2563eb !important;
        background: #eff6ff !important;
    }

    .billing-support-whatsapp-icon {
        color: #16a34a !important;
        background: #ecfdf5 !important;
    }

    .billing-support-text {
        display: block !important;
        min-width: 0 !important;
        line-height: 1.2 !important;
        pointer-events: none !important;
    }

    .billing-support-text strong {
        display: block !important;
        color: #374151 !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        white-space: nowrap !important;
    }

    .billing-support-text small {
        display: block !important;
        margin-top: 4px !important;
        color: #6b7280 !important;
        font-size: 11px !important;
        white-space: nowrap !important;
    }

    .billing-support-button-row {
        position: absolute !important;
        right: 0 !important;
        bottom: 0 !important;

        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 10px !important;

        width: max-content !important;
        height: 58px !important;
        margin: 0 !important;
        padding: 0 !important;
        pointer-events: none !important;
    }

    #billing-support-label {
        display: block !important;
        padding: 8px 11px !important;
        margin: 0 !important;

        color: #ffffff !important;
        background: #111827 !important;
        border-radius: 8px !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        white-space: nowrap !important;

        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
        transform: translateX(8px) !important;

        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.20) !important;
        transition:
            opacity 160ms ease,
            transform 160ms ease,
            visibility 160ms ease !important;
    }

    #billing-support-widget.support-open #billing-support-label {
        opacity: 1 !important;
        visibility: visible !important;
        transform: translateX(0) !important;
    }

    #billing-support-toggle {
        position: relative !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;

        width: 58px !important;
        height: 58px !important;
        min-width: 58px !important;
        min-height: 58px !important;
        padding: 0 !important;
        margin: 0 !important;

        color: #ffffff !important;
        background: linear-gradient(135deg, #e4b900 0%, #b88700 100%) !important;
        border: 4px solid rgba(255, 255, 255, 0.90) !important;
        border-radius: 9999px !important;

        box-sizing: border-box !important;
        cursor: pointer !important;
        pointer-events: auto !important;
        touch-action: manipulation !important;
        -webkit-tap-highlight-color: transparent !important;

        box-shadow:
            0 15px 35px rgba(184, 135, 0, 0.45),
            0 5px 15px rgba(15, 23, 42, 0.16) !important;

        transition:
            transform 160ms ease,
            box-shadow 160ms ease !important;
    }

    #billing-support-toggle:hover {
        transform: scale(1.05) !important;
    }

    #billing-support-toggle:active {
        transform: scale(0.96) !important;
    }

    #billing-support-toggle svg {
        position: absolute !important;
        width: 25px !important;
        height: 25px !important;
        pointer-events: none !important;
    }

    #billing-support-phone {
        opacity: 1 !important;
        transform: scale(1) rotate(0deg) !important;
        transition: opacity 160ms ease, transform 160ms ease !important;
    }

    #billing-support-close {
        opacity: 0 !important;
        transform: scale(0.65) rotate(-90deg) !important;
        transition: opacity 160ms ease, transform 160ms ease !important;
    }

    #billing-support-widget.support-open #billing-support-phone {
        opacity: 0 !important;
        transform: scale(0.5) rotate(90deg) !important;
    }

    #billing-support-widget.support-open #billing-support-close {
        opacity: 1 !important;
        transform: scale(1) rotate(0deg) !important;
    }

    .billing-support-pulse {
        position: absolute !important;
        inset: -2px !important;
        display: block !important;
        background: rgba(228, 185, 0, 0.35) !important;
        border-radius: 9999px !important;
        pointer-events: none !important;
        animation: billingSupportPulse 1.8s infinite !important;
    }

    #billing-support-widget.support-open .billing-support-pulse {
        display: none !important;
    }

    @keyframes billingSupportPulse {
        0% {
            transform: scale(0.9);
            opacity: 0.8;
        }

        70%,
        100% {
            transform: scale(1.35);
            opacity: 0;
        }
    }

    html.dark .billing-support-item {
        color: #f4f4f5 !important;
        background: #18181b !important;
        border-color: #3f3f46 !important;
    }

    html.dark .billing-support-text strong {
        color: #f4f4f5 !important;
    }

    html.dark .billing-support-text small {
        color: #a1a1aa !important;
    }

    html.dark #billing-support-toggle {
        border-color: #18181b !important;
    }

    @media screen and (max-width: 640px) {
        #billing-support-widget {
            right: 14px !important;
            bottom: 14px !important;
            width: 54px !important;
            height: 54px !important;
        }

        #billing-support-options {
            bottom: 66px !important;
            max-width: calc(100vw - 28px) !important;
        }

        .billing-support-button-row {
            height: 54px !important;
        }

        .billing-support-item {
            width: 205px !important;
            max-width: calc(100vw - 28px) !important;
            min-height: 54px !important;
        }

        #billing-support-toggle {
            width: 54px !important;
            height: 54px !important;
            min-width: 54px !important;
            min-height: 54px !important;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #billing-support-options,
        #billing-support-label,
        #billing-support-toggle,
        #billing-support-phone,
        #billing-support-close,
        .billing-support-item {
            transition: none !important;
        }

        .billing-support-pulse {
            animation: none !important;
        }
    }
</style>

<script>
    (() => {
        const widgetSelector = '#billing-support-widget';
        const toggleSelector = '#billing-support-toggle';
        const optionsSelector = '#billing-support-options';
        const labelSelector = '#billing-support-label';
        const openClass = 'support-open';

        const getWidget = () => document.querySelector(widgetSelector);
        const getToggle = () => document.querySelector(toggleSelector);

        function setSupportState(open) {
            const widget = getWidget();
            const toggle = getToggle();
            const options = document.querySelector(optionsSelector);
            const label = document.querySelector(labelSelector);

            if (!widget || !toggle) {
                return;
            }

            widget.classList.toggle(openClass, open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute(
                'aria-label',
                open ? 'Close help menu' : 'Open help menu'
            );

            options?.setAttribute('aria-hidden', open ? 'false' : 'true');
            label?.setAttribute('aria-hidden', open ? 'false' : 'true');
        }

        function closeSupport() {
            setSupportState(false);
        }

        function toggleSupport() {
            const widget = getWidget();

            if (!widget) {
                return;
            }

            setSupportState(!widget.classList.contains(openClass));
        }

        /*
         * Global listeners ek hi baar bind honge.
         * Sirf help toggle par preventDefault chalega.
         * Page ke kisi aur button par stopPropagation/preventDefault nahi chalega.
         */
        if (!window.__billingSupportEventsBound) {
            window.__billingSupportEventsBound = true;

            document.addEventListener('click', (event) => {
                const target = event.target;

                if (!(target instanceof Element)) {
                    return;
                }

                if (target.closest(toggleSelector)) {
                    event.preventDefault();
                    toggleSupport();
                    return;
                }

                if (target.closest('.billing-support-item')) {
                    closeSupport();
                    return;
                }

                const widget = getWidget();

                if (
                    widget
                    && widget.classList.contains(openClass)
                    && !widget.contains(target)
                ) {
                    closeSupport();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeSupport();
                }
            });

            document.addEventListener('livewire:navigating', closeSupport);
            document.addEventListener('livewire:navigated', closeSupport);
        }

        closeSupport();
    })();
</script>