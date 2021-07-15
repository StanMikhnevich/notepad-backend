<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-secondary">
            {{ __('Shared notes') }}
        </h2>
    </x-slot>

    <div class="container mt-4">
        <div id="NotesListShared" class="col-12">
        </div>
    </div>
</x-app-layout>
