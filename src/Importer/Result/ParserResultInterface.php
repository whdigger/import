<?php

namespace Drupal\import\Importer\Result;

use Drupal\import\Importer\Item\ItemInterface;

/**
 * Interface ParserResultInterface
 * @package Drupal\import\Importer\Result
 */
interface ParserResultInterface extends \Iterator, \ArrayAccess, \Countable
{
  public function addItem(ItemInterface $item);
}
