<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-secondary">
            @switch($filters['show'] ?? '')
                @case('shared')
                {{ __('Shared notes') }} ({{ count($notes) }})
                @break
                @case('my')
                {{ __('My notes') }} ({{ count($notes) }})
                @break
                @case('public')
                {{ __('Public notes') }} ({{ count($notes) }})
                @break
                @default
                {{ __('All') }} ({{ count($notes) }})
            @endswitch

            @isset($filters['search'])
                {{ __('Search') . ' : ' . $filters['search']}}
            @endisset

            @auth
            <div class="bg-white rounded-2 no-underline text-center shadow-sm cursor-pointer float-right w-12" data-bs-toggle="modal" data-bs-target="#NoteCreateModal"><i class="bi bi-plus"></i></div>
            @endauth
        </h2>
    </x-slot>

    <div class="container">
        <div id="NotesList" class="col-12">

            @forelse ($notes as $note)
                <div id="NoteItem{{ $note->id }}" class="bg-white mb-3 shadow-sm hover:shadow-lg rounded-3 note-item ">
                    <div class="p-4">
                        <a class="text-yellow-400 hover:text-yellow-500 no-underline" href="{{ route('notes.index') }}/{{ $note->uid }}"><strong>{{ $note->title }}</strong></a>

                        @if($note->private)
                            <i class="bi bi-lock-fill text-gray-500 ms-3"></i>
                        @endif

                        @if($note->hasAttachments())
                            <i class="bi bi-paperclip text-gray-500 ms-3"></i>
                        @endif

                        @if(($filters['show'] ?? '') == 'my')
                            @if($note->hasShared())
                            <i class="bi bi-share text-gray-500 ms-3"></i>
                            @endif
                        @endif


                        <div class="float-end">
                            <span>{{ $note->user_id == auth()->id() ? 'You' : $note->user->name }}</span>

                            @if(($filters['show'] ?? '') == 'my')
                            <span class="float-end item-actions text-dark tr-1 ms-4">
                                <a href="{{ route('notes.edit', $note->uid) }}" class="text-dark"><i class="bi bi-pencil-square"></i></a>
                                <i class="bi bi-x-lg text-danger ms-3 cursor-pointer" onclick="deleteNote('{{ $note->uid }}', {{ $note->id }}, '{{ $note->title }}')"></i>
                            </span>
                            @endif

                            <div class="float-right ms-3 text-secondary text-right font-half-lg">
                                {{ $note->created_at->diffForHumans() }}<br>{{ $note->updated_at->diffForHumans() }}
                            </div>

                        </div>

                     </div>
                </div>
            @empty
                <div class="text-center text-secondary">No results</div>
            @endforelse

        </div>
    </div>

    @auth
    @include('notes._create')
    @endauth

</x-app-layout>
