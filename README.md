Super Spotify
=============

Making Spotify better in Drupal 8.

Installation
=============

Super Spotify uses PHP libraries managed by Composer. 
If you are not using Composer to install Drupal, update the extra section of your root composer.json file
to include:
"extra": {
  "merge-plugin": {
    "require": [
      "modules/custom/superspotify/composer.json"
    ]
  }
}

Using the Module
================

After installing the module, a Spotify Albums content type is created. 

Additionally a Your Spotify Albums view is created that queries the Spotify Albums content type. 
It is located here: <sitename>/superspotify/albums/
  
To import your albums from Spotify, go to this page and authorize the script to query your Spotify information:
<sitename>/superspotify/import/
  
Eventually, the importer will work via the Batch API. This is still in progress, but you can see the form here:
<sitename>/superspotify/batchimport/
