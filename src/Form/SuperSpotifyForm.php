<?php

namespace Drupal\superspotify\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SuperSpotifyForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'superspotify_form';
  }
  
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
	
	  // Form constructor.
	  $form = parent::buildForm($form, $form_state);
	  // Default settings.
	  $config = $this->config('superspotify.settings');
	
	  // Client ID field
    $form['clientid'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Spotify API Client ID'),
      '#default_value' => $config->get('superspotify.clientid'),
      '#description' => $this->t('Your Spotify App Client ID. Sign up at: https://developer.spotify.com'),
    );
  
    // Client Secret field
    $form['clientsecret'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Spotify API Secret Key'),
      '#default_value' => $config->get('superspotify.clientsecret'),
      '#description' => $this->t('Your Spotify App Secret Key. Sign up at: https://developer.spotify.com'),
    );
      
    // redirect URI field
    $form['redirecturi'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Spotify API Redirect URI'),
      '#default_value' => $config->get('superspotify.redirecturi'),
      '#description' => $this->t('Your Spotify App Redirect URI. Sign up at: https://developer.spotify.com'),
    );
  
	
	  return $form;  
  }
  
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('superspotify.settings');
    $config->set('superspotify.clientid', $form_state->getValue('clientid'));
    $config->set('superspotify.clientsecret', $form_state->getValue('clientsecret'));
    $config->set('superspotify.redirecturi', $form_state->getValue('redirecturi'));
    $config->save();
    return parent::submitForm($form, $form_state);
  }
  
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'superspotify.settings',
    ];
  }
  
}	