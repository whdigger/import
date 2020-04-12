<?php

namespace Drupal\import\Importer\Helper;

use GuzzleHttp\Psr7\Uri;
use Zend\Feed\Reader\Reader;

/**
 * Class ImporterHelper
 * @package Drupal\import\Importer\Helper
 */
class ImporterHelper
{
  /**
   * @param $data
   *
   * @return bool
   */
  public static function canReadRss($data): bool
  {
    Reader::setExtensionManager(\Drupal::service('feed.bridge.reader'));
    
    try {
      $feedType = Reader::detectType($data);
    } catch (\Exception $e) {
      return false;
    }
    
    return $feedType != Reader::TYPE_ANY;
  }
  
  /**
   * @param $url
   *
   * @return bool
   */
  public static function isValidSchema($url): bool
  {
    $uri = new Uri($url);
    
    if (in_array($uri->getScheme(), self::getSupportedSchemes())) {
      return true;
    }
    
    throw new \InvalidArgumentException();
  }
  
  /**
   * @return array
   */
  public static function getSupportedSchemes(): array
  {
    return [
      'http',
      'https',
    ];
  }
  
}
