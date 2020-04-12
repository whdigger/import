<?php

namespace Drupal\import\Importer\Result;

/**
 * Interface FetcherResultInterface
 * @package Drupal\import\Importer\Result
 */
interface FetcherResultInterface
{
  public function getContent();
  
  public function getFilePath(): string;
  
}
