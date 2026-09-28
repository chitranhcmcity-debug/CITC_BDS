(function () {
    'use strict';
    const config = window.PostAnalyticsConfig;
    if (!config) return;

    function track(type, platform) {
        const body = new FormData();
        body.append('post_id', String(config.postId));
        body.append('_csrf_token', config.csrf);
        if (platform) body.append('platform', platform);
        return fetch(config.endpoint + '/' + type, {method:'POST', body, credentials:'same-origin', keepalive:true})
            .catch(function () { return null; });
    }

    const reveal = document.querySelector('[data-phone-reveal]');
    const call = document.querySelector('[data-call-action]');
    if (reveal && call) {
        reveal.addEventListener('click', function () {
            track('phone');
            reveal.classList.add('d-none');
            call.classList.remove('d-none');
        });
        call.addEventListener('click', function () { track('call'); });
    }

    document.querySelectorAll('[data-analytics-event="zalo"]').forEach(function (button) {
        button.addEventListener('click', function () {
            track('zalo');
            window.open(button.dataset.url, '_blank', 'noopener');
        });
    });

    document.querySelectorAll('[data-chat-seller]').forEach(function (button) {
        button.addEventListener('click', function () {
            track('chat');
            const toggle = document.querySelector('[data-chat-toggle]');
            if (toggle) toggle.click();
        });
    });

    document.querySelectorAll('[data-share-platform]').forEach(function (button) {
        button.addEventListener('click', function () {
            const platform = button.dataset.sharePlatform;
            track('share', platform);
            if (platform === 'copy') {
                navigator.clipboard.writeText(config.shareUrl).then(function () {
                    const old = button.innerHTML;
                    button.innerHTML = '<i class="fa-solid fa-check"></i>';
                    setTimeout(function () { button.innerHTML = old; }, 1500);
                });
                return;
            }
            window.open(button.dataset.shareUrl, '_blank', 'noopener,noreferrer,width=720,height=520');
        });
    });
})();
