require('./bootstrap');

require('alpinejs');


$(document).ready(function() {

    if($('#NotesListAll').length) getAllNotes()

    if($('#NotesListMy').length) getMyNotes()

    if($('#NotesListShared').length) getSharedNotes()

    $('#NoteShareModalEmail').on('keyup keypress change mouseout', function() {
        const email = $(this).val()

        if(email.length <= 5) {
            return ;
        }

        // Check user by email
        $.ajax({
            url: '/api/checkUserByEmail',
            type: 'POST',
            data: {email},
            async: true,
            headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')},
            dataType: 'JSON',
            success: (res) => {
                if(!res.success) {
                    $(this).next().text(res.msg)
                    $(this).closest('form').find('button[type=submit]').attr('disabled', true)
                    return ;
                }

                $(this).closest('form').find('button[type=submit]').attr('disabled', false)
                $(this).next().html('<i class="bi bi-person-check text-success"></i> ' + res.user.name)
            }
        })


    })

})



const fetchParams = {
    method: "POST",
    credentials: 'same-origin',
    headers: {
        "Accept": "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
    }
}


async function getAllNotes() {
    $('#NotesListAll').html('')

    let response = await fetch('/api/getAllNotes', fetchParams).then((res) => res.json()).then(notes => {
        notes.forEach((note) => {
            const isPrivate = (note.private)
                ? '<span class="ml-3 badge rounded-pill bg-dark text-light">Private</span>'
                : ''

                const createdAt = new Date(note.created_at)

            $('#NotesListAll').append(
'<div class="bg-white mb-3 shadow-sm rounded-3">' +
    '<div class="p-6">' +
        '<a class="text-decoration-none" href="/note/' + note.id + '"><strong>' + note.title + '</strong></a>' +
        '<span class="float-end text-secondary">' + createdAt.toLocaleString() + '</span>' +
        '<span class="float-end mx-3">' + note._author.name + '</span>' +
        isPrivate +
     '</div>' +
'</div>'
            )
        })
    })
}

async function getMyNotes() {
    $('#NotesListMy').html('')

    await fetch('/api/getMyNotes', fetchParams).then((res) => res.json()).then(notes => {
        notes.forEach((note) => {
            const isPrivate = (note.private)
                ? '<span class="ml-3 badge rounded-pill bg-dark text-light">Private</span>'
                : ''

                const createdAt = new Date(note.created_at)

            $('#NotesListMy').append(
'<div class="bg-white mb-3 shadow-sm rounded-3">' +
    '<div class="p-6">' +
        '<a class="text-decoration-none" href="/note/' + note.id + '"><strong>' + note.title + '</strong></a>' +
        '<span class="float-end text-secondary">' + createdAt.toLocaleString() + '</span>' +
        '<span class="float-end mx-3">' + note._author.name + '</span>' +
        isPrivate +
     '</div>' +
'</div>'
            )
        })
    })
}

async function getSharedNotes() {
    $('#NotesListShared').html('')

    let response = await fetch('/api/getSharedNotes', fetchParams).then((res) => res.json()).then(shared => {
        shared.forEach((share) => {
            const isPrivate = (share.note.private)
                ? '<span class="ml-3 badge rounded-pill bg-dark text-light">Private</span>'
                : ''

                const createdAt = new Date(share.note.created_at)

            $('#NotesListShared').append(
'<div class="bg-white mb-3 shadow-sm rounded-3">' +
    '<div class="p-6">' +
        '<a class="text-decoration-none" href="/note/' + share.note.id + '"><strong>' + share.note.title + '</strong></a>' +
        '<span class="float-end text-secondary">' + createdAt.toLocaleString() + '</span>' +
        '<span class="float-end mx-3">' + share.note._author.name + '</span>' +
        isPrivate +
     '</div>' +
'</div>'
            )
        })
    })
}

window.unshareNote = async function(sharing_id, note_id, user_id) {

    if (confirm('Stop sharing the note with this user ?')) {
        $.ajax({
            url: '/api/unshareNote',
            type: 'POST',
            data: {note_id, user_id},
            async: true,
            headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')},
            dataType: 'JSON',
            success: (res) => {
                if(res.success) {
                    $('#NoteSharingItem' + sharing_id).remove()
                }
            }
        })
    }

}

window.deleteNoteAttachment = async function(file_id, file_name) {

    if (confirm('Delete ' + file_name + ' from note attachments ?')) {
        $.ajax({
            url: '/api/deleteNoteAttachment',
            type: 'POST',
            data: {file_id},
            async: true,
            headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')},
            dataType: 'JSON',
            success: (res) => {
                if(res.success) {
                    $('#NoteAttachmentItem' + file_id).remove()
                }
            }
        })
    }

}
