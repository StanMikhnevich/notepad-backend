<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-secondary">
            {{ __('Notes') }}
            @auth
            <a class="btn btn-sm shadow-sm bg-white float-end" href="#" data-bs-toggle="modal" data-bs-target="#NoteCreateModal">+</a>
            @endauth
        </h2>
    </x-slot>

    <div class="container mt-4">
        <div id="NotesListAll" class="col-12">
        </div>
    </div>

    @auth
    @include('notes._create')
    @endauth

</x-app-layout>
