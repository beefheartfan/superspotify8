<?php

namespace Drupal\superspotify\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\Component\Utility\Html;
use SpotifyWebAPI;

class SuperSpotifyBatchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'superspotify_batch_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['description'] = [
      '#type' => 'markup',
      '#markup' => t('Submit this form to import your Spotify albums.'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Import Albums',
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $batch = $this->importAlbumsBatch();
    batch_set($batch);
  }
  
  public function importAlbumsBatch() {

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
  }    
    
    drupal_set_message(t('Importing @num Spotify albums', ['@num' => $total_albums]));
    superspotify_delete_albums();
    
    $albums = [];
    for ($x = 0; $x <= $total_albums; $x += 50) {
      $myAlbums = $api->getMySavedAlbums(array('offset' =>$x, 'limit' => 50));
      foreach ($myAlbums->items as $item) {	
        $album_name = $item->album->name;
        $album_url = $item->album->external_urls->spotify;
        $artist_name = $item->album->artists[0]->name;
        $artist_id = $item->album->artists[0]->id;
        $artist_url = $item->album->artists[0]->external_urls->spotify;
        $artwork_url = $item->album->images[0]->url;
        $release_date = $item->album->release_date;
        $current_album = array(
        'album_name' => Html::escape($album_name), 
        'album_url' => Html::escape($album_url), 
        'artist_name' => Html::escape($artist_name),
        'artist_id' => Html::escape($artist_id),
        'artist_url' => Html::escape($artist_url),
        'artwork_url' => Html::escape($artwork_url),
        'release_date' => Html::escape($release_date),
        );
        
        $albums[] = [
          'superspotify_batch_import',
          [
            $i + 1,
            t('(Album @operation)', ['@operation' => $i]),
            $current_album,
            $api,
          ],
        ];      
      
      }			
    }

    $batch = [
      'title' => t('Importing @num albums', ['@num' => $total_albums]),
      'operations' => $albums,
      'finished' => 'superspotify_finished',
    ];
    return $batch;
  }  
}