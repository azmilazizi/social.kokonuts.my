<div class="mb-3">
    <div class="card shadow-none b-r-6">
        <div class="card-header px-3">
            <div class="fs-12 fw-6 text-gray-700">
                {{ __("YouTube") }}
            </div>
        </div>
        <div class="card-body px-3">
            @php
                $ytDisclaimerOption = DB::table('options')
                    ->select(['name', 'value'])
                    ->where('name', 'yt_disclaimer')
                    ->first();
                $ytDisclaimerTemplate = $ytDisclaimerOption->value ?? '';
                $hasTemplate = $ytDisclaimerTemplate !== null && trim($ytDisclaimerTemplate) !== '';
                $ytDisclaimer = isset($postData) && isset($postData->options)
                    ? ($postData->options->yt_disclaimer ?? '')
                    : $ytDisclaimerTemplate;
            @endphp
            <div class="mb-3">
                <div class="d-flex align-items-center justify-content-between mb-1" data-yt-disclaimer-template>
                    <label class="form-label mb-0">{{ __('Disclaimer') }}</label>
                    <div class="d-flex align-items-center gap-8">
                        @if ($hasTemplate)
                            <button class="btn btn-outline-primary btn-sm" type="button" data-yt-disclaimer-edit>
                                <i class="fa-light fa-pen-to-square me-1"></i>{{ __('Edit') }}
                            </button>
                        @endif
                        <button class="btn btn-primary btn-sm d-none" type="button" data-yt-disclaimer-save data-save-url="{{ route('app.publishing.youtube_disclaimer_template') }}">
                            <i class="fa-light fa-save me-1"></i>{{ __('Save') }}
                        </button>
                    </div>
                </div>
                <textarea class="form-control input-emoji bbr-r-6 bbl-r-6" name="options[yt_disclaimer]" data-yt-disclaimer-input data-template-value="{{ e($ytDisclaimerTemplate) }}" data-has-template="{{ $hasTemplate ? '1' : '0' }}">{{ $ytDisclaimer }}</textarea>
            </div>
            <div class="mb-0">
                <label class="form-label">{{ __('Post To') }}</label>
                <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                    <div class="form-check me-3">
                        <input class="form-check-input" type="radio" name="options[yt_type]" value="video" id="youtube_type_video" checked>
                        <label class="form-check-label mt-1" for="youtube_type_video">
                            {{ __('YouTube Video') }}
                        </label>
                    </div>
                    <div class="form-check me-3">
                        <input class="form-check-input" type="radio" name="options[yt_type]" value="shorts" id="youtube_type_shorts">
                        <label class="form-check-label mt-1" for="youtube_type_shorts">
                            {{ __('YouTube Shorts') }}
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var wrapper = document.querySelector('[data-yt-disclaimer-template]');
        if (!wrapper) {
            return;
        }

        var textarea = wrapper.closest('.mb-3').querySelector('[data-yt-disclaimer-input]');
        var editButton = wrapper.querySelector('[data-yt-disclaimer-edit]');
        var saveButton = wrapper.querySelector('[data-yt-disclaimer-save]');

        if (!textarea || !saveButton) {
            return;
        }

        var templateValue = textarea.dataset.templateValue || '';
        var hasTemplate = textarea.dataset.hasTemplate === '1';
        var isDirty = false;

        function setReadOnly(state) {
            textarea.readOnly = state;
        }

        function updateSaveButton() {
            var currentValue = textarea.value;
            var shouldShow = isDirty && currentValue !== templateValue;
            saveButton.classList.toggle('d-none', !shouldShow);
            saveButton.disabled = currentValue.trim() === templateValue.trim();
        }

        function handleSaveSuccess(value) {
            templateValue = value;
            isDirty = false;
            updateSaveButton();

            if (hasTemplate) {
                setReadOnly(true);
                if (editButton) {
                    editButton.classList.remove('d-none');
                }
            } else {
                hasTemplate = true;
            }
        }

        if (hasTemplate) {
            setReadOnly(true);
        }

        if (editButton) {
            editButton.addEventListener('click', function () {
                setReadOnly(false);
                editButton.classList.add('d-none');
                textarea.focus();
            });
        }

        textarea.addEventListener('input', function () {
            isDirty = true;
            updateSaveButton();
        });

        saveButton.addEventListener('click', function () {
            var saveUrl = saveButton.dataset.saveUrl;
            if (!saveUrl) {
                return;
            }
            var formData = new FormData();
            formData.append('value', textarea.value);

            if (window.Main && window.Main.ajaxPost && window.jQuery) {
                window.Main.ajaxPost(window.jQuery(saveButton), saveUrl, formData, function (result) {
                    if (result && result.status === 1) {
                        handleSaveSuccess(textarea.value);
                    }
                });
                return;
            }

            var csrfToken = '';
            var tokenNode = document.querySelector('meta[name="csrf-token"]');
            if (tokenNode) {
                csrfToken = tokenNode.getAttribute('content') || '';
            }

            fetch(saveUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            }).then(function (response) {
                return response.json();
            }).then(function (result) {
                if (result && result.status === 1) {
                    handleSaveSuccess(textarea.value);
                }
            });
        });
    })();
</script>
