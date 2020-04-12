<?php

namespace Drupal\import\Plugin\Type;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Interface ImporterPluginInterface
 * @package Drupal\import\Plugin\Type
 */
interface ImporterPluginInterface extends PluginInspectionInterface, ConfigurableInterface
{
  /**
   * @return mixed
   */
  public function pluginType();
  
  /**
   * @return mixed
   */
  public function defaultImportConfiguration();
}
