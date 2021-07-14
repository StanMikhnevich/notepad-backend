require('./bootstrap');

require('alpinejs');


$(document).ready(function() {

    if($('#NotesListAll')) getAllNotes()
    if($('#NotesListMy')) getMyNotes()

})

const fetchParams = {
    method: "POST",
    credentials: 'same-origin',
    headers: {
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
        '<span class="float-end mx-3">' + note.author.name + '</span>' +
        isPrivate +
     '</div>' +
'</div>'
            )
        })
    })
}

async function getMyNotes() {
    $('#NotesListMy').html('')

    let response = await fetch('/api/getMyNotes', fetchParams).then((res) => res.json()).then(notes => {
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
        '<span class="float-end mx-3">' + note.author.name + '</span>' +
        isPrivate +
     '</div>' +
'</div>'
            )
        })
    })
}
