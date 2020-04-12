<?php

namespace Drupal\import\Plugin;

use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\import\Plugin\Type\ImporterPluginInterface;

/**
 * Class PluginFormFactory
 * @package Drupal\import\Plugin
 */
class PluginFormFactory
{
  /**
   * @var ClassResolverInterface
   */
  protected $classResolver;
  
  /**
   * @param ClassResolverInterface $classResolver
   */
  public function __construct(ClassResolverInterface $classResolver)
  {
    $this->classResolver = $classResolver;
  }
  
  /**
   * @param ImporterPluginInterface $plugin
   * @param                         $action
   *
   * @return bool
   */
  public function hasForm(ImporterPluginInterface $plugin, string $action): bool
  {
    $definition = $plugin->getPluginDefinition();
    
    if (empty($definition['form'][$action])) {
      return false;
    }
    
    $classForm = $definition['form'][$action];
    
    return class_exists($classForm) && is_subclass_of($classForm, PluginFormInterface::class);
  }
  
  /**
   * @param ImporterPluginInterface $plugin
   * @param string                  $action
   *
   * @return object
   */
  public function createInstance(ImporterPluginInterface $plugin, string $action)
  {
    $definition = $plugin->getPluginDefinition();
    $instanceFormDefinition = $this->classResolver->getInstanceFromDefinition($definition['form'][$action]);
    
    if ($instanceFormDefinition instanceof PluginAwareInterface) {
      $instanceFormDefinition->setPlugin($plugin);
    }
    
    return $instanceFormDefinition;
  }
  
}
