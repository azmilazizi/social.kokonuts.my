<div class="youtube-preview border border-gray-400 rounded bg-white">
    <div class="d-flex pf-13 align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-8">
            <div class="size-40 size-child">
                <img src="{{ theme_public_asset( "img/default.png" ) }}" class="align-self-center rounded-circle border cpv-avatar" alt="">
            </div>
            <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                <div class="flex-grow-1 me-2 text-truncate">
                    <a href="javascript:void(0);" class="text-gray-800 text-hover-primary fs-14 fw-bold cpv-name">{{ __("Your name") }}</a>
                    <span class="text-muted fw-semibold d-block fs-12">{{ __("YouTube") }}</span>
                </div>
            </div>
        </div>
        <span class="badge bg-dark text-white cpv-youtube-badge">{{ __("Shorts") }}</span>
    </div>

    <div class="mb-0">
        <div class="cpv-media">
            <div class="cpv-img w-100 cpv-youtube-img d-none"></div>
            <div class="cpv-youtube-img-view w-100">
                <div class="cpv-youtube-placeholder">
                    <div class="cpv-youtube-placeholder-text">{{ __("Video not available") }}</div>
                </div>
            </div>
        </div>

        <div class="cpv-text fs-14 px-3 py-3 text-truncate-5"></div>
    </div>

    <div class="px-3 pb-3 d-flex justify-content-between text-gray-800 align-items-center">
        <div class="d-flex gap-16 fs-20">
            <i class="fa-regular fa-thumbs-up"></i>
            <i class="fa-regular fa-thumbs-down"></i>
            <i class="fa-regular fa-share-from-square"></i>
        </div>
        <div class="d-flex align-items-center gap-6">
            <i class="fa-solid fa-bars-staggered"></i>
            <span class="fs-12 text-gray-600">{{ __("More") }}</span>
        </div>
    </div>
</div>

<script>
(function () {
    var container = document.querySelector('.cpv-youtube-img');
    var view = document.querySelector('.cpv-youtube-img-view');
    var badge = document.querySelector('.cpv-youtube-badge');
    if (!view) {
        return;
    }
    var defaultHtml = view.innerHTML;

    function youtube_renderMediaItem(element) {
        var tagName = element.tagName.toLowerCase();
        var isVideo = tagName === 'video' || (tagName === 'div' && element.dataset.mediaType === 'video');
        var wrapperClass = isVideo ? 'cpv-video-cell' : 'img-wrap';
        return `<div class="${wrapperClass}">${element.outerHTML}</div>`;
    }

    function youtube_renderFirstMedia(elements) {
        if (elements.length === 0) {
            return '';
        }
        var first = elements[0];
        return youtube_renderMediaItem(first);
    }

    function youtube_onMediaItemsChange() {
        var elements = document.querySelectorAll('.cpv-youtube-img > img, .cpv-youtube-img > div, .cpv-youtube-img > video');
        if (elements.length > 0) {
            var rendered = youtube_renderFirstMedia(Array.from(elements));
            view.innerHTML = rendered;
        } else {
            view.innerHTML = defaultHtml;
        }
    }

    function youtube_applyType() {
        var typeInput = document.querySelector('input[name="options[yt_type]"]:checked');
        var isShorts = typeInput && typeInput.value === 'shorts';
        var preview = document.querySelector('.youtube-preview');
        if (preview) {
            preview.classList.toggle('is-shorts', isShorts);
        }
        if (badge) {
            badge.classList.toggle('d-none', !isShorts);
        }
    }

    if (container) {
        var observer = new MutationObserver(youtube_onMediaItemsChange);
        observer.observe(container, {
            childList: true,
            subtree: false,
            attributes: true,
            attributeFilter: ['src']
        });
    }

    document.addEventListener('change', function (event) {
        if (event.target && event.target.matches('input[name="options[yt_type]"]')) {
            youtube_applyType();
        }
    });

    youtube_onMediaItemsChange();
    youtube_applyType();
})();
</script>
