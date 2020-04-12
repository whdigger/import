<?php

namespace Drupal\import\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\import\Plugin\Type\EntityHandler\EntityHandlerInterface;
use Drupal\import\Plugin\Type\Fetcher\FetcherInterface;
use Drupal\import\Plugin\Type\Parser\ParserInterface;
use Drupal\import\Plugin\Type\PluginBase;

/**
 * Interface ImporterTypeInterface
 * @package Drupal\import\Entity
 */
interface ImporterTypeInterface extends ConfigEntityInterface
{
  /**
   * @return PluginBase[]
   */
  public function getPlugins();
  
  /**
   * @return FetcherInterface
   */
  public function getFetcher();
  
  /**
   * @return ParserInterface
   */
  public function getParser();
  
  /**
   * @return EntityHandlerInterface
   */
  public function getEntityHandler();
  
  /**
   * @return string
   */
  public function getEntityTypeMaterial(): string;
  
  /**
   * @return int
   */
  public function getTypeMaterial(): string;
  
  /**
   * @return string
   */
  public function getSourcesFieldByTarget($target): string;
}
