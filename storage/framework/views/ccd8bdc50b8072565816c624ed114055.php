<?php $__env->startSection('content'); ?>
    <div class="container px-4 max-w-700">

        <div class="mt-4 mb-5">
            <div class="d-flex flex-column flex-lg-row flex-md-column align-items-md-start align-items-lg-center justify-content-between">
                <div class="my-3 d-flex flex-column gap-8">
                    <h1 class="fs-20 font-medium lh-1 text-gray-900">
                        <?php echo e(__("Instagram Unofficial Login")); ?>

                    </h1>
                    <div class="d-flex align-items-center gap-20 fw-5 fs-14">
                        <div class="d-flex gap-8">
                            <span class="text-gray-600"><span class="text-gray-600"><?php echo e(__('Connect Instagram Accounts Using Unofficial Login Method')); ?></span></span>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-8">
                    <a class="btn btn-light btn-sm" href="<?php echo e(url("app/channels")); ?>">
                        <span><i class="fa-light fa-angle-left"></i></span>
                        <span><?php echo e(__('Back')); ?></span>
                    </a>
                </div>
            </div>
        </div>

        <form class="actionForm" action="<?php echo e(module_url("proccess")); ?>" method="POST" data-call-after="Authentication(result);">
            
            <div class="card">
                <div class="card-body">
                    <div class="mb-4">
                        <label for="ig_username" class="form-label"><?php echo e(__("Instagram Username")); ?></label>
                        <input type="text" class="form-control" name="ig_username" id="ig_username" placeholder="<?php echo e(__("Enter Instagram Username")); ?>">
                    </div>

                    <div class="mb-0">
                        <label for="ig_password" class="form-label"><?php echo e(__("Instagram Password")); ?></label>
                        <input type="password" class="form-control" name="ig_password" id="ig_password" placeholder="<?php echo e(__("Enter Instagram Password")); ?>">
                    </div>

                    <div class="mt-4 d-none" id="IG_2FA_BOX">
                        <label for="ig_verification_code" class="form-label"><?php echo e(__("Two-Factor Authentication Code")); ?></label>
                        <input type="text" class="form-control" name="ig_verification_code" id="ig_verification_code" placeholder="<?php echo e(__("Enter the verification code")); ?>">
                    </div>

                    <div class="mt-4 d-none" id="IG_SECURITY_BOX">
                        <label for="ig_security_code" class="form-label"><?php echo e(__("Two-Factor Authentication Code")); ?></label>
                        <input type="text" class="form-control" name="ig_security_code" id="ig_security_code" placeholder="<?php echo e(__("Enter the security code")); ?>">
                    </div>

                    <input type="text" class="form-control d-none" name="ig_options" id="ig_options" >
                    <input type="text" class="form-control d-none" name="ig_type" id="ig_type" value="1">
                </div>
                <div class="card-footer">
                    <button class="btn btn-dark w-100"><?php echo e(__("Submit")); ?></button>
                </div>
            </div>

        </form>

    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script type="text/javascript">
    function Authentication(result){
        const options = result.options;
        $("#ig_options").val(JSON.stringify(options));
        if(result.type == "2FA"){
            $("#IG_2FA_BOX").removeClass("d-none");
            $("#IG_SECURITY_BOX").addClass("d-none");
            $("#ig_type").val(2);
        } else if (result.type == "challenge"){
            $("#IG_SECURITY_BOX").removeClass("d-none");
            $("#IG_2FA_BOX").addClass("d-none");
            $("#ig_type").val(3);
        }else{
            $("#ig_type").val(1);
        }
    }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/social.kokonuts.my/modules/AppChannelInstagramUnofficial/resources/views/oauth.blade.php ENDPATH**/ ?>