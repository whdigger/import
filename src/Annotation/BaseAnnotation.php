<?php

namespace Drupal\import\Annotation;

use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Class BaseAnnotation
 * @package Drupal\import\Annotation
 */
abstract class BaseAnnotation extends Plugin
{
  /**
   * @var string
   */
  public $id;
  
  /**
   * @var Translation
   */
  public $title;
  
  /**
   * @var Translation
   */
  public $description;
}
