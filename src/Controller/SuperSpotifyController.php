<?php

namespace Drupal\superspotify\Controller;

use \Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\Component\Utility\Html;
use SpotifyWebAPI;

/**
 * Controller routines for Super Spotify pages.
 */
class SuperSpotifyController {
  public function callback() {
	// Default Settings
	$config = \Drupal::config('superspotify.settings');
	$clientid = $config->get('superspotify.clientid');
	$clientsecret = $config->get('superspotify.clientsecret');
	$redirecturi = $config->get('superspotify.redirecturi');

	$session = new SpotifyWebAPI\Session($clientid, $clientsecret, $redirecturi);
	$api = new SpotifyWebAPI\SpotifyWebAPI();
	if (isset($_GET['code'])) {
		$session->requestAccessToken($_GET['code']);
		session_start();
		$_SESSION['superspotify_token'] = $session->getAccessToken();
		header('Location: /superspotify/import');
	} 
	else {
		$options = [
		'scope' => [
			'user-library-read',
		],
	];
		header('Location: ' . $session->getAuthorizeUrl($options));
	}
	die();
  }
  
  public function import() {
 
	// Default Settings
	$config = \Drupal::config('superspotify.settings');
	$clientid = $config->get('superspotify.clientid');
	$clientsecret = $config->get('superspotify.clientsecret');
	$redirecturi = $config->get('superspotify.redirecturi');

	$session = new SpotifyWebAPI\Session($clientid, $clientsecret, $redirecturi);

	$options = [
		'scope' => [
			'user-library-read',
		],
	];
	$authorizeUrl = $session->getAuthorizeUrl($options);
	$scopes = $session->getScope();
	$api = new SpotifyWebAPI\SpotifyWebAPI();
	session_start();
	if (isset($_SESSION['superspotify_token'])) {
		$api = new SpotifyWebAPI\SpotifyWebAPI();
		$accessToken = $_SESSION['superspotify_token'];
		$api->setAccessToken($accessToken);
		$scopes = $session->getScope();
		$myAlbums = $api->getMySavedAlbums(array('limit' => 50));
		$total_albums = $myAlbums->total;
		
		$albumnames = array();
		$this->delete_albums();
		for ($x = 0; $x <= $total_albums; $x += 50) {
			$myAlbums = $api->getMySavedAlbums(array('offset' =>$x, 'limit' => 50));
			foreach ($myAlbums->items as $item) {	
				$album_name = $item->album->name;
				$album_url = $item->album->external_urls->spotify;
				$artist_name = $item->album->artists[0]->name;
				$artist_url = $item->album->artists[0]->external_urls->spotify;
				$artwork_url = $item->album->images[0]->url;
				$release_date = $item->album->release_date;
				$current_album = array(
					'album_name' => Html::escape($album_name), 
					'album_url' => Html::escape($album_url), 
					'artist_name' => Html::escape($artist_name),
					'artist_url' => Html::escape($artist_url),
					'artwork_url' => Html::escape($artwork_url),
					'release_date' => Html::escape($release_date),
				);
				
				$this->save_album($current_album);			
				$albums[] = $current_album;
			}			
		}
	
	} else {
		header('Location: ' . $session->getAuthorizeUrl($options));
		die();
	}
	
	$element['#spotify_text'] = array();
	$element['#spotify_text']['albums'] = $albums;
	$element['#title'] = Html::escape('Import Spotify Albums');
	
	// Theme function.
	$element['#theme'] = 'superspotify';
	
	return $element;
  }
  
  public function delete_albums() {
	$uid = \Drupal::currentUser()->id();
	
	$result = \Drupal::entityQuery('node')
		->condition('type', 'spotify_album')
		->condition('uid', $uid)
		->execute();
	entity_delete_multiple('node', $result);
	
	return $result;
  }
  
  public function save_album($current_album) {
	if (strlen($current_album['release_date']) == 4) {
		$current_album['release_date'] = $current_album['release_date'].'-01-01';
	}
	$node = \Drupal::entityTypeManager()->getStorage('node')->create(array(
	'type'        => 'spotify_album',
	'title'       => $current_album['album_name'],
	'field_release_date' => [
        $current_album['release_date'],
      ],
	'field_album_url' => $current_album['album_url'],
	'field_artist' => $current_album['artist_name'],
	'field_artist_url' => $current_album['artist_url'],
	'field_artwork_url' => $current_album['artwork_url'],
	));
	$node->save();
	
  }
}	