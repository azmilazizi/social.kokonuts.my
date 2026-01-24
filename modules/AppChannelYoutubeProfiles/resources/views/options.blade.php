<div class="mb-3">
    <div class="card shadow-none b-r-6">
        <div class="card-header px-3">
            <div class="fs-12 fw-6 text-gray-700">
                {{ __("YouTube") }}
            </div>
        </div>
        <div class="card-body px-3">
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
            @php
                $disclaimerValue = $options['yt_disclaimer'] ?? data_get($postData, 'caption', '');
            @endphp
            <div class="mt-3">
                <label class="form-label fw-5" for="youtube-disclaimer">{{ __("Disclaimer") }}</label>
                <textarea id="youtube-disclaimer" class="form-control input-emoji post-youtube-disclaimer fw-4 border" name="options[yt_disclaimer]" placeholder="{{ __("Enter disclaimer") }}">{{ $disclaimerValue }}</textarea>
            </div>
        </div>
    </div>
</div>
