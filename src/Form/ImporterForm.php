<?php

namespace Drupal\import\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\import\Plugin\PluginFormFactory;
use Drupal\import\Plugin\Type\ImporterPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Importer edit forms.
 *
 * @ingroup import
 */
class ImporterForm extends ContentEntityForm
{
  /**
   * @var PluginFormFactory
   */
  protected $pluginFormFactory;
  /**
   * @var string
   */
  private $formAction = 'import';
  /**
   * @var string
   */
  private $formType = 'fetcher';
  /**
   * @var string
   */
  private $formName = 'importer';

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->pluginFormFactory = $container->get('plugin.import.form_factory');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $formState)
  {
    $form = parent::form($form, $formState);

    $importer = $this->entity;
    $importerType = $importer->getType();

    $importForm = $this->getImportForm($importerType->getFetcher());
    if (!$importForm) {
      return $form;
    }

    $pluginFormState = (new FormState())->setValues(
      $formState->getValue([$this->formType], [])
    );

    $form[$this->formType] = $importForm->buildConfigurationForm([], $pluginFormState, $importer);
    $form[$this->formType]['#tree'] = true;

    $formState->setValue([$this->formType], $pluginFormState->getValues());


    return $form;
  }

  /**
   * @param array              $form
   * @param FormStateInterface $formState
   *
   * @return array|\Drupal\Core\Entity\ContentEntityInterface|\Drupal\Core\Entity\ContentEntityTypeInterface|void
   */
  public function validateForm(array &$form, FormStateInterface $formState)
  {
    if ($formState->getErrors()) {
      return;
    }

    $importer = $this->entity;
    $importerType = $importer->getType();

    $importForm = $this->getImportForm($importerType->getFetcher());
    if (!$importForm) {
      return;
    }

    $pluginFormState = (new FormState())->setValues(
      $formState->getValue([$this->formType], [])
    );

    $importForm->validateConfigurationForm($form[$this->formType], $pluginFormState, $importer);
    $formState->setValue([$this->formType], $pluginFormState->getValues());

    foreach ($pluginFormState->getErrors() as $name => $error) {
      $formState->setErrorByName($name, $error);
    }

    parent::validateForm($form, $formState);
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $formState)
  {
    parent::submitForm($form, $formState);

    $importer = $this->entity;
    $importerType = $importer->getType();

    $importForm = $this->getImportForm($importerType->getFetcher());
    if (!$importForm) {
      return;
    }

    $pluginFormState = (new FormState())->setValues(
      $formState->getValue([$this->formType], [])
    );

    $importForm->submitConfigurationForm($form[$this->formType], $pluginFormState, $importer);
    $formState->setValue([$this->formType], $pluginFormState->getValues());
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state)
  {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Importer.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Importer.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.importer.canonical', [$this->formName => $entity->id()]);
  }

  /**
   * @param ImporterPluginInterface $plugin
   * @param                         $action
   *
   * @return bool
   */
  protected function pluginHasForm(ImporterPluginInterface $plugin, $action)
  {
    return $this->pluginFormFactory->hasForm($plugin, $action);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $formState)
  {
    $element = parent::actions($form, $formState);

    $element['delete']['#access'] = $this->entity->access('delete');

    return $element;
  }

  /**
   * @param $plugin
   *
   * @return bool|object
   */
  private function getImportForm($plugin)
  {
    if (!$this->pluginHasForm($plugin, $this->formAction)) {
      return false;
    }

    return $this->pluginFormFactory->createInstance($plugin, $this->formAction);
  }
}
