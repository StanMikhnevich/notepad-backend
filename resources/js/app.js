require('./bootstrap');

require('alpinejs');


$(document).ready(function() {

    // Проверка наличия юзера по email
    // Минимум 5 симолов для запросов
    $('#NoteShareModalEmail').on('keyup keypress change mouseout', function() {
        const email = $(this).val()

        if(email.length <= 5) {
            return ;
        }

        // Запрос на поиск юзера по email
        $.ajax({
            url: '/api/checkUserByEmail',
            type: 'POST',
            data: {email},
            async: true,
            headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')},
            dataType: 'JSON',
            success: (res) => {

                // Если пользователь найден, кнопка submit разблокируется
                if(res.success) {
                    // Снятие блока с кнопки
                    $(this).closest('form').find('button[type=submit]').attr('disabled', false)

                    // Вывод имени пользователя
                    $(this).next().html('<i class="bi bi-person-check text-success"></i> ' + res.user.name)

                    return ;
                }

                // Блокировка кнопки
                $(this).closest('form').find('button[type=submit]').attr('disabled', true)

                // Вывод сообщения о том, что пользователь не найден
                $(this).next().text(res.msg)

            }
        })


    })

})

// Прекращение доступа юзеру к записки
// Глобальная функция. Чтобы использовать сразу из шаблона
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

// Удаление прикрелённого к записки файла
// Глобальная функция. Чтобы использовать сразу из шаблона
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
