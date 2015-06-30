# TODO

## Functionality

- Organise scanned documents.
- Every *document* consists of one or several *pages*. When creating a document
  the user chooses the appropriate pages and *staple* them together to form a
  document.
- Every document has a *date*, a *sender*, and zero or more *tags*.
- Watch for new mails on e.g. paperjam@apps.zozs.se, and add all attachments of
  .jpg or .pdf to the list of non-organised scanned pages. See e.g.
  http://unix.stackexchange.com/q/53047

## Views

### Front page

- Should contain links to Add, Find, and List views.
- May contain a notification bar which could say
  "You have 5 scanned pages which are not yet organised. Do it now?"
  which links to the organise view described below.

### Add

- The add page simply uploads pages to the server for later organising.
- When files have been uploaded, they are regarded as non-organised files,
  and the user is offered to organise them immediately with a link.

### Organise

- Should be able to add staple multiple pages together to form a document.
- Perhaps we should also merge them together to a multi-pdf at this stage?
  or should we simply store them as the original jpg on disk (safer).
  We could then create a multi-page pdf on demand. If this is time-consuming,
  the resulting pdf could of course be cached.
- Should be able to select date using some nice control.
- Also add tags, using a text control with auto completion that shows current
  tags in database.
- When selecting sender, also show auto-complete list of previous senders.

### Find

- When finding documents, the system should accept a search string. This string
  will be matched against date, sender, and tags to find matches.

### List

- The list should show all stored documents in a list, ordered in reverse
  chronological order. The list should show date, sender, tags, and number of
  pages in the document.
- When clicking a row in the list, details about the document should be shown.

### Document details

- Show thumbnails of every page (cache them, or calculate thumbnails
  when adding?)
- Allow download of a multi-page PDF with the same content, as well as download
  each individual page.
- Show pages directly in web browser by using some image viewer dialog that
  shows each page. (Next/Prev dialog)
