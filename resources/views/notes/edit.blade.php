<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-secondary">
            {{ $note->title }} ⟶ Edit

            <span class="float-end">{{ $note->created_at }}</span>
            <span class="float-end mx-3">{{ $note->_author->name }}</span>
        </h2>
    </x-slot>

    <div class="container mt-4">
        <div id="NoteItem">
            <form action="{{ route('notes.update') }}" method="POST">
                @csrf
                <input type="hidden" name="id" value="{{ $note->id }}" required>

                <div class="bg-white mb-3 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-3">
                            <label for="NoteEditTitle" class="form-label">Title</label>
                            <input id="NoteEditTitle" type="text" name="title" value="{{ $note->title }}" class="form-control" placeholder="Title" required>
                        </div>

                        <div class="mb-3">
                            <label for="NoteEditText" class="form-label">Text</label>
                            <textarea id="NoteEditText" name="text" class="form-control" rows="3" required>{{ $note->text }}</textarea>
                        </div>

                        <div class="form-check">
                            <input id="NoteEditPrivate" type="checkbox" name="private" class="form-check-input" value="1" {{ $note->private ? 'checked' : '' }}>
                            <label class="form-check-label" for="NoteEditPrivate">
                                Private note
                            </label>
                        </div>

                        <div class="mb-3 text-right px-0">
                            <a class="btn btn-link" href="{{ route('notes.my') }}">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>

                        <hr class="my-5">

                        <div class="mb-3">
                            <h5 class="form-label">Shared with</h5>
                        </div>

                        <div class="mb-3">
                            @foreach ($shared as $sharing)
                                <span id="NoteSharingItem{{ $sharing->id }}" class="badge bg-dark p-3">
                                    {{ $sharing->user->name }} <i class="bi bi-x-circle cursor-pointer ms-2" onclick="unshareNote({{ $sharing->id }}, '{{ $note->id }}', {{ $sharing->user_id }})"></i>
                                </span>
                            @endforeach
                        </div>


                    </div>
                </div>

            </form>
        </div>
    </div>

</x-app-layout>
