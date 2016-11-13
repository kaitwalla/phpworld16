<?php

require_once('includes/local-config.php');

//initial setup
function phpworld_setup() {
	add_option('voting_enabled',0);
	add_option('questions_enabled',0);
}

add_action( 'after_setup_theme', 'phpworld_setup' );


//API working on front end
function enqueue_wp_api() {
  wp_enqueue_script('wp-api');
}
add_action( 'wp_enqueue_scripts', 'enqueue_wp_api' );

//Basic styling
if ( !is_admin() ):    
  wp_enqueue_style('fonts','https://fonts.googleapis.com/css?family=Fjalla+One|PT+Sans+Narrow:400,700');
  wp_enqueue_style('bootstrap','https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
  wp_enqueue_style('bootstrap_theme','https://bootswatch.com/superhero/bootstrap.min.css');
  wp_enqueue_style('style',get_stylesheet_directory_uri().'/style.css',['fonts','bootstrap']);
  wp_enqueue_script('bootstrap','https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js',['jquery']);
  wp_enqueue_script('pusher','https://js.pusher.com/3.2/pusher.min.js',array('jquery'));
  wp_enqueue_script('cookies',get_stylesheet_directory_uri().'/js/cookie.js',array());  
  wp_enqueue_script('app',get_stylesheet_directory_uri().'/js/app.js',array('jquery','cookies'));
endif;

//Nav menu
register_nav_menu('main_menu','Main menu on front page');

function add_menuclass($ulclass) {
   return preg_replace('/<a /', '<a class="btn btn-warning"', $ulclass);
}

add_filter('wp_nav_menu','add_menuclass');

//Admin menu
add_action( 'init', 'phpworld_settings_init' );

function phpworld_settings_init() {
  // To register Options with the API, you have to use register_setting
  // More info: https://make.wordpress.org/core/2016/10/06/api-team-update-4-7-week-7/
  register_setting( 'phpworld', 'voting_enabled', array('show_in_rest'=>array('name'=>'voting_enabled','default'=>false)) );
  register_setting( 'phpworld', 'questions_enabled', array('show_in_rest'=>array('name'=>'questions_enabled','default'=>false)) );
}

// Disable sending new questions if questions_enabled option is set to false
function disable_save( $maybe_empty, $postarr ) {
    if (!get_option('questions_enabled'))
      $maybe_empty = true;

    return $maybe_empty;
}
add_filter( 'wp_insert_post_empty_content', 'disable_save', 999999, 2 );

// Send notification on successful post insert from front end
function send_pusher_notification_new_post( $post_id ) {
  global $pusher;
  global $pusher_channel; 
  
  if ( wp_is_post_revision( $post_id ) )
		return;
  
  $post = get_post($post_id);
  
  //Trashing a post also counts as a save
  if ($post->post_status == 'publish' && empty(get_post_meta($post_id,'votes'))):
    update_post_meta($post_id,'votes',1);
		$action = 'new_post';
    $data = $post_id;
    $pusher->trigger($pusher_channel, $action, $data);
  endif;
}
add_action( 'save_post', 'send_pusher_notification_new_post' );

//Add custom routes for voting
add_action( 'rest_api_init', function () {
	register_rest_route( 'phpworld/vote', '/up/(?P<id>\d+)', array(
		'methods' => 'GET',
		'callback' => 'upvote_post',
	) );
} );

add_action( 'rest_api_init', function () {
	register_rest_route( 'phpworld/vote', '/down/(?P<id>\d+)', array(
		'methods' => 'GET',
		'callback' => 'downvote_post',
	) );
} );

add_action( 'rest_api_init', function () {
	register_rest_route( 'phpworld/vote', '/current/(?P<id>\d+)', array(
		'methods' => 'GET',
		'callback' => 'getvote_post',
	) );
} );

function getvote_post($request_data) {
  $id = $request_data->get_params();  
  $current = (get_post_meta(   $id['id'], 'votes', true )) ? get_post_meta(   $id['id'], 'votes', true ) : 0;
  return $current;
}

function upvote_post($request_data) {
  global $pusher;
  global $pusher_channel;

  $id = $request_data->get_params();
  $current = (get_post_meta(   $id['id'], 'votes', true )) ? get_post_meta(   $id['id'], 'votes', true ) : 0;
  if (!get_option('voting_enabled')):
    return $current;
  else:
    $current = intval($current) + 1;
    update_post_meta(   $id['id'], 'votes', $current );
    $pusher->trigger($pusher_channel, 'new_vote', $id['id']);
    return $current;
  endif;
}

function downvote_post($request_data) {
  global $pusher;
  global $pusher_channel;

  $id = $request_data->get_params();
  $current = (get_post_meta(   $id['id'], 'votes', true )) ? get_post_meta(   $id['id'], 'votes', true ) : 0;
  if (!get_option('voting_enabled')):
    return $current;
  else:
    $current = intval($current) - 1;
    update_post_meta(   $id['id'], 'votes', $current );
    $pusher->trigger($pusher_channel, 'new_vote', $id['id']);  
    return $current;
  endif;
}

//Pusher notifications