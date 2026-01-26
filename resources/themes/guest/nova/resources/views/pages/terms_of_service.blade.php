<div class="min-h-screen py-12 px-4 bg-blueGray-50 py-24">
    <div class="max-w-4xl mx-auto">
        <!-- Header Section -->
        <div class="mb-8 text-center">
            <h1 class="text-6xl md:text-7xl font-bold font-heading leading-none text-gray-900 mb-4">{{ __("Terms & Conditions") }}</h1>
            <p class="text-lg text-gray-600 font-medium">{{ __('Please read these Terms & Conditions carefully before using this service.') }}</p>
        </div>
        <!-- Main Content -->
        <div class="bg-white rounded-4xl shadow-sm p-8">
            <div class="prose prose-content prose-lg max-w-none">
                @php
                    $termsOfUse = trim((string) get_option('terms_of_use'));
                @endphp
                @if($termsOfUse)
                    {!! htmlspecialchars_decode($termsOfUse) !!}
                @else
                    <h2>{{ __("Acceptance of Terms") }}</h2>
                    <p>{{ __("By accessing or using Kokonuts (the \"Service\"), you agree to these Terms of Service (\"Terms\"). If you do not agree, do not use the Service.") }}</p>

                    <h2>{{ __("Eligibility") }}</h2>
                    <p>{{ __("You must be at least 13 years old (or the minimum age required by your jurisdiction) to use the Service. By using the Service, you represent that you meet this requirement.") }}</p>

                    <h2>{{ __("Account Responsibilities") }}</h2>
                    <ul>
                        <li>{{ __("Provide accurate and complete information and keep it updated.") }}</li>
                        <li>{{ __("Safeguard your login credentials and notify us of unauthorized access.") }}</li>
                        <li>{{ __("You are responsible for activity that occurs under your account.") }}</li>
                    </ul>

                    <h2>{{ __("Use of the Service") }}</h2>
                    <ul>
                        <li>{{ __("Do not misuse the Service or attempt to access it using unauthorized methods.") }}</li>
                        <li>{{ __("Do not upload content that is illegal, infringing, or violates the rights of others.") }}</li>
                        <li>{{ __("Comply with all applicable laws and platform policies when publishing content.") }}</li>
                    </ul>

                    <h2>{{ __("Subscriptions and Payments") }}</h2>
                    <p>{{ __("If you purchase a paid plan, you agree to pay all fees and taxes. Fees are non-refundable except as required by law or stated in your plan.") }}</p>

                    <h2>{{ __("Third-Party Services") }}</h2>
                    <p>{{ __("The Service may integrate with third-party platforms. Your use of those platforms is governed by their terms, and we are not responsible for their practices.") }}</p>

                    <h2>{{ __("Content") }}</h2>
                    <p>{{ __("You retain ownership of content you create or upload. You grant us a limited license to host, process, and display your content solely to provide the Service.") }}</p>

                    <h2>{{ __("Termination") }}</h2>
                    <p>{{ __("We may suspend or terminate your account if you violate these Terms or if required by law. You may stop using the Service at any time.") }}</p>

                    <h2>{{ __("Disclaimers") }}</h2>
                    <p>{{ __("The Service is provided \"as is\" and \"as available\" without warranties of any kind. We do not guarantee uninterrupted or error-free operation.") }}</p>

                    <h2>{{ __("Limitation of Liability") }}</h2>
                    <p>{{ __("To the maximum extent permitted by law, Kokonuts is not liable for indirect, incidental, or consequential damages arising from your use of the Service.") }}</p>

                    <h2>{{ __("Changes to These Terms") }}</h2>
                    <p>{{ __("We may update these Terms from time to time. Continued use of the Service after changes become effective constitutes acceptance of the updated Terms.") }}</p>

                    <h2>{{ __("Contact") }}</h2>
                    <p>{{ __("If you have questions about these Terms, contact us at support@kokonuts.my.") }}</p>

                    <p><strong>{{ __("Effective date:") }}</strong> {{ __("January 26, 2025") }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
