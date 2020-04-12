<?php

namespace Drupal\import\Plugin;

use Drupal\import\Plugin\Type\ImporterPluginInterface;

/**
 * Interface PluginAwareInterface
 * @package Drupal\import\Plugin
 */
interface PluginAwareInterface
{
  /**
   * @param ImporterPluginInterface $plugin
   *
   * @return mixed
   */
  public function setPlugin(ImporterPluginInterface $plugin);
}
