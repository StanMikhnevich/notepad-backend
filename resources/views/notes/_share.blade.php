<div class="modal fade" id="NoteShareModal" tabindex="-1" aria-labelledby="NoteShareModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <form action="{{ route('notes.share') }}" method="POST">
                @csrf
                <input type="hidden" name="note_id" value="{{ $note->id }}">

                <div class="modal-header">
                    <h5 class="modal-title">Share Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label for="NoteShareModalEmail" class="form-label">Email</label>
                        <input id="NoteShareModalEmail" type="email" name="email" class="form-control" placeholder="Email" required>

                        <div id="NoteShareModalEmailUser" class="text-secondary p-2 text-end"></div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Share</button>
                </div>

            </form>

        </div>
    </div>
</div>
