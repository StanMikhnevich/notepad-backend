<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-secondary">
            <span class="text-uppercase">{{ $note->title }}</span>

            @if($note->private)
            <i class="bi bi-lock-fill text-gray-500 ms-3"></i>
            @endif

            @if(($note->user_id ?? '') == (auth()->user()->id ?? ''))
            <a class="btn btn-sm shadow-sm bg-white float-end ms-3" href="#" data-bs-toggle="modal" data-bs-target="#NoteShareModal"><i class="bi bi-share"></i></a>
            <a class="btn btn-sm shadow-sm bg-white float-end ms-3" href="{{ route('notes.edit', $note->uid) }}"><i class="bi bi-pencil-square"></i></a>
            @endif

            <span class="float-end text-right font-half">
                {{ $note->created_at->diffForHumans() }}<br>{{ $note->updated_at->diffForHumans() }}
            </span>
            <span class="float-end mx-3">{{ $note->user->name }}</span>
        </h2>
    </x-slot>

    <div class="container mt-4">
        <div id="NoteItem">

            <div class="bg-white mb-3 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    {!! $note->text_md !!}
                    @isset($note->attachments[0])
                    <hr class="my-5">
                        <h5 class="form-label">Attachments</h5>

                        @foreach ($note->attachments as $file)
                        <a href="{{ asset('storage/' . $file->path) }}" target="_blank" class="badge bg-light shadow-sm text-decoration-none text-secondary p-3 me-1 mb-1">
                            <i class="bi bi-paperclip me-3"></i> {{ $file->original }}
                        </a>
                        @endforeach
                    @endisset
                </div>
            </div>

            @if($note->hasShared() && ($note->user_id == (auth()->id() ?? '')))
            <div class="bg-white mb-3 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-3">
                        <h5 class="form-label">Shared with</h5>
                    </div>

                    @foreach ($note->users as $sharing)
                    <span id="NoteSharingItem{{ $sharing->id }}" class="badge bg-light shadow-sm text-secondary p-3 me-1 mb-1">
                        <i class="bi bi-person-fill me-3"></i> {{ $sharing->name }}
                    </span>
                    @endforeach

                </div>
            </div>
            @endisset


        </div>
    </div>

    @if(($note->user_id ?? '') == (auth()->user()->id ?? ''))
    @include('notes._create')
    @include('notes._share')
    @endif

</x-app-layout>
