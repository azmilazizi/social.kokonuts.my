<?php
    use Carbon\Carbon;
    use Modules\AppChannels\Models\Accounts;
    use Modules\AppPublishing\Models\Posts;

    $teamId = request()->team_id;
    $startDate = Carbon::now()->subDays(30);
    $endDate = Carbon::now();
    $networks = [
        [
            'key' => 'facebook',
            'label' => __('Facebook'),
            'icon' => 'fa-brands fa-facebook-f',
            'color' => '#1877f2',
        ],
        [
            'key' => 'instagram',
            'label' => __('Instagram'),
            'icon' => 'fa-brands fa-instagram',
            'color' => '#e1306c',
        ],
    ];

    $accountsByNetwork = Accounts::query()
        ->where('team_id', $teamId)
        ->where('status', '!=', 0)
        ->whereIn('social_network', collect($networks)->pluck('key'))
        ->get()
        ->groupBy('social_network');
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header">
        <div class="fw-6"><?php echo e(__('Facebook & Instagram Analytics')); ?></div>
        <div class="text-gray-500 fs-12"><?php echo e(__('Last 30 days')); ?></div>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <?php $__currentLoopData = $networks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $network): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $accountCount = ($accountsByNetwork[$network['key']] ?? collect())->count();
                    $totalPosts = Posts::query()
                        ->whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
                        ->when($teamId, fn($q) => $q->where('team_id', $teamId))
                        ->where('social_network', $network['key'])
                        ->count();
                    $successPosts = Posts::query()
                        ->whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
                        ->when($teamId, fn($q) => $q->where('team_id', $teamId))
                        ->where('social_network', $network['key'])
                        ->where('status', 5)
                        ->count();
                    $successRate = $totalPosts > 0 ? round($successPosts * 100 / $totalPosts, 1) : 0;
                ?>

                <div class="col-md-6">
                    <div class="card border-gray-300 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-12 mb-3">
                                <div class="d-flex align-items-center justify-content-center size-44 b-r-12 text-white" style="background-color: <?php echo e($network['color']); ?>;">
                                    <i class="<?php echo e($network['icon']); ?>"></i>
                                </div>
                                <div>
                                    <div class="fw-6"><?php echo e(__(':network Analytics', ['network' => $network['label']])); ?></div>
                                    <div class="text-gray-500 fs-12"><?php echo e(__('Last 30 days')); ?></div>
                                </div>
                            </div>

                            <?php if($accountCount === 0): ?>
                                <div class="text-gray-600 fs-13">
                                    <?php echo e(__('Connect your social accounts to start tracking analytics and gain insights into your performance.')); ?>

                                </div>
                            <?php else: ?>
                                <div class="row g-3">
                                    <div class="col-4">
                                        <div class="text-gray-500 fs-12"><?php echo e(__('Connected Accounts')); ?></div>
                                        <div class="fw-6 fs-18"><?php echo e(number_format($accountCount)); ?></div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-gray-500 fs-12"><?php echo e(__('Posts')); ?></div>
                                        <div class="fw-6 fs-18"><?php echo e(number_format($totalPosts)); ?></div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-gray-500 fs-12"><?php echo e(__('Success Rate')); ?></div>
                                        <div class="fw-6 fs-18"><?php echo e($successRate); ?>%</div>
                                    </div>
                                </div>

                                <?php if($totalPosts === 0): ?>
                                    <div class="text-gray-500 fs-13 mt-3">
                                        <?php echo e(__('No analytics data available.')); ?>

                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>
<?php /**PATH /var/www/social.kokonuts.my/modules/AppDashboard/resources/views/partials/social-analytics.blade.php ENDPATH**/ ?>