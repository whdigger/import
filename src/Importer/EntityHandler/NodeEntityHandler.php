<?php
namespace Drupal\import\Importer\EntityHandler;

use Drupal\import\Entity\ImporterInterface;
use Drupal\import\Importer\Item\ItemInterface;

/**
 * Class NodeImporter.
 *
 * @ImporterEntityHandler(
 *   id = "node",
 *   entity_type = "node",
 *   label = @Translation("Node bundle")
 * )
 */
class NodeEntityHandler extends EntityHandlerBase
{
  /**
   * {@inheritdoc}
   */
  public function handle(ImporterInterface $importer, ItemInterface $item)
  {
    $fieldName = $importer->getType()->getPrimaryKey();
    if (empty($fieldName)) {
      return;
    }
    
    $itemValue = $item->get($fieldName);
    if (empty($itemValue)) {
      return;
    }
    
    if ($this->existEntity($importer, $item)) {
      return;
    }
    
    $entity = $this->createEntity($importer);
    
    $hash = $this->getHash($fieldName, $itemValue);
    $importerMeta = $entity->get('importer_meta');
    $importerMeta->target_id = $importer->id();
    $importerMeta->hash = $hash;
    
    $this->map($importer, $entity, $item);
    $this->storage->save($entity);
  }
}
