<?php

/*
Plugin Name: Instagram Feed
Plugin URI:
Description: Get instagram feed
Author: Mark Brand
Author URI: grndctrl.io
Version: 1.0
Text Domain:
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
error_reporting(0);

function do_instagram_options() {
  date_default_timezone_set('Europe/Amsterdam');
  
  $curr_token = get_option('kap_insta_token');
  $then = (int)get_option('kap_insta_timestamp');
  $now = time();

  $diff = $now - $then;
  $days = $diff / 8640;

  $api_url = 'https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=' . $curr_token;
  if ($days > 14) {   
    // create & initialize a curl session
    $curl = curl_init();
    
    // set our url with curl_setopt()
    curl_setopt($curl, CURLOPT_URL, $api_url);
    
    // return the transfer as a string, also with setopt()
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    
    // curl_exec() executes the started curl session
    // $output contains the output string
    $json = curl_exec($curl);
    
    // close curl resource to free up system resources
    // (deletes the variable made by curl_init)
    curl_close($curl);
    
    $fresh_token = false;
    
    if (json_decode($json)->access_token) {
      $fresh_token = json_decode($json)->access_token;
      
      update_option('kap_insta_token', $fresh_token);
      update_option('kap_insta_timestamp', $now);
    } 
    
  }
    return array(
      'token' => get_option('kap_insta_token'),
      'timestamp' => get_option('kap_insta_timestamp')
    );
}

function get_instagram_post() {
  date_default_timezone_set('Europe/Amsterdam');

  do_instagram_options();

  $transient = get_transient('last_insta_post');
  
  if ($transient) {
    return $transient;
  } else {
    $api_fields = array(
      'id',
      'caption',
      'media_type',
      'media_url',
      'permalink',
      'username',
      'timestamp'
    );

    
    $api_token = get_option('kap_insta_token');
    if (!$api_token) {
      $api_token = 'IGQVJXZA1AwZAWppNHRqSWJZAdDhYZAHhma0NHMnJTTV9aTmd5M05UX3BEM3hEWm50bzk2Umttd25oaW1RMFZAiWHdUOVpOYmR0dFlQOXo4UF81RTRhVk1SWUU4YUtQcnNMNHZA5UWZA0T3kyV1lsZAmJlMnY5OAZDZD';
    }
    $api_url = 'https://graph.instagram.com/me/media?fields=' . implode(',', $api_fields) . '&access_token=' . $api_token;

    // create & initialize a curl session
    $curl = curl_init();

    // set our url with curl_setopt()
    curl_setopt($curl, CURLOPT_URL, $api_url);

    // return the transfer as a string, also with setopt()
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    // curl_exec() executes the started curl session
    // $output contains the output string
    $json = curl_exec($curl);

    // close curl resource to free up system resources
    // (deletes the variable made by curl_init)
    curl_close($curl);

    $last_post = false;

    if (json_decode($json)->data) {
      $last_post = json_decode($json)->data[0];
    } 

    $transient = $last_post;
    set_transient('last_insta_post', $transient, 60*10);
    return $transient;
  }


  // $api_fields = array(
  //   'id',
  //   'caption',
  //   'media_type',
  //   'media_url',
  //   'permalink',
  //   'username',
  //   'timestamp'
  // );
  // $api_token = 'IGQVJVUUpKcTh2T3pwdHN6X0pHempnQmdRSlo2bzBVTUItdW0tSkJ2V0hyampwamVNODdaVnpmVlhHLUJTN0hYemtRVTczZATBLN0VBN3g5Q21TaWc2ajdDNV93QU5DRkQ1Tlh1TVNZAT2xWa2lDUk9OYwZDZD';
  // $api_url = 'https://graph.instagram.com/me/media?fields=' . implode(',', $api_fields) . '&access_token=' . $api_token;


  // // create & initialize a curl session
  // $curl = curl_init();

  // // set our url with curl_setopt()
  // curl_setopt($curl, CURLOPT_URL, $api_url);

  // // return the transfer as a string, also with setopt()
  // curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

  // // curl_exec() executes the started curl session
  // // $output contains the output string
  // $json = curl_exec($curl);

  // // close curl resource to free up system resources
  // // (deletes the variable made by curl_init)
  // curl_close($curl);

  // $last_post = '';

  // if (json_decode($json)->data) {
  //   return json_decode($json)->data[0];
  // } else {
  //   return false;
  // }
}

?>