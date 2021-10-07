<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
    <div>
        {{ $logo }}
    </div>

    <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-xl overflow-hidden sm:rounded-lg">
        {{ $slot }}
    </div>

    <div class="w-full text-center mt-4">
    @if (Route::has('login'))
        <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
            {{ __('Login') }}
        </a><i class="mx-2 text-purple-400">•</i>
    @endif

    @if (Route::has('register'))
        <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('register') }}">
            {{ __('Register') }}
        </a><i class="mx-2 text-purple-400">•</i>
    @endif

    @if (Route::has('password.request'))
        <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
            {{ __('Restore') }}
        </a>
    @endif
    </div>


</div>
