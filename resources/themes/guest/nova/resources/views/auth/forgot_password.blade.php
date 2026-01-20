<section class="min-h-screen bg-gray-100 flex items-center justify-center px-4 py-16">
    <div class="w-full max-w-md text-center">
        <a class="inline-flex justify-center mb-6" href="{{ url('') }}">
            <img class="h-12" src="{{ url( get_option("website_logo_brand_dark", asset('public/img/logo-brand-dark.png')) ) }}" alt="">
        </a>
        <h1 class="text-3xl font-semibold text-gray-900 mb-8">
            {{ __("Forgot Password") }}
        </h1>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 text-left">
            <form class="actionForm w-full space-y-5" action="{{ module_url('do_forgot_password') }}" method="POST">
                <div>
                    <label for="email" class="block text-sm text-gray-700 font-semibold mb-2">{{ __("Email Address") }}</label>
                    <input type="email" id="email" name="email"
                        class="w-full px-4 py-3 text-gray-700 font-medium bg-white border border-gray-200 rounded-lg focus:ring focus:ring-indigo-300 outline-none"
                        placeholder="{{ __('Enter your email address') }}" required autofocus>
                </div>

                <div class="mb-1">
                    {!! Captcha::render(); !!}
                </div>

                <div class="msg-error mb-2"></div>

                <button type="submit"
                    class="py-3.5 px-6 w-full text-white font-semibold rounded-xl shadow-sm focus:ring focus:ring-gray-300 bg-slate-900 hover:bg-black transition ease-in-out duration-200">
                    {{ __("Confirm") }}
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-gray-500 pt-6">
            <a href="{{ url('auth/login') }}" class="text-gray-900 hover:text-gray-700 font-semibold">
                <i class="fa fa-arrow-left mr-1"></i>{{ __("Back to login") }}
            </a>
        </p>
    </div>
</section>
