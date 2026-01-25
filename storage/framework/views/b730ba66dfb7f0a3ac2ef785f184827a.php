<?php $__env->startSection('sub_header'); ?>
    <?php if (isset($component)) { $__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $attributes; } ?>
<?php $component = App\View\Components\SubHeader::resolve(['title' => ''.e(__('YouTube API')).'','description' => ''.e(__('Easy Configuration Steps for YouTube API')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sub-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\SubHeader::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a)): ?>
<?php $attributes = $__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a; ?>
<?php unset($__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a)): ?>
<?php $component = $__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a; ?>
<?php unset($__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container max-w-800 pb-5">
    <form class="actionForm" action="<?php echo e(url_admin("settings/save")); ?>">
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label"><?php echo e(__('Status')); ?></label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="youtube_status" value="1" id="youtube_status_1" <?php echo e(get_option("youtube_status", 0)==1?"checked":""); ?>>
                                    <label class="form-check-label mt-1" for="youtube_status_1">
                                        <?php echo e(__('Enable')); ?>

                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="youtube_status" value="0" id="youtube_status_0"<?php echo e(get_option("youtube_status", 0)==0?"checked":""); ?>>
                                    <label class="form-check-label mt-1" for="youtube_status_0">
                                        <?php echo e(__('Disable')); ?>

                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="youtube_client_id" class="form-label"><?php echo e(__('Client ID')); ?></label>
                            <input class="form-control" name="youtube_client_id" id="youtube_client_id" type="text" value="<?php echo e(get_option("youtube_client_id", "")); ?>">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="youtube_client_secret" class="form-label"><?php echo e(__('Client Secret')); ?></label>
                            <input class="form-control" name="youtube_client_secret" id="youtube_client_secret" type="text" value="<?php echo e(get_option("youtube_client_secret", "")); ?>">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="youtube_scopes" class="form-label"><?php echo e(__('Scopes')); ?></label>
                            <input class="form-control" name="youtube_scopes" id="youtube_scopes" type="text" value="<?php echo e(get_option("youtube_scopes", "https://www.googleapis.com/auth/youtube.readonly,https://www.googleapis.com/auth/youtube.upload")); ?>">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="youtube_privacy" class="form-label"><?php echo e(__('Default Privacy')); ?></label>
                            <select class="form-select" name="youtube_privacy" id="youtube_privacy">
                                <?php $privacy = get_option('youtube_privacy', 'unlisted'); ?>
                                <option value="public" <?php echo e($privacy === 'public' ? 'selected' : ''); ?>><?php echo e(__('Public')); ?></option>
                                <option value="unlisted" <?php echo e($privacy === 'unlisted' ? 'selected' : ''); ?>><?php echo e(__('Unlisted')); ?></option>
                                <option value="private" <?php echo e($privacy === 'private' ? 'selected' : ''); ?>><?php echo e(__('Private')); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="youtube_category_id" class="form-label"><?php echo e(__('Default Category ID')); ?></label>
                            <input class="form-control" name="youtube_category_id" id="youtube_category_id" type="text" value="<?php echo e(get_option("youtube_category_id", "22")); ?>">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="alert alert-primary fs-14">
                            <?php echo e(__("Callback URL: ")); ?> 
                            <a href="<?php echo e(url_app("youtube/channel")); ?>" target="_blank"><?php echo e(url_app("youtube/channel")); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-dark b-r-10 w-100">
                <?php echo e(__('Save changes')); ?>

            </button>
        </div>

    </form>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/social.kokonuts.my/modules/AppChannelYoutubeProfiles/resources/views/settings.blade.php ENDPATH**/ ?>