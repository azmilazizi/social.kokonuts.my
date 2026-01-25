<div class="threads-preview border border-gray-400 rounded bg-white">
    <div class="d-flex pf-13 align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-8">
            <div class="size-40 size-child">
                <img src="{{ theme_public_asset( "img/default.png" ) }}"
                    class="align-self-center rounded-circle border cpv-avatar" alt="">
            </div>
            <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                <div class="flex-grow-1 me-2 text-truncate">
                    <a href="javascript:void(0);" class="text-gray-800 text-hover-primary fs-14 fw-bold cpv-name">{{
                        __("Your name") }}</a>
                    <span class="text-muted fw-semibold d-block fs-12">{{ __("Threads") }}</span>
                </div>
            </div>
        </div>
        <div class="text-gray-500 fs-18">
            <i class="fa-light fa-ellipsis"></i>
        </div>
    </div>

    <div class="mb-0">
        <div class="cpv-media">
            <div class="cpv-img w-100 cpv-threads-img d-none"></div>
            <div class="cpv-threads-img-view w-100">
                <img src="{{ theme_public_asset( "img/default.png" ) }}" class="w-100">
            </div>
        </div>

        <div class="cpv-text fs-14 px-3 py-3 text-truncate-5"></div>
    </div>

    <div class="px-3 pb-3 d-flex justify-content-between text-gray-800 align-items-center">
        <div class="d-flex gap-16 fs-20">
            <i class="fa-regular fa-heart"></i>
            <i class="fa-regular fa-comment"></i>
            <i class="fa-regular fa-retweet"></i>
        </div>
        <div class="d-flex align-items-center gap-6">
            <i class="fa-regular fa-paper-plane"></i>
            <span class="fs-12 text-gray-600">{{ __("Share") }}</span>
        </div>
    </div>
</div>

<script>
    (function () {
    var container = document.querySelector('.cpv-threads-img');
    var view = document.querySelector('.cpv-threads-img-view');
    if (!view) {
        return;
    }
    var defaultHtml = view.innerHTML;

    function threads_renderMediaItem(element) {
        var tagName = element.tagName.toLowerCase();
        var isVideo = tagName === 'video' || (tagName === 'div' && element.dataset.mediaType === 'video');
        var wrapperClass = isVideo ? 'cpv-video-cell' : 'img-wrap';
        return `<div class="${wrapperClass}">${element.outerHTML}</div>`;
    }

    function threads_renderFirstMedia(elements) {
        if (elements.length === 0) {
            return '';
        }
        var first = elements[0];
        return threads_renderMediaItem(first);
    }

    function threads_onMediaItemsChange() {
        var elements = document.querySelectorAll('.cpv-threads-img > img, .cpv-threads-img > div, .cpv-threads-img > video');
        if (elements.length > 0) {
            var rendered = threads_renderFirstMedia(Array.from(elements));
            view.innerHTML = rendered;
        } else {
            view.innerHTML = defaultHtml;
        }
    }

    if (container) {
        var observer = new MutationObserver(threads_onMediaItemsChange);
        observer.observe(container, {
            childList: true,
            subtree: false,
            attributes: true,
            attributeFilter: ['src']
        });
    }

    threads_onMediaItemsChange();
})();
</script>
