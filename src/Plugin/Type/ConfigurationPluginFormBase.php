<?php

namespace Drupal\import\Plugin\Type;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\import\Plugin\PluginAwareInterface;

/**
 * Class ConfigurationPluginFormBase
 * @package Drupal\import\Plugin\Type
 */
abstract class ConfigurationPluginFormBase implements PluginFormInterface, PluginAwareInterface
{
  use StringTranslationTrait;
  use DependencySerializationTrait;
  
  /**
   * @var ImporterPluginInterface
   */
  protected $plugin;
  
  /**
   * {@inheritdoc}
   */
  public function setPlugin(ImporterPluginInterface $plugin)
  {
    $this->plugin = $plugin;
  }
  
  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state)
  {
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state)
  {
    $this->plugin->setConfiguration($form_state->getValues());
  }
}
