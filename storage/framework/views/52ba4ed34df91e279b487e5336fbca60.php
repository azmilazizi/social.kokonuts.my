<section class="min-h-screen bg-gray-100 flex items-center justify-center px-4 py-16">
    <div class="w-full max-w-md text-center">
        <a class="inline-flex justify-center mb-6" href="<?php echo e(url('')); ?>">
            <img class="h-12" src="<?php echo e(url( get_option("website_logo_brand_dark", asset('public/img/logo-brand-dark.png')) )); ?>" alt="">
        </a>
        <h1 class="text-3xl font-semibold text-gray-900 mb-2">
            <?php echo e(__("Login")); ?>

        </h1>
        <p class="text-sm text-gray-500 mb-8">
            <?php echo e(__("Welcome, please sign in to your dashboard")); ?>

        </p>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 text-left">
            <form class="actionForm w-full" action="<?php echo e(module_url('do_login')); ?>" method="POST">
                <label class="block mb-5">
                    <p class="mb-2 text-sm text-gray-700 font-semibold leading-normal"><?php echo e(__("Email Address")); ?></p>
                    <input type="text" id="username" name="username" class="px-4 py-3 w-full text-gray-700 font-medium placeholder-gray-400 bg-white outline-none border border-gray-200 rounded-lg focus:ring focus:ring-indigo-300" placeholder="<?php echo e(__('Enter your email address')); ?>">
                </label>
                <label class="block mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm text-gray-700 font-semibold leading-normal"><?php echo e(__("Password")); ?></p>
                        <a href="<?php echo e(url('auth/forgot-password')); ?>" class="text-sm text-gray-500 hover:text-gray-700 font-medium"><?php echo e(__("Forgot Password?")); ?></a>
                    </div>
                    <input id="password" type="password" name="password" class="px-4 py-3 w-full text-gray-700 font-medium placeholder-gray-400 bg-white outline-none border border-gray-200 rounded-lg focus:ring focus:ring-indigo-300" placeholder="<?php echo e(__('Enter your password')); ?>">
                </label>
                <div class="mb-4">
                    <?php echo Captcha::render(); ?>

                </div>

                <div class="flex items-center mb-6">
                    <input class="w-4 h-4 text-gray-900 border-gray-300 rounded" id="remember" type="checkbox" name="remember" value="1">
                    <label class="ml-2 text-sm text-gray-600 font-medium" for="remember">
                        <span><?php echo e(__("Remember Me")); ?></span>
                    </label>
                </div>

                <div class="msg-error mb-2"></div>

                <button type="submit" class="mb-6 py-3.5 px-6 w-full text-white font-semibold rounded-xl shadow-sm focus:ring focus:ring-gray-300 bg-black hover:bg-gray-800 border border-black transition ease-in-out duration-200">
                    <?php echo e(__("Login")); ?>

                </button>
                    <?php
                        $socials = [
                            'google' => [
                                'status' => get_option('auth_google_login_status', 0),
                                'url'    => url('auth/login/google'),
                                'icon'   => '<img src="'.theme_public_asset('images/google.png').'" class="size-6">',
                                'label'  => __("Continue with Google"),
                            ],
                            'facebook' => [
                                'status' => get_option('auth_facebook_login_status', 0),
                                'url'    => url('auth/login/facebook'),
                                'icon'   => '<i class="fa-brands fa-square-facebook text-2xl text-[#1877F2]"></i>',
                                'label'  => __("Continue with Facebook"),
                            ],
                            'x' => [
                                'status' => get_option('auth_x_login_status', 0),
                                'url'    => url('auth/login/x'),
                                'icon'   => '<i class="fab fa-x-twitter mr-2 text-2xl text-[#000]"></i>',
                                'label'  => __("Continue with X"),
                            ],
                        ];
                    ?>
                <?php if(collect($socials)->where('status', 1)->count()): ?>
                    <p class="mb-4 text-xs text-gray-400 font-medium text-center uppercase tracking-wider">
                        <?php echo e(__("Or continue with")); ?>

                    </p>

                    <div class="flex flex-wrap justify-center -m-2">
                        <?php $__currentLoopData = $socials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($s['status']): ?>
                                <a href="<?php echo e($s['url']); ?>" class="flex items-center justify-center p-4 bg-white hover:bg-gray-50 border border-gray-200 rounded-lg transition ease-in-out duration-200 gap-2 w-full mb-3 text-sm">
                                    <?php echo $s['icon']; ?>

                                    <span class="font-semibold leading-normal text-gray-700"><?php echo e($s['label']); ?></span>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <?php if(get_option("auth_signup_page_status", 1)): ?>
            <p class="text-center pt-6 text-sm text-gray-500">
                <?php echo e(__("Don't have an account?")); ?>

                <a href="<?php echo e(url('auth/signup')); ?>" class="text-gray-900 hover:text-gray-700 font-semibold"><?php echo e(__("Sign up")); ?></a>
            </p>
        <?php endif; ?>
    </div>
</section>
<?php /**PATH /var/www/social.kokonuts.my/resources/themes/guest/nova/resources/views/auth/login.blade.php ENDPATH**/ ?>