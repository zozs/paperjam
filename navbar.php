<?php
function printMenuItem($filename, $text) {
  global $navbarCurrent;
  if ($navbarCurrent === $filename) {
    echo '<li class="active">';
  } else {
    echo '<li>';
  }
  echo '<a href="' . $filename . '">' . $text . '</a></li>';
}
?>
    <nav class="navbar navbar-default">
      <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" ng-init="isCollapsed = true" ng-click="isCollapsed = !isCollapsed">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">PaperJam</a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" ng-class="{collapse: isCollapsed}">
          <ul class="nav navbar-nav">
<?php
            printMenuItem("list.php", "List");
            printMenuItem("find.php", "Find");
            printMenuItem("add.php", "Add");
            printMenuItem("organise.php", "Organise");
?>
          </ul>
          <p ng-controller="UnorganisedCtrl" class="navbar-text navbar-right">
            <a href="organise.html" class="navbar-link">
              <ng-pluralize count="unorganised.length"
                   when="{'0': 'You have no unorganised pages.',
                          'one': 'You have 1 unorganised page.',
                          'other': 'You have {} unorganised pages.'}">
              </ng-pluralize>
            </a>
          </p>
        </div><!-- /.navbar-collapse -->
      </div><!-- /.container-fluid -->
    </nav>
