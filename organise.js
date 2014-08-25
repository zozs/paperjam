$(document).ready(function() {
  $('#organise-accordion').accordion({
    heightStyle: 'content'
  });
  
  $('#date').datepicker();
  $('#date').datepicker('option', 'dateFormat', 'yy-mm-dd');
  $('#date').datepicker('setDate', new Date());

  $('.status-complete').click(function() {
    location.reload();
  });

  $('#organise-order').sortable();
  $('#organise-order').disableSelection();

  $('#dialog-confirm').dialog({
    autoOpen: false,
    resizable: false,
    modal: true,
    buttons: {
      'Delete pages': function() {
        delete_pages();
        $(this).dialog('close');
      },
      'Cancel': function() {
        $(this).dialog('close');
      }
    }
  });

  $('#tag-form').submit(function(event) {
    var tag = $('<li>').addClass('tag').text($('#tag').val());
    tag.click(function() {
      tag.remove();
    });
    $('#organise-tags').append(tag);

    $('#tag').val('');
    return false;
  });

  $('#create-document').click(create_document);
  $('#delete-pages').click(function() {
    $('#dialog-confirm').dialog('open');
  });

  load_senders();
  load_tags();
  load_unorganised();
});

function create_document() {
  var pages = selected_pages();
  var tags = $('#organise-tags li')
    .map(function() {
      return $(this).text();
    }).get();
  var sender = $('#sender').val();
  var date = $('#date').val();

  $.ajax({
    url: 'documents',
    type: 'POST',
    timeout: 5000,
    contentType: 'application/json',
    data: JSON.stringify({
      pages: pages,
      tags: tags,
      sender: sender,
      date: date
    })      
  }).done(function() {
    /* Alles gut. */
    $('.status-failed').hide();
    $('.status-complete').show();
    $('#organise-accordion').hide();
  }).fail(function() {
    /* Failed. */
    $('.status-failed').show();
  });
}

function delete_pages() {
  var pages = selected_pages();
  var requests = $.map(pages, function(p) {
    return $.ajax({
        url: 'pages/' + p,
        type: 'delete',
        dataType: 'json',
        timeout: 5000
      }).fail(function() {
        /* Handle failure. Care must be taken since this may be launched
         * multiple times since deletion is done in parallel for each page. */
        alert('Failed to delete page with id ' + p);
      });
  });
  $.when.apply($, requests).then(function() {
    /* Reload pages. */
    load_unorganised();
  });
}

function file_img(file) {
  return $('<img>', {
    src: file_img_url(file),
    alt: file
  });
}

function file_img_url(file) {
  return 'files/' + file;
}

function load_senders() {
  $.ajax({
    url: 'senders',
    dataType: 'json',
    timeout: 5000
  }).done(function(data) {
    $('#sender').autocomplete({
      source: data.senders
    });
  });
}

function load_tags() {
  $.ajax({
    url: 'tags',
    dataType: 'json',
    timeout: 5000
  }).done(function(data) {
    $('#tag').autocomplete({
      source: data.tags
    });
  });
}

function load_unorganised() {
  $.ajax({
    url: 'unorganised',
    dataType: 'json',
    timeout: 5000
  }).done(function(data) {
    $('#unorganised-pages').empty();
    $('#organise-order').empty();
    $.each(data.unorganised, function(i, page) {
      var p = $('<div>').addClass('page')
        .append($('<a>', {
          href: file_img_url(page.file),
          target: '_blank'
        })
          .append(file_img(page.file)))
        .append($('<label>')
          .text(page.file)
          .append($('<input>', {
            type: 'checkbox'
          })
            .data('file', page.file)
            .data('id', page.id)
            .change(function() {
              var data = $(this).data();
              if ($(this).is(':checked')) {
                $(this).parents('.page').addClass('page-selected');
                var row = $('<tr>')
                  .data(data)
                  .attr('id', 'organise-order-row-' + data.id)
                  .append($('<td>')
                    .append(file_img(data.file)))
                  .append($('<td>').text(data.file));
                  
                $('#organise-order').append(row);
              } else {
                $(this).parents('.page').removeClass('page-selected');
                $('#organise-order tr').filter(function() {
                  return $(this).data('id') == data.id;
                }).remove();
              }
            })          
          )
        );
      $('#unorganised-pages').append(p);
    });
  }).fail(function() {
    alert("Failed to get unorganised pages.");
  });
}

function selected_pages() {
  return $('#organise-order tr')
    .map(function() {
      return $(this).data('id');
    }).get();
}