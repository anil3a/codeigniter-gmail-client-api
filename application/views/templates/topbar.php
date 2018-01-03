<br>
 <!-- Static navbar -->
      <nav class="navbar navbar-default">
        <div class="container-fluid">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">G Proz</a>
          </div>
          <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
              <li class="menu0 <?php if( !empty( $menu )){  if( !empty( $menu[0] ) ) { echo 'active'; } } else { echo 'active'; } ?>"><a href="/">Home</a></li>
              <li class="menu1 <?php if( !empty( $menu ) && !empty( $menu[1] ) ) { echo 'active'; } ?>"><a href="/google/emails">Gmail Emails</a></li>
              <li class="menu2 <?php if( !empty( $menu ) && !empty( $menu[2] ) ) { echo 'active'; } ?>"><a href="/google/settings">Gmail Settings</a></li>
            </ul>
           
          </div><!--/.nav-collapse -->
        </div><!--/.container-fluid -->
      </nav>