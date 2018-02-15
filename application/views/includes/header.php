<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon">
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">

    <title><?=$ProjectName; ?></title>

    <!-- Bootstrap -->
    <link href="<?=base_url("assets/vendors/bootstrap/dist/css/bootstrap.min.css"); ?>" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="<?=base_url("assets/vendors/nprogress/nprogress.css"); ?>" rel="stylesheet">
    <!-- iCheck -->
    <link href="<?=base_url("assets/vendors/iCheck/skins/flat/green.css"); ?>" rel="stylesheet">
	
    <!-- bootstrap-progressbar -->
    <link href="<?=base_url("assets/vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css"); ?>" rel="stylesheet">
    <!-- JQVMap -->
    <link href="<?=base_url("assets/vendors/jqvmap/dist/jqvmap.min.css"); ?>" rel="stylesheet"/>
    <!-- bootstrap-daterangepicker -->
    <link href="<?=base_url("assets/vendors/bootstrap-daterangepicker/daterangepicker.css"); ?>" rel="stylesheet">

    <!-- Custom Theme Style -->
    <link href="<?=base_url("assets/css/custom.min.css"); ?>" rel="stylesheet">
  </head>

  <body class="nav-md" style="background: #2A3F54; height: 100% !important;">
    <div class="container body">
      <div class="main_container">
        <div class="col-md-3 left_col">
          <div class="left_col scroll-view">
            <div class="navbar nav_title" style="border: 0;">
              <a href="<?=site_url(); ?>" class="site_title"><i class="fa fa-envelope-o"></i> <span><?=$ProjectName; ?></span></a>
            </div>

            <div class="clearfix"></div>

            <!-- menu profile quick info -->
            <div class="profile clearfix">
              <div class="profile_pic">
                <img src="<?=base_url("assets/img/user.png"); ?>" alt="user avartar" class="img-circle profile_img">
              </div>
              <div class="profile_info">
                <span>Welcome,</span>
                <h2><?=$this->session->userdata("fullname"); ?></h2>
              </div>
            </div>
            <!-- /menu profile quick info -->

            <br />

            <!-- sidebar menu -->
            <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
              <div class="menu_section">
                <h3>General</h3>
                <ul class="nav side-menu">
                  <li><a href="<?=site_url("dashboard"); ?>"><i class="fa fa-dashboard"></i> Dashboard </a></li>
                  <li><a href="<?=site_url("home/transactionstatus"); ?>"><i class="fa fa-spinner "></i>Transaction Status</a></li>
                  <li><a href="<?=site_url("home/reversal"); ?>"><i class="fa fa-refresh"></i>Reversal</a></li>
                  <li><a href="<?=site_url("home/b2c"); ?>"><i class="fa  fa-money fa-pull-left"></i><i class="fa  fa-user fa-pull-left"></i>Send to Customer</a></li>
                    <li><a href="<?=site_url("home/b2b"); ?>"><i class="fa  fa-money fa-pull-left"></i><i class="fa fa-institution fa-pull-left"></i>Send to Business</a></li>
                  <li class="clearfix"><a><i class="fa fa-table"></i> Reports <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      
                      <li><a href="<?=site_url("home/incoming"); ?>"><i class="fa fa-inbox-in"></i>Incoming</a></li>
                      <li><a href="<?=site_url("home/outgoing"); ?>"><i class="fa fa-inbox-out"></i>Outgoing</a></li>
                    </ul>
                  </li>
                  
                  <li><a><i class="fa fa-gears"></i> Administrator <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="<?=site_url("users"); ?>">Users</a></li>
                      
                      
                    </ul>
                  </li>
                </ul>
              </div>
             
            </div>
            <!-- /sidebar menu -->

            <!-- /menu footer buttons -->
            <div class="sidebar-footer hidden-small">
              <a data-toggle="tooltip" data-placement="top" title="Dashboard" href="<?=site_url("dashboard"); ?>">
                <span class="fa fa-dashboard" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="Incoming sms" href="<?=site_url("incomingsms"); ?>">
                <span class="fa fa-envelope aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="profile" href="<?=site_url("profile"); ?>">
                <span class="fa fa-user" aria-hidden="true"></span>
              </a> 
              
              <a data-toggle="tooltip" data-placement="top" title="Logout" href="<?=site_url("logout"); ?>">
                <span class="fa fa-sign-out" aria-hidden="true"></span>
              </a>
            </div>
            <!-- /menu footer buttons -->
          </div>
        </div>

        <!-- top navigation -->
        <div class="top_nav">
          <div class="nav_menu">
            <nav>
              <div class="nav toggle">
                <a id="menu_toggle"><i class="fa fa-bars"></i></a>
              </div>

              <ul class="nav navbar-nav navbar-right">
                <li class="">
                  <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <img src="<?=base_url("assets/img/user.png"); ?>" alt="User avartar"><?=$this->session->userdata("fullname"); ?>
                    <span class=" fa fa-angle-down"></span>
                  </a>
                  <ul class="dropdown-menu dropdown-usermenu pull-right">
                    <li><a href="javascript:;"> Profile</a></li>
                    <li><a href="<?=site_url("logout"); ?>"><i class="fa fa-sign-out pull-right"></i> Log Out</a></li>
                  </ul>
                </li>
              </ul>
            </nav>
          </div>
        </div>
        <!-- /top navigation -->