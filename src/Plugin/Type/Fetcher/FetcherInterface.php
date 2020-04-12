<?php

namespace Drupal\import\Plugin\Type\Fetcher;

use Drupal\import\Entity\ImporterInterface;
use Drupal\import\Plugin\Type\ImporterPluginInterface;

/**
 * Interface FetcherInterface
 * @package Drupal\import\Plugin\Type\Fetcher
 */
interface FetcherInterface extends ImporterPluginInterface
{
  /**
   * @param ImporterInterface $importer
   *
   * @return mixed
   */
  public function fetch(ImporterInterface $importer);
}
