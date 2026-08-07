<x-guest-layout>
    <h1 class="mb-1 text-xl font-bold text-gray-900">Create your account</h1>
    <p class="mb-6 text-sm text-gray-500">Start catching missed calls in minutes.</p>

    <x-google-button label="Sign up with Google" />

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Mobile number (the number that receives SMS; ties to the consent below) -->
        <div class="mt-4">
            <x-input-label for="phone" :value="__('Mobile number')" />
            <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')" required autocomplete="tel" placeholder="(555) 123-4567" />
            <p class="mt-1 text-xs text-gray-500">We text your verification code and account alerts to this number.</p>
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- SMS consent (OPTIONAL; A2P 10DLC error 30923: consent must not be required for service) -->
        <div class="mt-4">
            <label for="sms_consent" class="flex items-start gap-2">
                <input id="sms_consent" name="sms_consent" type="checkbox" value="1"
                       class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('sms_consent') ? 'checked' : '' }}>
                <span class="text-sm text-gray-600">
                    <span class="font-medium text-gray-700">Optional:</span> I agree to receive SMS text messages from Textback at the mobile number above, including a one-time verification code and account notifications. Message frequency varies. Message and data rates may apply. Reply STOP to opt out, HELP for help. This is not required to create an account or use Textback. See our
                    <a href="{{ route('privacy') }}" class="text-indigo-600 underline" target="_blank">Privacy Policy</a> and
                    <a href="{{ route('terms') }}" class="text-indigo-600 underline" target="_blank">Terms</a>.
                </span>
            </label>
            <x-input-error :messages="$errors->get('sms_consent')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
