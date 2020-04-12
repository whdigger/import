<?php

namespace Drupal\import\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ImporterImportForm
 * @package Drupal\import\Form
 */
class ImporterImportForm extends ContentEntityConfirmFormBase
{
  /**
   * {@inheritdoc}
   */
  public function getQuestion()
  {
    return $this->t('Are you sure you want to import task %task?', ['%task' => $this->entity->label()]);
  }
  
  /**
   * {@inheritdoc}
   */
  public function getCancelUrl()
  {
    return $this->entity->toUrl();
  }
  
  /**
   * {@inheritdoc}
   */
  public function getConfirmText()
  {
    return $this->t('Import');
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $this->entity->startImport();
    $form_state->setRedirectUrl($this->getCancelUrl());
  }
  
}
