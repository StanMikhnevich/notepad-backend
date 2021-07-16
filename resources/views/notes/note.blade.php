<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-secondary">
            {{ $note->title }}

            @if($note->private)
            <span class="ml-3 badge rounded-pill bg-dark text-light">Private</span>
            @endif

            @if($isAuthor)
            <a class="btn btn-sm shadow-sm bg-white float-end ms-3" href="#" data-bs-toggle="modal" data-bs-target="#NoteShareModal"><i class="bi bi-share"></i></a>
            <a class="btn btn-sm shadow-sm bg-white float-end ms-3" href="{{ route('notes') . '/note/' . $note->id }}/edit"><i class="bi bi-pencil-square"></i></a>
            @endif

            <span class="float-end">{{ $note->created_at }}</span>
            <span class="float-end mx-3">{{ $note->_author->name }}</span>


        </h2>
    </x-slot>

    <div class="container mt-4">
        <div id="NoteItem">

            <div class="bg-white mb-3 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    {!! $note->text !!}
                </div>
            </div>

            @if(isset($note->attachments[0]))
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @foreach ($note->attachments as $file)
                        <a href="{{ asset('storage/' . $file->path) }}" target="_blank" class="badge bg-dark text-decoration-none text-light p-3 me-1 mb-1"><i class="bi bi-paperclip me-3"></i>{{ $file->_name }}</a>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>

    @auth
    @include('notes._create')
    @include('notes._share')
    @endauth

</x-app-layout>
