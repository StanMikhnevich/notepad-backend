<div class="modal fade" id="NoteCreateModal" tabindex="-1" aria-labelledby="NoteCreateModal" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <form action="{{ route('notes.create') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Create Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label for="NoteCreateModalTitle" class="form-label">Title</label>
                        <input id="NoteCreateModalTitle" type="text" name="title" class="form-control" placeholder="Title" required>
                    </div>

                    <div class="mb-3">
                        <label for="NoteCreateModalText" class="form-label">Text</label>
                        <textarea id="NoteCreateModalText" name="text" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="NoteCreateModalAttachment" class="form-label">Attach files</label>
                        <input id="NoteCreateModalAttachment" class="form-control" type="file" name="attachment[]" multiple>
                    </div>

                    <div class="form-check">
                        <input id="NoteCreateModalPrivate" type="checkbox" name="private" class="form-check-input" value="1" >
                        <label class="form-check-label" for="NoteCreateModalPrivate">
                            Private note
                        </label>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>

            </form>

        </div>
    </div>
</div>
