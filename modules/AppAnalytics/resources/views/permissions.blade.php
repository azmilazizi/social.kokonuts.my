<div class="card b-r-6 border-gray-300 mb-3">
    <div class="card-header">
        <div class="form-check">
            <input class="form-check-input prevent-toggle" type="checkbox" value="1" id="appanalytics" name="permissions[appanalytics]" @checked(array_key_exists("appanalytics", $permissions))>
            <label class="fw-6 fs-14 text-gray-700 ms-2" for="appanalytics">
                {{ __("Analytics") }}
            </label>
        </div>
        <input class="form-control d-none" name="labels[appanalytics]" type="text" value="Analytics">
    </div>
</div>
