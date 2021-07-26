<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-secondary">
            {{ __('Search results') }} ({{ count($notes)  }})
        </h2>
    </x-slot>

    <div class="container mt-4">
        <div id="NotesListSearch" class="col-12">
            @forelse ($notes as $note)
                <div class="bg-white mb-3 shadow-sm rounded-3">
                    <div class="p-6">
                        <a class="text-decoration-none" href="{{ route('notes.index') }}/{{ $note->uid }}"><strong>{{ $note->title }}</strong></a>

                        @if($note->private)
                            <i class="bi bi-lock-fill text-gray-500 ms-3"></i>
                        @endif

                        <span class="float-end">
                            {{ $note->user->name }}
                            <span class="text-secondary ms-3" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $note->created_at }}"><small>{{ $note->created_at->diffForHumans() }}</small></span>
                        </span>
                     </div>
                </div>
            @empty
                <div class="text-center text-secondary">No results</div>
            @endforelse

        </div>
    </div>
</x-app-layout>
