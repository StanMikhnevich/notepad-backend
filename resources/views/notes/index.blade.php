<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between text-xl py-8">
            <div class="flex space-x-4 justify-between py-2">

                <h2 class="font-semibold text-2xl leading-10 text-gray-500">
                    @if(isset($filters['search']))
                        {{ __('Search') . ' : "' . $filters['search'] . '"'}}
                    @else
                        @switch($filters['show'] ?? '')
                            @case('shared')
                            {{ __('Shared notes') }}
                            @break
                            @case('my')
                            {{ __('My notes') }}
                            @break
                            @case('public')
                            {{ __('Public notes') }}
                            @break
                            @default
                            {{ __('All notes') }}
                        @endswitch
                    @endif
                </h2>

                <span class="font-semibold text-2xl leading-10 text-gray-300">({{ $notes->total() }})</span>

                <form action="" method="GET" class="flex space-x-4">

                    <select name="perPage" id="NotesItemsPerPage" onchange="$('#NotesHeaderPage').val(1); this.form.submit();"
                            class="bg-transparent border-transparent text-center cursor-pointer text-sm focus:outline-none">
                        <option value="15" {{ ($filters['perPage'] ?? '') == 15 ? 'selected' : '' }}>15</option>
                        <option value="30" {{ ($filters['perPage'] ?? '') == 30 ? 'selected' : '' }}>30</option>
                        <option value="50" {{ ($filters['perPage'] ?? '') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ ($filters['perPage'] ?? '') == 100 ? 'selected' : '' }}>100</option>
                    </select>

                    <select name="order" id="NotesItemsPerPage" onchange="this.form.submit();"
                            class="bg-transparent border-transparent text-center cursor-pointer text-sm focus:outline-none">
                        <option value="title" {{ ($filters['order'] ?? '') == 'title' ? 'selected' : '' }}>Alphabetical</option>
                        <option value="created_at" {{ ($filters['order'] ?? '') == 'created_at' ? 'selected' : '' }}>Newest</option>
                        <option value="user_id" {{ ($filters['order'] ?? '') == 'user_id' ? 'selected' : '' }}>By user</option>
                    </select>

                    <input type="hidden" name="show" value="{{ $filters['show'] }}" required>

                    <input id="NotesHeaderPage" type="hidden" name="page" value="{{ $filters['page'] ?? 1 }}" required>

                    @isset($filters['search'])
                        <input type="hidden" name="search" value="{{ $filters['search'] }}" required>
                    @endisset

                </form>

            </div>

            @auth
                @if(auth()->user()->hasVerifiedEmail())
                <div class="flex py-2">
                    <div class="w-12 h-8 bg-white rounded-lg no-underline text-center shadow-md cursor-pointer"
                         data-modal-toggle="modal" data-modal-target="#NoteCreateModal">
                        <i class="mdi mdi-plus"></i>
                    </div>
                </div>
                @endif
            @endauth

        </div>


    </x-slot>

    <div id="NotesList">
        @forelse ($notes->items() as $note)
        <div id="NoteItem{{ $note->id }}" class="bg-white mb-5 p-4 px-6 shadow-md hover:shadow-lg rounded-lg note-item tr-1">
            <div class="flex space-x-4 justify-between">
                <span class="flex space-x-4 py-2 text-gray-500">
                    <a href="{{ route('notes.index') }}/{{ $note->uid }}" class="text-gray-500 no-underline font-medium text-xl">{{ $note->title }}</a>

                    @if($note->private)
                        <i class="mdi mdi-lock"></i>
                    @endif

                    @if($note->hasAttachments())
                        <i class="mdi mdi-attachment"></i>
                    @endif

                </span>

                <div class="flex space-x-8 justify-between py-2 text-gray-500">

                    <span>{{ $note->user_id == auth()->id() ? 'You' : $note->user->name }}</span>

                    <span class="float-end ms-3 text-secondary text-right text-xl font-half-lg">
                        {{ $note->created_at->diffForHumans() }}<br>{{ $note->updated_at->diffForHumans() }}
                    </span>

                    @if(($note->user_id == auth()->id()))
                    <x-dropdown align="right" width="36">
                        <x-slot name="trigger">
                            <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                <i class="mdi mdi-dots-vertical text-xl"></i>
                            </button>
                        </x-slot>

                        <x-slot name="content">
{{--                            <x-dropdown-link class="cursor-pointer" data-modal-toggle="modal" data-modal-target="#NotePrintModal">--}}
{{--                                <i class="mdi mdi-printer mr-3" ></i> Print--}}
{{--                            </x-dropdown-link>--}}
                            @if($note->user_id == auth()->id())
{{--                            <x-dropdown-link class="cursor-pointer" data-modal-toggle="modal" data-modal-target="#NoteShareModal">--}}
{{--                                <i class="mdi mdi-share mr-3"></i> Share--}}
{{--                            </x-dropdown-link>--}}
                            <x-dropdown-link href="{{ route('notes.edit', $note->uid) }}" class="cursor-pointer" data-modal-toggle="modal" data-modal-target="#NoteEditModal">
                                <i class="mdi mdi-pencil mr-3"></i> Edit
                            </x-dropdown-link>
                            <x-dropdown-link class="cursor-pointer" data-modal-toggle="modal" data-modal-target="#NoteDeleteModal" onclick="deleteNote('{{ $note->uid }}', {{ $note->id }}, '{{ $note->title }}')">
                                <i class="mdi mdi-close text-red-500 mr-3" ></i> Delete
                            </x-dropdown-link>
                            @endif

                        </x-slot>
                    </x-dropdown>
                    @endif
                </div>

            </div>

        </div>
        @empty
        <div class="text-center text-secondary">No results</div>
        @endforelse

        @if($notes->lastPage() > 1)
        <div class="card-section py-5">
            <div class="table-pagination text-center">
                <div class="table-pagination-navigation py-5">
                    <form action="" method="GET">
                        <input type="hidden" name="show" value="{{ $filters['show'] }}" required>

                        @isset($filters['search'])
                        <input type="hidden" name="search" value="{{ $filters['search'] }}" required>
                        @endisset

                        @isset($filters['perPage'])
                        <input type="hidden" name="perPage" value="{{ $filters['perPage'] }}" required>
                        @endisset

                        @isset($filters['order'])
                        <input type="hidden" name="order" value="{{ $filters['order'] }}" required>
                        @endisset

                        <input id="NotesPaginatorPage" type="hidden" name="page" value="{{ $filters['page'] ?? 1 }}" required>

                        <button class="button button-default w-14 text-center mx-1 py-2 {{ $notes->currentPage() == 1 ? 'disabled' : '' }}"
                            type="button" onclick="$('#NotesPaginatorPage').val(1).closest(form).submit()">
                            <i class="mdi mdi-chevron-left"></i>
                        </button>

                        @for($page = 1; $page <= $notes->lastPage(); $page++)
                        <button class="button button-default w-10 text-center mx-1 py-2 {{ $page == $notes->currentPage() ? 'bg-purple-400 text-white shadow-sm rounded-md' : '' }}"
                            type="button" onclick="$('#NotesPaginatorPage').val({{ $page }}).closest(form).submit()">
                            {{ $page }}
                        </button>
                        @endfor

                        <button class="button button-default w-14 text-center mx-1 py-2 {{ $notes->currentPage() == $notes->lastPage() ? 'disabled' : '' }}"
                            type="button" onclick="$('#NotesPaginatorPage').val({{ $notes->lastPage() }}).closest(form).submit()">
                            <i class="mdi mdi-chevron-right"></i>
                        </button>

                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>

    @auth
    @include('notes._create')
    @endauth

</x-app-layout>
