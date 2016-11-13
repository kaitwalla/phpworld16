<!DOCTYPE html>
<html lang="en">
<head>

<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<meta charset="<?php bloginfo( 'charset' ); ?>">

<?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>
<div id="header_background">

</div>
<nav class="navbar">
  <div class="container-fluid">
    <h1>
        <a href="<?php print get_site_url(); ?>">PHPWorld | When to use WP-API</a>
    </h1>
  </div>
</nav>
<main class="container-fluid">
    <div class="col-xs-12 text-center">
	    <?php wp_nav_menu(array('theme_location' => 'main_menu') ); ?>
    </div>
  