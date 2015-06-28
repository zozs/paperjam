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
      <div class="container">
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
            /* organise is a special case because of the badge. */
?>
<?php if ($navbarCurrent === "organise.php"): ?>
            <li class="active">
<?php else: ?>
            <li>
<?php endif; ?>
              <a href="organise.php" ng-controller="UnorganisedCtrl">
                Organise <span class="badge" ng-show="unorganisedData.unorganised.length > 0" ng-cloak>
                  {{ unorganisedData.unorganised.length }}
                </span>
              </a>
            </li>
          </ul>
        </div><!-- /.navbar-collapse -->
      </div><!-- /.container-fluid -->
    </nav>
    
    <!-- ALERTS -->
    <div class="container" ng-controller="AlertCtrl">
      <alert ng-repeat="alert in alerts" type="{{alert.type}}" close="closeAlert($index)" ng-cloak>{{alert.msg}}</alert>
    </div>

