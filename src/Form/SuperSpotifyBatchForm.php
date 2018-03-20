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

    $albums = [];
    
      for ($i = 0; $i < $total_albums; $i++) {
        $albums[] = [
          'superspotify_batch_import',
          [
            $i + 1,
            t('(Album @operation)', ['@operation' => $i]),
          ],
        ];
      }

    $batch = [
      'title' => t('Importing @num albums', ['@num' => $total_albums]),
      'operations' => $albums,
      'finished' => 'superspotify_finished',
    ];
    return $batch;
  }  
}