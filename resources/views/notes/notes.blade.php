<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-secondary">
            {{ __('Notes') }}
            <a class="btn btn-sm shadow-sm bg-white float-end" href="#" data-bs-toggle="modal" data-bs-target="#NoteCreateModal">+</a>
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div id="NotesList" class="col-12">
        </div>
    </div>

    @include('notes._create')

</x-app-layout>
