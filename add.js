$(document).ready(function() {
    $('#upload-button').click(function() {
        var formData = new FormData($('form')[0]);
        /* If we want individual progress for each file, we'll have to fire
           one AJAX request per file, let's do that sometime in the future. */
        $('#upload-status progress').show();
        $('#upload-status p').empty();
        $('#upload-status-complete').hide();
        $.ajax({
            url: 'api/add_page.php',
            type: 'POST',
            xhr: function() {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    myXhr.upload.addEventListener('progress', upload_progress,
                        false);
                }
                return myXhr;
            },
            success: upload_complete,
            error: upload_failed,
            data: formData,
            cache: false,
            contentType: false,
            processData: false
        });
    });

    $('#add-more-button').click(add_file_field);
});

function add_file_field() {
    var file_row = $('<div/>').addClass('upload-row');
    var file_control = $('<input/>', {
        type: 'file',
        name: 'file[]',
        'multiple': 'multiple'
    });
    var file_remove  = $('<input/>', {
        type: 'button',
        value: '-',
        click: function() {
            // Deletes this file form the form.
            file_control.remove();
            $(this).remove();
        }
    });
    file_row.append(file_control).append(file_remove);
    $('form').append(file_row);
}

function upload_complete() {
    $('#upload-status progress').hide();
    $('#upload-status-complete').show();
}

function upload_failed(e, textStatus) {
    $('#upload-status progress').hide();
    $('#upload-status-complete').hide();
    $('#upload-status p').text('Upload failed! ' + textStatus);
}

function upload_progress(e) {
    if (e.lengthComputable) {
        $('#upload-status progress').attr({value: e.loaded, max: e.total});
    }
}
