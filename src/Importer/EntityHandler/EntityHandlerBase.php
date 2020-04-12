<?php

namespace Drupal\import\Importer\EntityHandler;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\import\Entity\ImporterInterface;
use Drupal\import\Importer\Item\ItemInterface;
use Drupal\import\Plugin\Type\EntityHandler\EntityHandlerInterface;
use Drupal\import\Plugin\Type\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class EntityHandlerBase extends PluginBase implements EntityHandlerInterface, ContainerFactoryPluginInterface
{
  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  
  /**
   * @var EntityStorageInterface
   */
  protected $storage;
  
  /**
   * @var EntityTypeInterface
   */
  protected $entityType;
  
  protected $file;
  
  /**
   * @param ContainerInterface $container
   * @param array              $configuration
   * @param                    $pluginId
   * @param array              $pluginDefinition
   *
   * @return PluginBase
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition)
  {
    $instance = parent::create($container, $configuration, $pluginId, $pluginDefinition);
    $entityTypeManager = $container->get('entity_type.manager');
    
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->entityType = $entityTypeManager->getDefinition($pluginDefinition['entity_type']);
    $instance->storage = $entityTypeManager->getStorage($pluginDefinition['entity_type']);
    $instance->file = $container->get('plugin.import.fields.file');
    
    return $instance;
  }
  
  /**
   * @return string
   */
  public function bundleKey(): string
  {
    return $this->entityType->getKey('bundle');
  }
  
  /**
   * @param ImporterInterface $importer
   * @param ItemInterface     $item
   *
   * @return mixed|void
   */
  protected function existEntity(ImporterInterface $importer, ItemInterface $item)
  {
    $fieldName = $importer->getType()->getPrimaryKey();
    $hash = $this->getHash($fieldName, $item->get($fieldName));
    
    $nameTypeMaterial = $importer->getType()->getTypeMaterial();
    $query = \Drupal::entityQuery($nameTypeMaterial)
      ->accessCheck(false)
      ->condition('importer_meta.hash', $hash)
      ->range(0, 1);
    
    $bundleKey = $this->bundleKey();
    if ($bundleKey) {
      $query->condition($bundleKey, $importer->getType()->getEntityTypeMaterial());
    }
    
    if ($result = $query->execute()) {
      return true;
    }
    
    return false;
  }
  
  /**
   * @param ImporterInterface $importer
   *
   * @return EntityInterface
   */
  protected function createEntity(ImporterInterface $importer): EntityInterface
  {
    $typeEntity = [];
    if ($this->entityType->hasKey('bundle') && $importer->getType()->getEntityTypeMaterial()) {
      $typeEntity = [$this->bundleKey() => $importer->getType()->getEntityTypeMaterial()];
    }
    
    $entity = $this->storage->create($typeEntity);
    $entity->enforceIsNew();
    
    return $entity;
  }
  
  /**
   * @param ImporterInterface $importer
   * @param EntityInterface   $entity
   * @param ItemInterface     $parsedItem
   *
   * @return EntityInterface
   */
  protected function map(ImporterInterface $importer, EntityInterface $entity, ItemInterface $parsedItem): EntityInterface
  {
    $mappings = $importer->getType()->getMappings();
    foreach ($mappings as $target => $sources) {
      
      $entityField = $entity->get($target)->getFieldDefinition();
      if ($entityField->isReadOnly() || $target === $this->bundleKey()) {
        continue;
      }
      $value = $this->getMappedValue($entityField, $parsedItem, $sources);
      
      if (!empty($value)) {
        $entity->set($target, $value);
      }
    }
    
    return $entity;
  }
  
  /**
   * @param $fieldName
   * @param $value
   *
   * @return string
   */
  protected function getHash($fieldName, $value)
  {
    return hash('md5', $fieldName . $value);
  }
  
  /**
   * @TODO Create an abstract factory
   *
   * @param               $entityField
   * @param ItemInterface $parsedItem
   * @param string        $sources
   *
   * @return mixed
   */
  private function getMappedValue($entityField, ItemInterface $parsedItem, string $sources)
  {
    $value = $parsedItem->get($sources);
    switch ($entityField->getType()) {
      case 'file':
        $value = [
          'target_id' => $this->file->getFile($entityField, $parsedItem->get($sources)),
        ];
        break;
      
      case 'text_with_summary':
        $value = [
          'value'  => $value,
          'format' => 'full_html',
        ];
        break;
    }
    
    return $value;
  }
}
