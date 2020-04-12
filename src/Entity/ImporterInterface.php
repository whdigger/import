<?php

namespace Drupal\import\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface for defining Importer entities.
 *
 * @ingroup import
 */
interface ImporterInterface extends ContentEntityInterface, EntityChangedInterface
{
  /**
   * Gets the Importer name.
   *
   * @return string
   *   Name of the Importer.
   */
  public function getName();
  
  /**
   * Sets the Importer name.
   *
   * @param string $name
   *   The Importer name.
   *
   * @return \Drupal\import\Entity\ImporterInterface
   *   The called Importer entity.
   */
  public function setName($name);
  
  /**
   * Gets the Importer creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Importer.
   */
  public function getCreatedTime();
  
  /**
   * Sets the Importer creation timestamp.
   *
   * @param int $timestamp
   *   The Importer creation timestamp.
   *
   * @return \Drupal\import\Entity\ImporterInterface
   *   The called Importer entity.
   */
  public function setCreatedTime($timestamp);
  
}
