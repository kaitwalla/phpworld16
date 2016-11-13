<?php
  require('pusher.php');
  $options = array(
    'encrypted' => true
  );
  
  $pusher = new Pusher(
    '',
    '',
    '',
    $options
  );

  $pusher_channel = '';