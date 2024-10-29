<?php
defined('ABSPATH') or die('This page may not be accessed directly.');

/**
 * Plugin Name: AppiFire for Mobile Apps
 * Plugin URI: https://appifire.com
 * Description: AppiFire.com - AppiFire convert your WordPress website into Android & iOS app. An app that is easy to use, has a blazingly fast performance and design that you will fall in love with.
 * This plugin currently an extension of "OneSignal – Free Web Push Notifications" plugin and this plugin should be installed first. Our plugin code sends additional push notification to android and iOS via OneSignal API call. 
 * More features will be added soon which include AppiFire API and other supporting features which are used in AppiFire app.
 * Version: 1.0
 */

class AppiFire 
{
	function construct() {
	}
	
	// OneSignal offical gave a solution on the link below. We wrote the appifire_onesignal_send_notification_filter() method with accordance with WordPress standards like use of wp_remote_post() and also changed notifcation title and description and other modifications.
	// https://documentation.onesignal.com/docs/web-push-wordpress-faq
	
	function appifire_onesignal_send_notification_filter($fields, $new_status, $old_status, $post) 
	{
		$body = new stdClass();
		$body->app_id = $fields['app_id'];

		$body->headings = array('en' => $post->post_title);
		$body->contents = array('en' => $fields['headings']['en'].' New Article');

		$body->included_segments = $fields['included_segments'];

		// $body->filters = array('All'); // ["Active Users", "Inactive Users"]
		// $body->filters = array(array('field' => 'tag', 'key' => 'authorID', 'relation' => '=', 'value' => '54'));

		$body->isAndroid = true;
		$body->isIos = true;
		$body->isAnyWeb = false;
		$body->isWP = false;
		$body->isAdm = false;
		$body->isChrome = false;

		$body->data->wordpress_url = $fields['url'];
		$body->data->post_id = $post->ID;

		$bodyAsJson = json_encode($body);

		/* Plugin Call = OneSignal::get_onesignal_settings(); */
		$onesignal_wp_settings = OneSignal::get_onesignal_settings();

		$onesignal_auth_key = $onesignal_wp_settings['app_rest_api_key'];
		
		$response = wp_remote_post( "https://onesignal.com/api/v1/notifications", array(
				'method' => 'POST',
				'timeout' => 60,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'sslverify' => false,
				'headers' => array("Content-type" => "application/json",
					"Authorization" => "Basic ".$onesignal_auth_key),
				'body' =>$bodyAsJson,
			)
		);

		$response_headers =  wp_remote_retrieve_headers( $response );

		$body = wp_remote_retrieve_body( $response );

		$response_code =  wp_remote_retrieve_response_code( $response );

		// Cancel the notification from being sent via original plugin
		// $fields['do_send_notification'] = false;	

		// unset url so that web notications is not sent
		// unset($fields['url']);	

		return $fields;
	}

}

add_filter('onesignal_send_notification', array( 'AppiFire', 'appifire_onesignal_send_notification_filter'), 10, 4);

?>