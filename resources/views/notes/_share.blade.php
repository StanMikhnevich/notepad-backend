<div id="NoteShareModal" class="fixed pin z-10 inset-0 overflow-y-auto modal hidden" aria-labelledby="NoteShareModal" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

        <div class="modal-close fixed inset-0 bg-black bg-opacity-50 transition-opacity cursor-pointer" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:align-middle mx-6 mx-auto w-1/4 z-20 m-8 p-5">
            <div class="bg-white">
                <div class="flex justify-between modal-header mb-5">
                    <h2 class="font-semibold text-xl">Share note</h2>
                    <i class="mdi mdi-close cursor-pointer modal-close"></i>
                </div>

                <form action="{{ route('notes.index') . '/' . $note->uid }}/share" method="POST">
                    @csrf

                    <input type="hidden" name="note_id" value="{{ $note->id }}">

                    <div class="mb-5">
                        <label for="NoteShareModalEmail"> Email
                            <input id="NoteShareModalEmail" type="text" name="email" placeholder="Email"
                                   class="mt-2 p-4 w-full block rounded-md border border-gray-300 focus_o-300 focus:ring focus:ring-gray-300 focus:ring-opacity-50"
                                   required>
                        </label>
                        <div id="NoteShareModalEmailUser" class="text-secondary p-2 text-end"></div>
                    </div>

                    <div class="text-right space-x-4">
                        <button type="button" class="px-4 py-2 text-center modal-close">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-purple-300 hover:bg-purple-400 rounded-lg text-center shadow-md transition ease-in-out duration-150"><i class="mdi mdi-share mr-4"></i> Share</button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>
