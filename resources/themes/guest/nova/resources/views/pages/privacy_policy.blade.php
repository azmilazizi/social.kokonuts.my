<div class="min-h-screen py-12 px-4 bg-blueGray-50 py-24">
    <div class="max-w-4xl mx-auto">
        <!-- Header Section -->
        <div class="mb-8 text-center">
            <h1 class="text-6xl md:text-7xl font-bold font-heading leading-none text-gray-900 mb-4">{{ __("Privacy Policy") }}</h1>
            <p class="text-lg text-gray-600 font-medium">{{ __('We are committed to safeguarding your personal information.') }}</p>
        </div>
        <!-- Main Content -->
        <div class="bg-white rounded-4xl shadow-sm p-8">
            <div class="prose prose-content prose-lg max-w-none">
                @php
                    $privacyPolicy = trim((string) get_option('privacy_policy'));
                @endphp
                @if($privacyPolicy)
                    {!! htmlspecialchars_decode($privacyPolicy) !!}
                @else
                    <h2>{{ __("Overview") }}</h2>
                    <p>{{ __("This Privacy Policy explains how Kokonuts collects, uses, and protects your information when you access our website, mobile apps, and services (collectively, the \"Service\"). By using the Service, you agree to the practices described here.") }}</p>

                    <h2>{{ __("Information We Collect") }}</h2>
                    <ul>
                        <li>{{ __("Account information such as your name, email address, and password.") }}</li>
                        <li>{{ __("Profile and social account data you choose to connect, including tokens and public profile details.") }}</li>
                        <li>{{ __("Content you create, upload, or schedule through the Service.") }}</li>
                        <li>{{ __("Usage data such as log files, device identifiers, IP address, and analytics events.") }}</li>
                    </ul>

                    <h2>{{ __("How We Use Information") }}</h2>
                    <ul>
                        <li>{{ __("Provide, operate, and improve the Service.") }}</li>
                        <li>{{ __("Authenticate users, secure accounts, and prevent fraud.") }}</li>
                        <li>{{ __("Communicate with you about updates, support, and administrative messages.") }}</li>
                        <li>{{ __("Comply with legal obligations and enforce our Terms of Service.") }}</li>
                    </ul>

                    <h2>{{ __("Sharing and Disclosure") }}</h2>
                    <p>{{ __("We do not sell your personal information. We may share data with trusted service providers (such as hosting, analytics, and payment processors) under contractual obligations, or as required by law.") }}</p>

                    <h2>{{ __("Data Retention") }}</h2>
                    <p>{{ __("We retain personal data only as long as necessary to provide the Service or comply with legal requirements. You may request deletion of your account or data by contacting us.") }}</p>

                    <h2>{{ __("Your Choices") }}</h2>
                    <p>{{ __("You can access, update, or delete your account information from your profile settings. You may also withdraw consent for connected social accounts at any time.") }}</p>

                    <h2>{{ __("Security") }}</h2>
                    <p>{{ __("We use reasonable administrative, technical, and physical safeguards to protect your data. However, no method of transmission or storage is 100% secure.") }}</p>

                    <h2>{{ __("Children's Privacy") }}</h2>
                    <p>{{ __("The Service is not directed to children under 13 (or other age required by local law). We do not knowingly collect personal data from children.") }}</p>

                    <h2>{{ __("Updates to This Policy") }}</h2>
                    <p>{{ __("We may update this Privacy Policy from time to time. If we make material changes, we will update the effective date and provide notice where required.") }}</p>

                    <h2>{{ __("Contact Us") }}</h2>
                    <p>{{ __("If you have questions about this Privacy Policy, please contact us at support@kokonuts.my.") }}</p>

                    <p><strong>{{ __("Effective date:") }}</strong> {{ __("January 26, 2025") }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
