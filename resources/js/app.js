require('./bootstrap');

require('alpinejs');


$(document).ready(function() {

    if($('.alert').length) {
        $('.alert').delay(2000).slideUp(500);
    }

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
/**
 * @param note_uid
 * @param sharing_id
 * @param user_id
 * @param user_name
 * @returns {Promise<void>}
 */
window.unshareNote = async function(note_uid, sharing_id, user_id, user_name) {
    if (confirm('Stop sharing this note with ' + user_name + ' ?')) {
        await axios.post('/notes/' + note_uid + '/unshareNote', {sharing_id}).then(function (res) {
            if(res.data.success) {
                $('#NoteSharingItem' + sharing_id).remove();
            }
        }).catch(function (error) {
            console.log(error);
        });
    }
}

/**
 * @param note_uid
 * @param attachment
 * @param file_name
 * @returns {Promise<void>}
 */
window.deleteNoteAttachment = async function(note_uid, attachment, file_name) {
    if (confirm('Delete ' + file_name + ' from note attachments ?')) {
        await axios.post('/notes/' + note_uid + '/deleteNoteAttachment', {attachment}).then(function (res) {
            if(res.data.success) {
                $('#NoteAttachmentItem' + attachment).remove()
            }
        }).catch(function (error) {
            console.log(error);
        });
    }
}

/**
 * @param note_uid
 * @param note_id
 * @param title
 * @returns {Promise<void>}
 */
window.deleteNote = async function(note_uid, note_id, title) {
    if (confirm('Delete ' + title + ' from notes ?')) {
        await axios.delete('/notes/' + note_uid).then(function (res) {
            if(res.data.success) {
                $('#NoteItem' + note_id).remove()
            }
        }).catch(function (error) {
            console.log(error);
        });
    }
}
