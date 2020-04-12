<?php

namespace Drupal\import;

use Drupal\import\Entity\ImporterInterface;
use Drupal\import\Importer\Item\ItemInterface;
use Drupal\import\Importer\Result\FetcherResultInterface;
use Drupal\import\Importer\Result\HttpFetcherResult;

/**
 * Class ImporterImportHandler
 * @package Drupal\import
 */
class ImporterImportHandler
{
  /**
   * @param ImporterInterface $importer
   *
   * @throws \Exception
   */
  public function import(ImporterInterface $importer)
  {
    $importer->lock();
    $fetcherResult = $this->fetch($importer);
    
    try {
      foreach ($this->parse($importer, $fetcherResult) as $item) {
        $this->entityHandler($importer, $item);
      }
      $importer->time_imported = \Drupal::service('datetime.time')->getRequestTime();
      $importer->save();
    } catch (\Exception $exception) {
      throw $exception;
    }
    
    $importer->unlock();
  }
  
  /**
   * @param ImporterInterface $importer
   *
   * @return mixed
   */
  protected function fetch(ImporterInterface $importer)
  {
    return $importer
      ->getType()
      ->getFetcher()
      ->fetch($importer);
  }
  
  /**
   * @param ImporterInterface      $importer
   * @param FetcherResultInterface $fetcherResult
   *
   * @return mixed
   */
  protected function parse(ImporterInterface $importer, FetcherResultInterface $fetcherResult)
  {
    return $importer
      ->getType()
      ->getParser()
      ->parse($importer, $fetcherResult);
  }
  
  /**
   * @param ImporterInterface $importer
   * @param ItemInterface     $item
   *
   * @return mixed
   */
  protected function entityHandler(ImporterInterface $importer, ItemInterface $item)
  {
    return $importer
      ->getType()
      ->getEntityHandler()
      ->handle($importer, $item);
  }
}

