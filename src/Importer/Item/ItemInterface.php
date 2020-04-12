<?php

namespace Drupal\import\Importer\Item;

/**
 * Interface ItemInterface
 * @package Drupal\import\Importer\Item
 */
interface ItemInterface
{
  /**
   * @param $field
   *
   * @return mixed
   */
  public function get($field);
  
  /**
   * @param $field
   * @param $value
   *
   * @return mixed
   */
  public function set($field, $value);
  
  /**
   * @return array
   */
  public function toArray();
  
}
