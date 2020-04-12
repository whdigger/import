<?php

namespace Drupal\import\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Defines the Importer type entity.
 *
 * @ConfigEntityType(
 *   id = "importer_type",
 *   label = @Translation("Importer type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\import\ImporterTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\import\Form\ImporterTypeForm",
 *       "add" = "Drupal\import\Form\ImporterTypeForm",
 *       "edit" = "Drupal\import\Form\ImporterTypeForm",
 *       "delete" = "Drupal\import\Form\ImporterTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\import\ImporterTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "importer_type",
 *   bundle_of = "importer",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/importer_type/{importer_type}",
 *     "add-form" = "/admin/structure/importer_type/add",
 *     "edit-form" = "/admin/structure/importer_type/{importer_type}/edit",
 *     "delete-form" = "/admin/structure/importer_type/{importer_type}/delete",
 *     "collection" = "/admin/structure/importer_type",
 *     "mapping" = "/admin/structure/importer_type/manage/{importer_type}/mapping"
 *   },
 *   admin_permission = "administer site configuration"
 * )
 */
class ImporterType extends ConfigEntityBundleBase implements ImporterTypeInterface
{
  /**
   * @var string
   */
  protected $id;
  
  /**
   * @var string
   */
  protected $label;
  
  /**
   * @var string
   */
  protected $fetcher = 'http';
  
  /**
   * @var string
   */
  protected $entityHandler = 'node';
  
  /**
   * @var string
   */
  protected $parser = 'rss';
  
  /**
   * @var array
   */
  protected $pluginTypes = ['parser', 'fetcher', 'entityHandler'];
  
  /**
   * @var string
   */
  protected $entityTypeMaterial = '';
  
  /**
   * @var array
   */
  protected $pluginCollection;
  
  /**
   * @var array
   */
  protected $mappings = [];
  
  /**
   * @var string
   */
  protected $primaryKey = '';
  
  /**
   * @return string
   */
  public function getEntityTypeMaterial(): string
  {
    return $this->entityTypeMaterial;
  }
  
  /**
   * @return int
   */
  public function getTypeMaterial(): string
  {
    return $this->entityHandler;
  }
  
  /**
   * @return string
   */
  public function getSourcesFieldByTarget($target): string
  {
    return $this->mappings[$target] ?? '';
  }
  
  /**
   * @return string
   */
  public function getMappings(): array
  {
    return $this->mappings;
  }
  
  /**
   * @param $primaryKey
   *
   * @return $this
   */
  public function setPrimaryKey($primaryKey)
  {
    $this->primaryKey = $primaryKey;
    
    return $this;
  }
  
  /**
   * @return string
   */
  public function getPrimaryKey()
  {
    return $this->primaryKey;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getPlugins()
  {
    $plugins = [];
    foreach ($this->pluginTypes as $type) {
      $plugins[$type] = $this->getPlugin($type);
    }
    
    return $plugins;
  }
  
  /**
   * @param $type
   *
   * @return mixed
   */
  protected function getPlugin($type)
  {
    if (!isset($this->pluginCollection[$type])) {
      $typeService = \Drupal::service("plugin.manager.import.$type");
      $this->pluginCollection[$type] = $typeService->createInstance($this->get($type), []);
    }
    
    return $this->pluginCollection[$type];
  }
  
  /**
   * {@inheritdoc}
   */
  public function getFetcher()
  {
    return $this->getPlugin('fetcher');
  }
  
  /**
   * {@inheritDoc}
   */
  public function getParser()
  {
    return $this->getPlugin('parser');
  }
  
  /**
   * {@inheritDoc}
   */
  public function getEntityHandler()
  {
    return $this->getPlugin('entityHandler');
  }
  
  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storageController, $update = true)
  {
    $field = 'importer_meta';
    if (!FieldStorageConfig::loadByName($this->entityHandler, $field)) {
      FieldStorageConfig::create([
        'field_name'   => $field,
        'entity_type'  => $this->entityHandler,
        'type'         => $field,
        'translatable' => false,
      ])->save();
    }
    if (!FieldConfig::loadByName($this->entityHandler, $this->entityTypeMaterial, $field)) {
      FieldConfig::create([
        'label'       => 'Import meta',
        'description' => 'Metadate for import module',
        'field_name'  => $field,
        'entity_type' => $this->entityHandler,
        'bundle'      => $this->entityTypeMaterial,
      ])->save();
    }
    parent::preSave($storageController, $update);
  }
}
