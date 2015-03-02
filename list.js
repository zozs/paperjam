$(document).ready(function() {
  $.ajax({
    url: 'documents',
    dataType: 'json',
    timeout: 25000
  }).done(function(data) {
    $.each(data.documents, function(i, d) {
      var row = $('<tr>');
      row.append(
        $('<td>')
          .append(
            $('<img>', {
              src: 'images/open-iconic/svg/document.svg',
              alt: 'Document'
            })));
      row.append($('<td>').text(d.date));
      row.append($('<td>').text(d.sender));
      row.append($('<td>').text(d.pages.length));
      var tag_cell = $('<td>').appendTo(row);
      $.each(d.tags, function(j, t) {
        tag_cell.append($('<span>').addClass('tag').text(t));
      });
      
      row.click(function() {
        window.location.href = 'view.php?id=' + d.id;
      });

      $('.document-list tbody').append(row);
    });
  }).fail(function() {
    
  });
});
