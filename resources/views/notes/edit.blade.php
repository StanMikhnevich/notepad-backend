<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-secondary">
            <span class="text-uppercase">{{ $note->title }} ⟶ Edit</span>

            @if($note->private)
                <i class="bi bi-lock-fill text-gray-500 ms-3"></i>
            @endif

            <span class="float-end text-right font-half">
                {{ $note->created_at->diffForHumans() }}<br>{{ $note->updated_at->diffForHumans() }}
            </span>
            <span class="float-end mx-3">{{ $note->user->name }}</span>
        </h2>
    </x-slot>

    <div class="container">
        <div id="NoteItem">
            <form action="{{ route('notes.update', $note->uid) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <input type="hidden" name="id" value="{{ $note->id }}" required>

                <div class="bg-white mb-3 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-3">
                            <label for="NoteEditTitle" class="form-label">Title</label>
                            <input id="NoteEditTitle" type="text" name="title" value="{{ $note->title }}" class="form-control form-control-lg" placeholder="Title" required>
                        </div>

                        <div class="mb-3">
                            <label for="NoteEditText" class="form-label">Text</label>
                            <textarea id="NoteEditText" name="text" class="form-control form-control-lg" rows="8" required>{{ $note->text }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="NoteEditAttachment" class="form-label">Attach files</label>
                            <input id="NoteEditAttachment" class="form-control form-control-lg" type="file" name="attachment[]" multiple>
                        </div>

                        <div class="form-check mb-3">
                            <input id="NoteEditPrivate" type="checkbox" name="private" class="form-check-input" value="1" {{ $note->private ? 'checked' : '' }}>
                            <label class="form-check-label" for="NoteEditPrivate">
                                Private note
                            </label>
                        </div>

                        <div class="mb-3 text-right px-0">
                            <a class="btn btn-lg btn-link" href="{{ route('notes.show', $note->uid) }}">Cancel</a>
                            <button type="submit" class="btn btn-lg btn-primary">Save</button>
                        </div>

                        @if($note->hasAttachments())
                        <hr class="my-5">

                        <div class="mb-3">
                            <h5 class="form-label">Attachments</h5>
                        </div>

                        @foreach ($note->attachments as $file)
                        <span id="NoteAttachmentItem{{ $file->id }}" class="badge bg-light shadow-sm text-secondary cursor-pointer p-3 me-1 mb-1">
                            <i class="bi bi-paperclip me-3"></i>
                            {{ $file->original }}
                            <i class="bi bi-x-circle cursor-pointer text-danger ms-3" onclick="deleteNoteAttachment('{{ $note->uid }}', {{ $file->id }}, '{{ $file->_name }}')"></i>
                        </span>
                        @endforeach
                        @endif

                    </div>
                </div>

                @if($note->hasShared())
                <div class="bg-white mb-3 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">

                        <div class="mb-3">
                            <h5 class="form-label">Shared with</h5>
                        </div>

                        <div class="mb-3">
                            @foreach ($note->shared as $sharing)
                                <span id="NoteSharingItem{{ $sharing->id }}" class="badge bg-light shadow-sm text-secondary p-3 me-1 mb-1" >
                                    {{ $sharing->user->name }} <i class="bi bi-x-circle cursor-pointer text-danger ms-3" onclick="unshareNote('{{ $note->uid }}', {{ $sharing->id }}, {{ $sharing->user_id }}, '{{ $sharing->user->name }}')"></i>
                                </span>
                            @endforeach
                        </div>

                    </div>
                </div>
                @endempty

            </form>
        </div>
    </div>

</x-app-layout>
