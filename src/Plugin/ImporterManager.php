<?php

namespace Drupal\import\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Class ImporterManager
 * @package Drupal\import\Plugin
 */
class ImporterManager extends DefaultPluginManager
{
  private const PLUGIN_DEFINITION_ANNOTATION_NAME = [
    'fetcher'       => 'Drupal\import\Annotation\ImporterFetcher',
    'parser'        => 'Drupal\import\Annotation\ImporterParser',
    'entityHandler' => 'Drupal\import\Annotation\ImporterEntityHandler',
  ];
  
  private const PLUGIN_INTERFACE = [
    'fetcher'       => 'Drupal\import\Plugin\Type\Fetcher\FetcherInterface',
    'parser'        => 'Drupal\import\Plugin\Type\Parser\ParserInterface',
    'entityHandler' => 'Drupal\import\Plugin\Type\EntityHandler\EntityHandlerInterface',
  ];
  protected $pluginType;
  
  public function __construct(
    $type,
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    LanguageManagerInterface $language_manager,
    ModuleHandlerInterface $moduleHandler
  ) {
    $this->pluginType = $type;
    
    parent::__construct(
      'Importer/' . ucfirst($type),
      $namespaces,
      $moduleHandler,
      self::PLUGIN_INTERFACE[$type],
      self::PLUGIN_DEFINITION_ANNOTATION_NAME[$type]
    );
    
    $this->alterInfo("import_{$type}_info");
    $this->setCacheBackend($cache_backend, "import_{$type}_info");
  }
}
