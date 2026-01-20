<section class="min-h-screen bg-gray-100 flex items-center justify-center px-4 py-16">
    <div class="w-full max-w-md text-center">
        <a class="inline-flex justify-center mb-6" href="{{ url('') }}">
            <img class="h-12" src="{{ url( get_option("website_logo_brand_dark", asset('public/img/logo-brand-dark.png')) ) }}" alt="">
        </a>
        <h1 class="text-3xl font-semibold text-gray-900 mb-2">
            {{ __("Login") }}
        </h1>
        <p class="text-sm text-gray-500 mb-8">
            {{ __("Welcome, please sign in to your dashboard") }}
        </p>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 text-left">
            <form class="actionForm w-full" action="{{ module_url('do_login') }}" method="POST">
                <label class="block mb-5">
                    <p class="mb-2 text-sm text-gray-700 font-semibold leading-normal">{{ __("Email Address") }}</p>
                    <input type="text" id="username" name="username" class="px-4 py-3 w-full text-gray-700 font-medium placeholder-gray-400 bg-white outline-none border border-gray-200 rounded-lg focus:ring focus:ring-indigo-300" placeholder="{{ __('Enter your email address') }}">
                </label>
                <label class="block mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm text-gray-700 font-semibold leading-normal">{{ __("Password") }}</p>
                        <a href="{{ url('auth/forgot-password') }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium">{{ __("Forgot Password?") }}</a>
                    </div>
                    <input id="password" type="password" name="password" class="px-4 py-3 w-full text-gray-700 font-medium placeholder-gray-400 bg-white outline-none border border-gray-200 rounded-lg focus:ring focus:ring-indigo-300" placeholder="{{ __('Enter your password') }}">
                </label>
                <div class="mb-4">
                    {!! Captcha::render(); !!}
                </div>

                <div class="flex items-center mb-6">
                    <input class="w-4 h-4 text-gray-900 border-gray-300 rounded" id="remember" type="checkbox" name="remember" value="1">
                    <label class="ml-2 text-sm text-gray-600 font-medium" for="remember">
                        <span>{{ __("Remember Me") }}</span>
                    </label>
                </div>

                <div class="msg-error mb-2"></div>

                <button type="submit" class="mb-6 py-3.5 px-6 w-full text-white font-semibold rounded-xl shadow-sm focus:ring focus:ring-gray-300 bg-slate-900 hover:bg-black transition ease-in-out duration-200">
                    {{ __("Login") }}
                </button>
                    @php
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
                    @endphp
                @if(collect($socials)->where('status', 1)->count())
                    <p class="mb-4 text-xs text-gray-400 font-medium text-center uppercase tracking-wider">
                        {{ __("Or continue with") }}
                    </p>

                    <div class="flex flex-wrap justify-center -m-2">
                        @foreach($socials as $s)
                            @if($s['status'])
                                <a href="{{ $s['url'] }}" class="flex items-center justify-center p-4 bg-white hover:bg-gray-50 border border-gray-200 rounded-lg transition ease-in-out duration-200 gap-2 w-full mb-3 text-sm">
                                    {!! $s['icon'] !!}
                                    <span class="font-semibold leading-normal text-gray-700">{{ $s['label'] }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif
            </form>
        </div>

        @if(get_option("auth_signup_page_status", 1))
            <p class="text-center pt-6 text-sm text-gray-500">
                {{ __("Don't have an account?") }}
                <a href="{{ url('auth/signup') }}" class="text-gray-900 hover:text-gray-700 font-semibold">{{ __("Sign up") }}</a>
            </p>
        @endif
    </div>
</section>
