<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between text-xl py-8">
            <div class="flex space-x-4 justify-between py-2">
                <h2 class="font-semibold text-2xl leading-10 text-gray-500">{{ $note->title }} ⟶ Edit</h2>

                @if($note->private)
                    <i class="mdi mdi-lock text-gray-500 my-1"></i>
                @endif

            </div>

            <div class="flex space-x-4 py-2">
                <span>{{ $note->user_id == auth()->id() ? 'You' : $note->user->name }}</span>

                <span class="float-end ms-3 text-secondary text-right text-xl font-half">
                    {{ $note->created_at->diffForHumans() }}<br>{{ $note->updated_at->diffForHumans() }}
                </span>
            </div>

        </div>

    </x-slot>

    <div id="NoteItem" class="mb-20">
        <div class="bg-white mb-10 p-6 shadow-md rounded-lg note-item">
            <div class="note-text">
                <form action="{{ route('notes.update', $note->uid) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="id" value="{{ $note->id }}" required>


                    <div class="mb-5">
                        <label for="NoteEditModalTitle"> Title
                            <input id="NoteEditModalTitle" type="text" name="title" placeholder="Note title" value="{{ $note->title }}"
                                   class="mt-2 p-4 w-full block rounded-md border border-gray-300 focus_o-300 focus:ring focus:ring-gray-300 focus:ring-opacity-50"
                                   required>
                        </label>
                    </div>

                    <div class="mb-5">
                        <label for="NoteEditModalText"> Text
                            <textarea id="NoteEditModalText" name="text" rows="8" placeholder="Note text"
                                      class="mt-2 p-4 w-full block rounded-md border border-gray-300 focus_o-300 focus:ring focus:ring-gray-300 focus:ring-opacity-50"
                                      required>{{ $note->text }}</textarea>
                        </label>
                    </div>

                    <div class="mb-5">
                        <label for="NoteEditModalAttachment"> Attachments
                            <input id="NoteEditModalAttachment" type="file" name="attachment[]" accept="text/*,image/*,audio/*,video/*"
                                   class="mt-2 p-4 w-full block rounded-md border border-gray-300 focus_o-300 focus:ring focus:ring-gray-300 focus:ring-opacity-50"
                                   multiple>
                        </label>
                    </div>

                    <div class="mb-5">
                        <label for="NoteEditModalPrivate" class="inline-flex items-center select-none">
                            <input id="NoteEditModalPrivate" type="checkbox" name="private" value="1" {{ $note->private ? 'checked' : '' }}
                                   class="p-4 block rounded-md border border-gray-300 focus_o-300">
                            <span class="ml-2">Private</span>
                        </label>
                    </div>

                    <div class="text-right space-x-4">
                        <a href="{{ route('notes.index') }}/{{ $note->uid }}" class="px-4 py-3 text-center"><i class="mdi mdi-arrow-left mr-4"></i> Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-purple-300 hover:bg-purple-400 rounded-lg text-center shadow-md transition ease-in-out duration-150"><i class="mdi mdi-content-save mr-4"></i> Save</button>
                    </div>
                </form>

                @if($note->hasAttachments())
                    <div class="note-attachments text-gray-500">

                        <hr class="my-8">
                        <h5 class="font-semibold mb-4">Attachments</h5>
                        <div id="NoteItemAttachments" class="flex space-x-4">
                            @foreach ($note->attachments as $file)
                                <span id="NoteAttachmentItem{{ $file->id }}"
                                   class="fancybox iframe bg-gray-100 px-4 py-2 rounded-full shadow no-underline font-medium text-gray-500">
                                    <i class="mdi mdi-attachment mr-2"></i>
                                    {{ $file->original }}
                                    <i class="mdi mdi-close-circle-outline cursor-pointer text-red-500 ml-2" onclick="deleteNoteAttachment('{{ $note->uid }}', {{ $file->id }}, '{{ $file->_name }}')"></i>
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if($note->hasShared() && ($note->user_id == (auth()->id() ?? '')))
        <div class="note-shares text-gray-500">
            <h5 class="font-semibold mb-4">Shares</h5>
            <div id="NoteItemUsers" class="flex space-x-4 text-gray-500 text-xs">
                @foreach ($note->shared as $sharing)
                    <span id="NoteSharingItem{{ $sharing->id }}" class="bg-white px-4 py-2 rounded-full shadow no-underline font-medium">
                        <i class="mdi mdi-account-outline mr-2"></i> {{ $sharing->user->name }}
                        <i class="mdi mdi-close-circle-outline cursor-pointer text-red-500 ml-2" onclick="unshareNote('{{ $note->uid }}', {{ $sharing->id }}, {{ $sharing->user_id }}, '{{ $sharing->user->name }}');"></i>
                    </span>
                @endforeach
            </div>
        </div>
        @endif

    </div>

</x-app-layout>
