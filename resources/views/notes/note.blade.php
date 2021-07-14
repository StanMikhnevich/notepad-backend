<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $title }}
            @if($private)
            <span class="ml-3 badge rounded-pill bg-dark text-light">Private</span>
            @endif
            <a class="btn btn-sm shadow-sm bg-white float-end" href="#" data-bs-toggle="modal" data-bs-target="#NoteShareModal"><i class="bi-share"></i></a>
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div id="NotesItem">

            <div class="bg-white mb-3 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    {{ $text }}
                </div>
            </div>

        </div>
    </div>

    @include('notes._create')
    @include('notes._share')

</x-app-layout>
