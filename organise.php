<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="paperjam.css" />
    <link rel="icon" href="images/favicon.png" />
    <title>Paperjam - organise</title>


    <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/smoothness/jquery-ui.css" />
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/jquery-ui.min.js"></script>
  </head>

  <body>
    <?php $navbarCurrent = basename(__FILE__); require('navbar.php'); ?>

    <div class="container">
      <div id="organise-accordion">
        <h3>Select pages</h3>
        <div>
          <p>Select the pages you want to staple together to form a document.</p>

          <div id="unorganised-pages">
            <!--
            <div class="page">
              <a href="images/open-iconic/svg/document.svg" target="_blank">
                <img src="images/open-iconic/svg/document.svg" alt="Document" />
              </a>
              <label>
                Text
                <input type="checkbox" data-file="document.svg" data-id="0" />
              </label>
            </div>
            -->
          </div>
          <div class="clearfix"></div>
          <p>
            Proceed to the next section to proceed with creating a document, or <button type="button" id="delete-pages">Delete the selected pages</button>
          </p>
        </div>
        
        <h3>Order</h3>
        <div>
          <table class="organise-order-box">
            <tbody id="organise-order">
              <!--
              <tr>
                <td><img src="images/open-iconic/svg/document.svg" alt="Document" /></td>
                <td>Text</td>
              </tr>
              -->
            </tbody>
          </table>
          
        </div>

        <h3>Sender, date, tags</h3>
        <div class="organise-form">
          <label>
            Sender
            <input id="sender" />
          </label>

          <label>
            Date
            <input id="date" />
          </label>
          
          <form id="tag-form">
            <label>
              Tag
              <input id="tag" />
            </label>
          </form>

          <ul id="organise-tags" class="tag-list remove-tag-list">
          </ul>

          <button type="button" id="create-document">Create document</button>

          <div id="related-tags">
            <h4>Related tags</h4>
            <ul class="tag-list add-tag-list">
            </ul>
          </div>
        </div>
      </div>

      <div class="status-complete">
        <p>Document created sucessfully! Click to organise a new one.</p>
      </div>

      <div class="status-failed">
        <p>Failed to create a new document!</p>
      </div>
    </div>

    <!-- DIALOGS -->
    <div id="dialog-confirm" title="Delete selected pages?">
      <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        The selected pages will be permanently deleted from both disk and database,
        and can not be recovered! Are you sure?
      </p>
    </div>

    <!-- scripts and stuff -->
    <script src="bower_components/angular/angular.min.js"></script>
    <script src="bower_components/angular-bootstrap/ui-bootstrap-tpls.min.js"></script>
    <script src="paperjam.js"></script>
    <script src="organise.js"></script>
  </body>
</html>
