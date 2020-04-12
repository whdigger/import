<?php

namespace Drupal\import\Importer\Fields;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Utility\Token;
use GuzzleHttp\ClientInterface;

/**
 * Class File
 * @package Drupal\import\Importer\Fields
 */
class File
{
  use StringTranslationTrait;
  
  /**
   * @var ClientInterface
   */
  protected $client;
  
  /**
   * @var Token
   */
  protected $token;
  
  /**
   * File constructor.
   *
   * @param ClientInterface $client
   * @param Token           $token
   */
  public function __construct(ClientInterface $client, Token $token)
  {
    $this->token = $token;
    $this->client = $client;
  }
  
  /**
   * @param $field
   * @param $url
   *
   * @return int|string|void|null
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getFile($field, $url)
  {
    if (empty($url)) {
      return;
    }
    
    $settings = $field->getSettings();
    $filepath = $this->getDestinationDirectory($settings) . '/' . $this->getFileName($url, $settings);
    
    if ($file = file_save_data($this->getContent($url), $filepath, FILE_EXISTS_REPLACE)) {
      return $file->id();
    }
  }
  
  /**
   * @param $settings
   *
   * @return string
   */
  protected function getDestinationDirectory($settings)
  {
    $destination = $this->token->replace($settings['uri_scheme'] . '://' . trim($settings['file_directory'], '/'));
    file_prepare_directory($destination, FILE_MODIFY_PERMISSIONS | FILE_CREATE_DIRECTORY);
    
    return $destination;
  }
  
  /**
   * @param $url
   * @param $settings
   *
   * @return string
   * @throws \Exception
   */
  protected function getFileName($url, $settings)
  {
    $fileExtensions = array_filter(explode(' ', $settings['file_extensions']));
    
    $filename = trim(\Drupal::service('file_system')->basename($url), " \t\n\r\0\x0B.");
    $extension = substr($filename, strrpos($filename, '.') + 1);
    
    if (!preg_grep('/' . $extension . '/i', $fileExtensions)) {
      throw new \Exception($this->t('The file, %url invalid extension %ext.', [
        '%url' => $url,
        '%ext' => $extension,
      ]));
    }
    
    return $filename;
  }
  
  /**
   * @param $url
   *
   * @return string
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function getContent($url)
  {
    $response = $this->client->request('GET', $url);
    
    if ($response->getStatusCode() >= 400) {
      $args = [
        '%url'  => $url,
        '%code' => $response->getStatusCode(),
      ];
      throw new \Exception($this->t('Download of %url failed with code %code.', $args));
    }
    
    return (string)$response->getBody();
  }
}
