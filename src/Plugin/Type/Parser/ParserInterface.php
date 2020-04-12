<?php

namespace Drupal\import\Plugin\Type\Parser;

use Drupal\import\Entity\ImporterInterface;
use Drupal\import\Importer\Result\FetcherResultInterface;
use Drupal\import\Plugin\Type\ImporterPluginInterface;

/**
 * Interface ParserInterface
 * @package Drupal\import\Plugin\Type\Parser
 */
interface ParserInterface extends ImporterPluginInterface
{
  /**
   * @param ImporterInterface      $importer
   * @param FetcherResultInterface $fetcherResult
   *
   * @return mixed
   */
  public function parse(ImporterInterface $importer, FetcherResultInterface $fetcherResult);
  
  /**
   * @return array
   */
  public function getMappingSources();
}
