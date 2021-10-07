<div id="NoteCreateModal" class="fixed pin z-10 inset-0 overflow-y-auto modal hidden" aria-labelledby="NoteCreateModal" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

        <div class="modal-close fixed inset-0 bg-black bg-opacity-50 transition-opacity cursor-pointer" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:align-middle mx-6 mx-auto w-1/2 z-20 m-8 p-5">
            <div class="bg-white">
                <div class="flex justify-between modal-header mb-5">
                    <h2 class="font-semibold text-xl">New note</h2>
                    <i class="mdi mdi-close cursor-pointer modal-close"></i>
                </div>

                <form action="{{ route('notes.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-5">
                        <label for="NoteCreateModalTitle"> Title
                            <input id="NoteCreateModalTitle" type="text" name="title" placeholder="Note title" value="{{ old('title') }}"
                                   class="mt-2 p-4 w-full block rounded-md border border-gray-300 focus_o-300 focus:ring focus:ring-gray-300 focus:ring-opacity-50"
                                   required>
                        </label>
                    </div>

                    <div class="mb-5">
                        <label for="NoteCreateModalText"> Text
                            <textarea id="NoteCreateModalText" name="text" rows="8" placeholder="Note text"
                                      class="mt-2 p-4 w-full block rounded-md border border-gray-300 focus_o-300 focus:ring focus:ring-gray-300 focus:ring-opacity-50"
                                   required>{{ old('text') }}</textarea>
                        </label>
                    </div>

                    <div class="mb-5">
                        <label for="NoteCreateModalAttachment"> Attachments
                            <input id="NoteCreateModalAttachment" type="file" name="attachment[]" accept="text/*,image/*,audio/*,video/*"
                                   class="mt-2 p-4 w-full block rounded-md border border-gray-300 focus_o-300 focus:ring focus:ring-gray-300 focus:ring-opacity-50"
                                   multiple>
                        </label>
                    </div>

                    <div class="mb-5">
                        <label for="NoteCreateModalPrivate" class="inline-flex items-center select-none">
                            <input id="NoteCreateModalPrivate" type="checkbox" name="private" value="1" {{ old('private') ? 'checked' : '' }}
                                   class="p-4 block rounded-md border border-gray-300 focus_o-300">
                            <span class="ml-2">Private</span>
                        </label>
                    </div>

                    <div class="text-right space-x-4">
                        <button type="button" class="px-4 py-2 text-center modal-close">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-purple-300 hover:bg-purple-400 rounded-lg text-center shadow-md transition ease-in-out duration-150"><i class="mdi mdi-plus mr-4"></i> Create</button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>
