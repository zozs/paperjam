$(document).ready(function() {
  $.ajax({
    url: 'unorganised',
    dataType: 'json',
    timeout: 25000
  }).done(function(data) {
    if (data.unorganised.length > 0) {
      $('#unorganised-notification').show();
      if (data.unorganised.length > 1) {
        var t = $('<p>').text('You have ' + data.unorganised.length +
          ' unorganised pages.');
      } else {
        var t = $('<p>').text('You have ' + data.unorganised.length +
          ' unorganised page.');
      }
      $('#unorganised-notification').append(t);
    }
  }).fail(function(data) {
    $('#unorganised-notification')
      .text('Failed to get unorganised count!')
      .show();
  });
});
