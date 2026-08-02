<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Textback - never lose a lead to a missed call</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white font-sans text-gray-900 antialiased">
    <header class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
        <span class="text-xl font-bold tracking-tight text-indigo-600">Textback</span>
        <nav class="flex items-center gap-4 text-sm font-medium">
            @auth
                <a href="{{ route('dashboard') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-500">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Log in</a>
                <a href="{{ route('register') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-500">Start free</a>
            @endauth
        </nav>
    </header>

    <main>
        {{-- Hero --}}
        <section class="mx-auto max-w-4xl px-6 pb-16 pt-16 text-center">
            <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-indigo-600">For realtors, contractors & freelancers</p>
            <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl">
                Never lose a lead to a missed call.
            </h1>
            <p class="mx-auto mt-5 max-w-2xl text-lg text-gray-600">
                When you miss a call, Textback instantly texts the caller back as if from you, so they do not call the next person on the list. It also asks happy clients for Google reviews. All by text.
            </p>
            <div class="mt-8 flex items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="rounded-md bg-indigo-600 px-6 py-3 text-base font-semibold text-white hover:bg-indigo-500">Start free</a>
                <a href="#how" class="text-base font-semibold text-gray-700 hover:text-gray-900">See how it works</a>
            </div>
            <p class="mt-4 text-sm text-gray-500">Free until it recovers your first {{ config('textback.trial_lead_cap') }} leads. No contract. Money-back guarantee.</p>
        </section>

        {{-- Stat strip --}}
        <section class="border-y border-gray-100 bg-gray-50 py-10">
            <div class="mx-auto grid max-w-5xl grid-cols-1 gap-8 px-6 text-center sm:grid-cols-3">
                <div>
                    <p class="text-3xl font-bold text-indigo-600">21x</p>
                    <p class="mt-1 text-sm text-gray-600">more likely to convert a lead when you respond in 5 minutes</p>
                </div>
                <div>
                    <p class="text-3xl font-bold text-indigo-600">78%</p>
                    <p class="mt-1 text-sm text-gray-600">of leads go with the first business to respond</p>
                </div>
                <div>
                    <p class="text-3xl font-bold text-indigo-600">35%</p>
                    <p class="mt-1 text-sm text-gray-600">of calls to local businesses go unanswered</p>
                </div>
            </div>
        </section>

        {{-- Pillars --}}
        <section id="how" class="mx-auto max-w-5xl px-6 py-16">
            <h2 class="text-center text-2xl font-bold text-gray-900">Three ways Textback wins you money</h2>
            <div class="mt-10 grid grid-cols-1 gap-8 sm:grid-cols-3">
                <div class="rounded-xl bg-white p-6 shadow ring-1 ring-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Missed-call text-back</h3>
                    <p class="mt-2 text-sm text-gray-600">Miss a call, and the caller instantly gets a friendly text from your number. The conversation starts before they move on.</p>
                </div>
                <div class="rounded-xl bg-white p-6 shadow ring-1 ring-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Review requests</h3>
                    <p class="mt-2 text-sm text-gray-600">One tap texts a happy client your Google review link. Fresh reviews mean better ranking and more referrals.</p>
                </div>
                <div class="rounded-xl bg-white p-6 shadow ring-1 ring-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Follow-up</h3>
                    <p class="mt-2 text-sm text-gray-600">Keep cold leads and past clients warm with simple follow-up texts, so no opportunity slips away.</p>
                </div>
            </div>
            <div class="mt-12 text-center">
                <a href="{{ route('register') }}" class="rounded-md bg-indigo-600 px-6 py-3 text-base font-semibold text-white hover:bg-indigo-500">Get set up in 10 minutes</a>
            </div>
        </section>
    </main>

    <footer class="border-t border-gray-100 bg-gray-50">
        <div class="mx-auto max-w-6xl px-6 py-10">
            <div class="flex flex-col items-center justify-between gap-6 sm:flex-row sm:items-start">
                <div class="text-center sm:text-left">
                    <span class="text-lg font-bold tracking-tight text-indigo-600">Textback</span>
                    <p class="mt-1 max-w-xs text-sm text-gray-500">Never lose a lead to a missed call. Automatic text-back, reviews, and follow-ups by SMS.</p>
                </div>

                <div class="flex flex-col gap-8 text-sm sm:flex-row">
                    <div>
                        <p class="mb-2 font-semibold text-gray-900">Product</p>
                        <ul class="space-y-1.5 text-gray-500">
                            <li><a href="{{ route('register') }}" class="hover:text-gray-900">Start free</a></li>
                            <li><a href="{{ route('login') }}" class="hover:text-gray-900">Log in</a></li>
                            <li><a href="#how" class="hover:text-gray-900">How it works</a></li>
                        </ul>
                    </div>
                    <div>
                        <p class="mb-2 font-semibold text-gray-900">Legal</p>
                        <ul class="space-y-1.5 text-gray-500">
                            <li><a href="{{ route('privacy') }}" class="hover:text-gray-900">Privacy Policy</a></li>
                            <li><a href="{{ route('terms') }}" class="hover:text-gray-900">Terms of Service</a></li>
                        </ul>
                    </div>
                    <div>
                        <p class="mb-2 font-semibold text-gray-900">Contact</p>
                        <ul class="space-y-1.5 text-gray-500">
                            <li><a href="mailto:support@text-back.net" class="hover:text-gray-900">support@text-back.net</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="mt-8 border-t border-gray-200 pt-6 text-center text-xs text-gray-400 sm:text-left">
                &copy; {{ date('Y') }} Textback. All rights reserved. Message and data rates may apply. Reply STOP to opt out.
            </div>
        </div>
    </footer>
</body>
</html>
