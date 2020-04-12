<?php

namespace Drupal\import\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Importer entities.
 */
class ImporterViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();
    return $data;
  }

}
