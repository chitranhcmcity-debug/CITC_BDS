(function () {
    'use strict';
    const config = window.AnalyticsEventConfig;
    if (!config) return;
    document.addEventListener('click', function (event) {
        const target = event.target.closest('[data-track-post][data-track-type]');
        if (!target) return;
        const postId = parseInt(target.dataset.trackPost || '0', 10);
        const type = target.dataset.trackType || '';
        if (!postId || !['call','chat','save','share','phone','zalo'].includes(type)) return;
        const body = new FormData();
        body.append('post_id', String(postId));
        body.append('_csrf_token', config.csrf);
        if (target.dataset.trackPlatform) body.append('platform', target.dataset.trackPlatform);
        fetch(config.endpoint + '/' + type, {method:'POST', body, credentials:'same-origin', keepalive:true}).catch(function () {});
    });
})();
