<?php $__env->startSection('sub_header'); ?>
    <?php if (isset($component)) { $__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $attributes; } ?>
<?php $component = App\View\Components\SubHeader::resolve(['title' => ''.e(__('Search Media Online')).'','description' => ''.e(__('Search and download high-quality images and videos online.')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
    <div class="container py-4">
        <form class="searchMediaForm actionForm" action="<?php echo e(route("app.search_media.search")); ?>" data-content="search-media-result">
            <div class="d-flex border b-r-20 p-4 gap-12">
                <div class="form-control form-control-lg pe-0">
                    <span class="btn btn-icon">
                        <i class="fa-light fa-magnifying-glass"></i>
                    </span>
                    <input name="keyword" placeholder="<?php echo e(__('Enter keyword')); ?>" type="text">
                    <select class="max-w-120 border-start ps-3" name="source">
                        <?php $__currentLoopData = SearchMedia::services(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($key); ?>"><?php echo e($value); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <button class="btn btn-dark btn-lg btl-r-0 bbl-r-0"><i class="fa-light fa-magnifying-glass"></i> <?php echo e(__('Search')); ?></button>

                </div>
                <a href="<?php echo e(route('app.files.save_files')); ?>" class="btn btn-primary btn-lg actionMultiItem ms-auto text-nowrap" data-form=".searchMediaForm" data-redirect=""><?php echo e(__('Save To Files')); ?></a>
            </div>
            <div class="modal-body p-0">
                <div class="py-4 search-media-result">

                    <div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
                        <span class="fs-70 mb-3 text-primary">
                            <i class="fa-light fa-image-polaroid"></i>
                        </span>
                        <div class="fw-semibold fs-5 mb-2 text-gray-800">
                            <?php echo e(__('No media found')); ?>

                        </div>
                        <div class="text-body-secondary mb-4">
                            <?php echo e(__('Start by searching for high-quality images or videos from popular online sources.')); ?>

                        </div>
                    </div>

                </div>

            </div>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/social.kokonuts.my/modules/AppMediaSearch/resources/views/index.blade.php ENDPATH**/ ?>