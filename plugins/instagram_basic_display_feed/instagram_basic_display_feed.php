<?php
/**
 * @package Instagram Basic Display Feed
 * @version 0.1
 */
/*
Plugin Name: Instagram Basic Display Feed
Plugin URI:
Description:
Author: GRND CTRL
Version: 0.2
Author URI: https://grndctrl.io
*/

function ibd_register_settings()
{
    add_option('ibd_user_token', '');
    add_option('ibd_timestamp', '');
    register_setting('ibd_options_group', 'ibd_user_token', 'ibd_callback');
}
 add_action('admin_init', 'ibd_register_settings');

 function ibd_register_options_page()
 {
     add_options_page('Instagram Feed', 'Instagram Feed', 'administrator', 'instagram-basic-display', 'ibd_options_page');
 }
  add_action('admin_menu', 'ibd_register_options_page');

function ibd_options_page()
{
    ?>
  <div>
  <h2>Instagram Basic Display Feed</h2>
  <form method="post" action="options.php">
  <?php settings_fields('ibd_options_group'); ?>
  <p>Enter generated user token below.</p>
  <table>
  <tr valign="top">
  <th scope="row"><label for="ibd_user_token">Token</label></th>
  <td><input type="text" id="ibd_user_token" name="ibd_user_token" value="<?php echo get_option('ibd_user_token'); ?>" /></td>
  </tr>
  </table>
  <?php  submit_button(); ?>
  </form>
  </div>
<?php
}

function do_instagram_options()
{
    date_default_timezone_set('Europe/Amsterdam');
  
    $curr_token = get_option('ibd_user_token');
    $then = (int)get_option('ibd_timestamp');
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
      
            update_option('ibd_user_token', $fresh_token);
            update_option('ibd_timestamp', $now);
        }
    }
    return array(
      'token' => get_option('ibd_user_token'),
      'timestamp' => get_option('ibd_timestamp')
    );
}

function get_instagram_post()
{
    date_default_timezone_set('Europe/Amsterdam');
  
    do_instagram_options();
  
    $transient = get_transient('ibd_last_insta_post');
    
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
  
      
        $api_token = get_option('ibd_user_token');
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
        set_transient('ibd_last_insta_post', $transient, 60*10);
        return $transient;
    }
}
