<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-secondary">
            {{ __('Search results') }}
        </h2>
    </x-slot>

    <div class="container mt-4">
        <div id="NotesListSearch" class="col-12">
            @forelse ($notes as $note)
                <div class="bg-white mb-3 shadow-sm rounded-3">
                    <div class="p-6">

                        <a class="text-decoration-none" href="/note/{{ $note->id }}"><strong>{{ $note->title }}</strong></a>
                        <span class="float-end text-secondary">{{ $note->created_at }}</span>
                        <span class="float-end mx-3">{{ $note->_author->name }}</span>

                        @if($note->private)
                        <span class="ml-3 badge rounded-pill bg-dark text-light">Private</span>
                        @endif

                     </div>
                </div>
            @empty
                <div class="text-center text-secondary">No results</div>
            @endforelse

        </div>
    </div>
</x-app-layout>
