<?php

namespace Drupal\import;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Importer entities.
 *
 * @ingroup import
 */
class ImporterListBuilder extends EntityListBuilder
{
  
  /**
   * {@inheritdoc}
   */
  public function buildHeader()
  {
    $header['id'] = $this->t('Importer ID');
    $header['name'] = $this->t('Name');
    
    return $header + parent::buildHeader();
  }
  
  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity)
  {
    /* @var \Drupal\import\Entity\Importer $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.importer.edit_form',
      ['importer' => $entity->id()]
    );
    
    return $row + parent::buildRow($entity);
  }
  
  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity)
  {
    $operations = parent::getDefaultOperations($entity);
    $operations['edit']['weight'] = 0;
    
    if ($entity->hasLinkTemplate('import')) {
      $operations['import'] = [
        'title'  => $this->t('Import'),
        'weight' => 2,
        'url'    => $entity->toUrl('import'),
      ];
    }
    
    $destination = $this->redirectDestination->getAsArray();
    
    foreach ($operations as $key => $operation) {
      $operations[$key]['query'] = $destination;
    }
    
    return $operations;
  }
}
