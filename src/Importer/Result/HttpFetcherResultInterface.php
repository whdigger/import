<?php

namespace Drupal\import\Importer\Result;

interface HttpFetcherResultInterface extends FetcherResultInterface
{
  /**
   * @return array
   */
  public function getHeaders(): array;
}
