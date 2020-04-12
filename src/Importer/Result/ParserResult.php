<?php

namespace Drupal\import\Importer\Result;

use Drupal\import\Importer\Item\ItemInterface;

class ParserResult extends \SplDoublyLinkedList implements ParserResultInterface
{
  /**
   * {@inheritdoc}
   */
  public function addItem(ItemInterface $item)
  {
    $this->push($item);
    
    return $this;
  }
}
