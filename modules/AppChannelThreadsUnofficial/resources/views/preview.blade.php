<div class="threads-preview border border-gray-400 rounded bg-white">
    <div class="d-flex justify-content-between align-items-center pf-13">
        <div class="d-flex align-items-center gap-8">
            <div class="size-40 size-child">
                <img src="{{ theme_public_asset( " img/default.png" ) }}"
                    class="align-self-center rounded-circle border cpv-avatar" alt="">
            </div>
            <div class="d-flex flex-column">
                <div class="d-flex align-items-center gap-6">
                    <span class="text-gray-900 fs-14 fw-bold cpv-name">{{ __("Your name") }}</span>
                    <span class="text-gray-500 fs-12">@<span class="cpv-username">{{ __("username") }}</span></span>
                </div>
                <span class="text-gray-500 fs-12">1h</span>
            </div>
        </div>
        <div class="text-gray-500 fs-18">
            <i class="fa-light fa-ellipsis"></i>
        </div>
    </div>

    <div class="px-3 pb-3">
        <div class="cpv-text fs-14 mb-3 text-truncate-5"></div>

        <div class="cpv-media">
            <div class="cpv-img w-100 cpv-threads-img d-none"></div>
            <div class="cpv-threads-img-view w-100">
                <div class="img-wrap b-r-10" style="aspect-ratio: 1 / 1;">
                    <img src="{{ theme_public_asset( " img/default.png" ) }}" class="w-100">
                </div>
            </div>
        </div>

        <div class="cpv-link d-flex justify-content-start w-100 d-none border b-r-10">
            <div
                class="cpv-link-img img-wrap w-100 size-120 size-child border-end btl-r-10 bbl-r-10 border-start border-top border-bottom">
                <img src="{{ theme_public_asset( " img/default.png" ) }}" class="w-100">
            </div>
            <div
                class="d-flex flex-column justify-content-center w-100 bg-gray-100 fs-12 pf-13 btr-r-10 bbr-r-10 border-end border-top border-bottom">
                <div class="cpv-default">
                    <div class="h-12 bg-gray-300 mb-2"></div>
                    <div class="h-12 bg-gray-300 mb-2"></div>
                    <div class="h-12 bg-gray-300 mb-1"></div>
                    <div class="h-12 bg-gray-300 mb-1 wp-50"></div>
                </div>
                <div class="cpv-link-web fs-10 fw-3 text-truncate-1"></div>
                <div class="cpv-link-title fw-6 text-truncate-1"></div>
                <div class="cpv-link-desc text-gray-700 text-truncate-2"></div>
            </div>
        </div>
    </div>

    <div class="px-3 pb-3 d-flex justify-content-between text-gray-600 fs-18">
        <div class="d-flex flex-stack">
            <div class="symbol symbol-45px me-2">
                <i class="fa-regular fa-heart"></i>
            </div>
        </div>
        <div class="d-flex flex-stack">
            <div class="symbol symbol-45px me-2">
                <i class="fa-regular fa-comment"></i>
            </div>
        </div>
        <div class="d-flex flex-stack">
            <div class="symbol symbol-45px me-2">
                <i class="fa-regular fa-retweet"></i>
            </div>
        </div>
        <div class="d-flex flex-stack">
            <div class="symbol symbol-45px me-2">
                <i class="fa-regular fa-paper-plane"></i>
            </div>
        </div>
    </div>
</div>

<script>
    function renderThreadsMedia(elements) {
    if (elements.length === 0) {
        return '';
    }

    var first = elements[0];
    var tagName = first.tagName.toLowerCase();
    var isVideo = tagName === 'video' || (tagName === 'div' && first.dataset.mediaType === 'video');
    var wrapperClass = isVideo ? 'cpv-video-cell' : 'img-wrap';
    return `<div class="${wrapperClass} b-r-10" style="aspect-ratio: 1 / 1;">${first.outerHTML}</div>`;
}

function onThreadsMediaItemsChange() {
    var threadsElements = document.querySelectorAll('.cpv-threads-img > img, .cpv-threads-img > div, .cpv-threads-img > video');
    if (threadsElements.length > 0) {
        var threadsMediaList = Array.from(threadsElements).filter(el =>
            ['img', 'div', 'video'].includes(el.tagName.toLowerCase())
        );

        var rendered = renderThreadsMedia(threadsMediaList);
        if (rendered) {
            document.querySelector('.cpv-threads-img-view').innerHTML = rendered;
        }
    }
}

var threadsContainer = document.querySelector('.cpv-threads-img');
if (threadsContainer) {
    var threadsObserver = new MutationObserver(onThreadsMediaItemsChange);
    threadsObserver.observe(threadsContainer, {
        childList: true,
        subtree: false,
        attributes: true,
        attributeFilter: ['src'],
    });

    onThreadsMediaItemsChange();
}
</script>