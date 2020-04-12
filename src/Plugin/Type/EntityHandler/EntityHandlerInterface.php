<?php

namespace Drupal\import\Plugin\Type\EntityHandler;

use Drupal\import\Entity\ImporterInterface;
use Drupal\import\Importer\Item\ItemInterface;
use Drupal\import\Plugin\Type\ImporterPluginInterface;

/**
 * Interface EntityHandlerInterface
 * @package Drupal\import\Plugin\Type\EntityHandler
 */
interface EntityHandlerInterface extends ImporterPluginInterface
{
  /**
   * @param ImporterInterface $importer
   *
   * @return mixed
   */
  public function handle(ImporterInterface $importer, ItemInterface $item);
}
