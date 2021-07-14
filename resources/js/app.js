require('./bootstrap');

require('alpinejs');


$(document).ready(function() {

    if($('#NotesList')) getNotes()

})

const fetchParams = {
    method: "POST",
    credentials: 'same-origin',
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
    }
}


async function getNotes() {
    $('#NotesList').html('')

    let response = await fetch('/api/notes', fetchParams).then((res) => res.json()).then(notes => {
        notes.forEach((note) => {
            console.log(note);
            const isPrivate = (note.private)
                ? '<span class="ml-3 badge rounded-pill bg-dark text-light">Private</span>'
                : ''

            $('#NotesList').append(
'<div class="bg-white mb-3 shadow-sm rounded-3">' +
    '<div class="p-6">' +
        '<a href="/note/' + note.id + '">' + note.title + '</a>' +
        isPrivate +
     '</div>' +
'</div>'
            )
        })
    })
    // response = response.json()
    // console.log(response);
    // return await response.json()
}
