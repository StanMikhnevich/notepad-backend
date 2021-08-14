<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 shadow-sm font-mono">
    <!-- Primary Navigation Menu -->
    <div class="container">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('index') }}">
                        <x-application-logo class="block h-10 w-auto fill-current text-gray-600" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link class="no-underline" :href="route('notes.all')" :active="request()->input('show') == 'all'">
                        {{ __('All') }}
                    </x-nav-link>

                    <x-nav-link class="no-underline" :href="route('notes.public')" :active="request()->input('show') == 'public'">
                        {{ __('Public') }}
                    </x-nav-link>

                    @auth
                    <x-nav-link class="no-underline" :href="route('notes.my')" :active="request()->input('show') == 'my'">
                        {{ __('My') }}
                    </x-nav-link>

                    <x-nav-link class="no-underline" :href="route('notes.shared')" :active="request()->input('show') == 'shared'">
                        {{ __('Shared') }}
                    </x-nav-link>
                    @endauth
                </div>

                <form action="{{ route('notes.index') }}" method="GET">
                    <div class="input-group input-group-sm my-3 mx-4">
                        <input type="text" name="search" class="form-control" value="{{ request()->get('search') ?? '' }}" placeholder="Search..." aria-describedby="AppNavSearch" required>
                        <button class="btn btn-outline-dark" type="submit" id="AppNavSearch"><i class="bi bi-search"></i></button>
                    </div>
                </form>

            </div>


            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
            @auth
                @if(!Auth::user()->hasVerifiedEmail())
                <a href="{{ route('verification.notice') }}" class="bg-red-500 rounded-full py-2 px-3 me-3 no-underline text-center text-white font-mono"><i class="bi bi-person-x"></i> Not verified!</a>
                @endif
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            @else
                @if (Route::has('login'))
                <a href="{{ route('login') }}" class="text-sm text-gray-700 no-underline">Log in</a>
                @endif

                @if (Route::has('register'))
                <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-700 no-underline">Register</a>
                @endif
            @endauth

            </div>
        </div>
    </div>
</nav>
