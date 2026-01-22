<div class="border border-gray-400 rounded bg-white">
    <div class="d-flex pf-13">
        <div class="d-flex align-items-center gap-8">
            <div class="size-40 size-child">
                <img src="{{ theme_public_asset( "img/default.png" ) }}" class="align-self-center rounded-circle border cpv-avatar" alt="">
            </div>
            <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                <div class="flex-grow-1 me-2 text-truncate">
                    <a href="javascript:void(0);" class="text-gray-800 text-hover-primary fs-14 fw-bold cpv-name">{{ __("Your name") }}</a>
                    <span class="text-muted fw-semibold d-block fs-12 cpv-username">@username</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-0">
        <div class="cpv-media">
            <div class="cpv-img w-100 cpv-tiktok-img d-none"></div>
            <div class="cpv-tiktok-img-view w-100 bg-gray-900 text-white d-flex align-items-center justify-content-center" style="height: 520px;">
                <i class="fa-solid fa-play fs-50"></i>
            </div>
        </div>

        <div class="cpv-text fs-14 px-3 py-3 text-truncate-5"></div>
    </div>

    <div class="px-3 pb-3 d-flex justify-content-between text-gray-800 align-items-center">
        <div class="d-flex gap-16 fs-20">
            <i class="fa-regular fa-heart"></i>
            <i class="fa-regular fa-comment"></i>
            <i class="fa-regular fa-share-from-square"></i>
        </div>
    </div>
</div>

<script>
(function () {
    var container = document.querySelector('.cpv-tiktok-img');
    var view = document.querySelector('.cpv-tiktok-img-view');
    if (!view) {
        return;
    }
    var defaultHtml = view.innerHTML;

    function tiktok_renderFirstMedia(elements) {
        if (elements.length === 0) {
            return '';
        }
        var first = elements[0];
        return `<div class="img-wrap">${first.outerHTML}</div>`;
    }

    function tiktok_onMediaItemsChange() {
        var elements = document.querySelectorAll('.cpv-tiktok-img > img, .cpv-tiktok-img > div');
        if (elements.length > 0) {
            var rendered = tiktok_renderFirstMedia(Array.from(elements));
            view.innerHTML = rendered;
            view.classList.remove('bg-gray-900', 'text-white', 'align-items-center', 'justify-content-center');
            view.style.height = '';
        } else {
            view.innerHTML = defaultHtml;
            view.classList.add('bg-gray-900', 'text-white', 'align-items-center', 'justify-content-center');
            view.style.height = '520px';
        }
    }

    if (container) {
        var observer = new MutationObserver(tiktok_onMediaItemsChange);
        observer.observe(container, {
            childList: true,
            subtree: false,
            attributes: true,
            attributeFilter: ['src']
        });
    }

    tiktok_onMediaItemsChange();
})();
</script>
