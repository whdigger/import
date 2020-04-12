<?php

namespace Drupal\import;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Importer entity.
 *
 * @see \Drupal\import\Entity\Importer.
 */
class ImporterAccessControlHandler extends EntityAccessControlHandler
{
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account)
  {
    /** @var \Drupal\import\Entity\ImporterInterface $entity */
    
    switch ($operation) {
      
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view published importer entities');
      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit importer entities');
      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete importer entities');
      case 'import':
        return AccessResult::allowedIfHasPermission($account, 'import importer entities');
    }
    
    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }
  
  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = null)
  {
    return AccessResult::allowedIfHasPermission($account, 'add importer entities');
  }
}
