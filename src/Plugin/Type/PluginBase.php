<?php

namespace Drupal\import\Plugin\Type;

use Drupal\Core\Plugin\PluginBase as DrupalPluginBase;
use Drupal\Core\Entity\DependencyTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PluginBase
 * @package Drupal\import\Plugin\Type
 */
abstract class PluginBase extends DrupalPluginBase implements ImporterPluginInterface
{
  use DependencyTrait;

  /**
   * PluginBase constructor.
   *
   * @param array $configuration
   * @param       $pluginId
   * @param array $pluginDefinition
   */
  public function __construct(array $configuration, $pluginId, array $pluginDefinition)
  {
    $this->setConfiguration($configuration);
    $this->pluginId = $pluginId;
    $this->pluginDefinition = $pluginDefinition;
  }
  
  /**
   * @param ContainerInterface $container
   * @param array              $configuration
   * @param                    $pluginId
   * @param array              $pluginDefinition
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, array $pluginDefinition)
  {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition
    );
  }
  
  /**
   * {@inheritdoc}
   */
  public function pluginType()
  {
    return $this->pluginDefinition['plugin_type'];
  }
  
  /**
   * {@inheritdoc}
   */
  public function getConfiguration($key = null)
  {
    if ($key === null) {
      return $this->configuration;
    }
    
    if (isset($this->configuration[$key])) {
      return $this->configuration[$key];
    }
    
    return [];
  }
  
  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration)
  {
    $defaults = $this->defaultConfiguration();
    $this->configuration = array_intersect_key($configuration, $defaults) + $defaults;
  }
  
  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration()
  {
    return [];
  }
  
  /**
   * {@inheritdoc}
   */
  public function defaultImportConfiguration()
  {
    return [];
  }
}
