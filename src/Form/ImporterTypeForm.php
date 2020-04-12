<?php

namespace Drupal\import\Form;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\field\Entity\FieldConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ImporterTypeForm
 * @package Drupal\import\Form
 */
class ImporterTypeForm extends EntityForm
{
  /**
   * @var EntityStorageInterface
   */
  protected $importerTypeStorage;
  
  /**
   * @var DateFormatterInterface
   */
  protected $dateFormatter;
  
  /**
   * @var RendererInterface
   */
  protected $renderer;
  
  /**
   * @var EntityFieldManagerInterface
   */
  protected $entityFieldManager;
  
  /**
   * ImporterTypeForm constructor.
   *
   * @param ConfigEntityStorageInterface $importerTypeStorage
   * @param DateFormatterInterface       $dateFormatter
   * @param RendererInterface            $renderer
   * @param EntityFieldManagerInterface  $entityFieldManager
   */
  public function __construct(
    ConfigEntityStorageInterface $importerTypeStorage,
    DateFormatterInterface $dateFormatter,
    RendererInterface $renderer,
    EntityFieldManagerInterface $entityFieldManager
  ) {
    $this->importerTypeStorage = $importerTypeStorage;
    $this->dateFormatter = $dateFormatter;
    $this->renderer = $renderer;
    $this->entityFieldManager = $entityFieldManager;
  }
  
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('entity_type.manager')->getStorage('importer_type'),
      $container->get('date.formatter'),
      $container->get('renderer'),
      $container->get('entity_field.manager')
    );
  }
  
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $formState)
  {
    $importerType = $this->entity;
    $form['importer'] = [
      '#type'       => 'container',
      '#attributes' => [
        'id' => 'importer',
      ],
    ];
    
    $form['importer']['label'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Name'),
      '#maxlength'     => 255,
      '#default_value' => $importerType->label(),
      '#description'   => $this->t("Name Importer type."),
      '#required'      => true,
    ];
    
    $form['importer']['id'] = [
      '#type'          => 'machine_name',
      '#default_value' => $importerType->id(),
      '#machine_name'  => [
        'exists' => '\Drupal\import\Entity\ImporterType::load',
        'source' => ['importer', 'label'],
      ],
      '#disabled'      => !$importerType->isNew(),
      '#required'      => true,
    ];
    
    $options = $this->getEntityTypeMaterialOptions($importerType->getTypeMaterial());
    if ($options) {
      $form['importer']['entityTypeMaterial'] = [
        '#type'          => 'select',
        '#title'         => $this->t('Choose entity material'),
        '#options'       => $options,
        '#empty_option'  => $this->t('- Select a entity type -'),
        '#required'      => true,
        '#default_value' => $importerType->getEntityTypeMaterial(),
        '#ajax'          => [
          'callback' => [$this, 'getMappingsFieldAjaxForm'],
          'wrapper'  => 'importer',
          'event'    => 'change',
        ],
      ];
    }
    
    $changeEntityTypeMaterial = $formState->getValue('entityTypeMaterial', $importerType->getEntityTypeMaterial());
    
    if ($changeEntityTypeMaterial) {
      $form['importer']['mappings'] = [
        '#type'        => 'fieldset',
        '#title'       => t('Mappings fields'),
        '#description' => t('These fields are required for data parsing'),
        '#collapsible' => false,
        '#collapsed'   => false,
      ];
      
      $entityTypeFields = $this->getEntityTypeFields($importerType->getTypeMaterial(), $changeEntityTypeMaterial);
      $mappingSourcesField = $importerType->getParser()->getMappingSources()['fields'];
      
      $requiredFieldsMapping = $this->getFormMappingFields($entityTypeFields['required'], $mappingSourcesField, true);
      $otherFieldsMapping = $this->getFormMappingFields($entityTypeFields['fields'], $mappingSourcesField, false);
      
      $targetPrimaryKey = [
        'primaryKey' => [
          '#type'          => 'select',
          '#title'         => $this->t('Uniq field for insert'),
          '#options'       => $mappingSourcesField,
          '#required'      => true,
          '#empty_option'  => $this->t('- Select a source -'),
          '#empty_value'   => '',
          '#default_value' => $importerType->getPrimaryKey(),
        ],
      ];
      
      if (!$this->entity->isNew()) {
        $targetPrimaryKey['primaryKey']['#disabled'] = true;
      }
      
      $form['importer'] +=
        $targetPrimaryKey +
        $requiredFieldsMapping +
        $otherFieldsMapping;
    }
    
    return parent::form($form, $formState);
  }
  
  /**
   * @param array              $form
   * @param FormStateInterface $formState
   *
   * @return array|mixed
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getMappingsFieldAjaxForm(array &$form, FormStateInterface $formState)
  {
    return $form['importer'];
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $formState)
  {
    $mappings = [];
    foreach ($formState->getValues() as $key => $value) {
      $findPattern = 'map_';
      if (strpos($key, $findPattern) !== false && !empty($value)) {
        $mappings[substr($key, strlen($findPattern))] = $value;
      }
    }
    
    $formState->setValue('mappings', $mappings);
    parent::submitForm($form, $formState);
  }
  
  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $formState)
  {
    $importerType = $this->entity;
    $status = $importerType->save();
    
    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Importer type.', [
          '%label' => $importerType->label(),
        ]));
        break;
      
      default:
        $this->messenger()->addMessage($this->t('Saved the %label Importer type.', [
          '%label' => $importerType->label(),
        ]));
    }
    $formState->setRedirect('entity.importer_type.edit_form', ['importer_type' => $this->entity->id()]);
  }
  
  /**
   * @param $fields
   * @param $required
   *
   * @return array
   */
  private function getFormMappingFields($fields, $options, $required)
  {
    $form = [];
    
    foreach ($fields as $fieldName => $label) {
      $form["map_${fieldName}"] = [
        '#type'          => 'select',
        '#title'         => $this->t('%label (%fieldName)', ['%label' => $label, '%fieldName' => $fieldName]),
        '#options'       => $options,
        '#required'      => $required,
        '#default_value' => $this->entity->getSourcesFieldByTarget($fieldName),
        '#empty_option'  => $this->t('- Select a source -'),
        '#empty_value'   => '',
      ];
    }
    
    return $form;
  }
  
  /**
   * @param string $entityType
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getEntityTypeMaterialOptions(string $entityType): array
  {
    $options = [];
    $entity = $this->entityTypeManager->getDefinition($entityType);
    
    if ($entity && $type = $entity->getBundleEntityType()) {
      $types = $this->entityTypeManager->getStorage($type)->loadMultiple();
      
      if (!empty($types) && is_array($types)) {
        foreach ($types as $type) {
          $options[$type->id()] = $type->label();
        }
      }
    }
    
    return $options;
  }
  
  /**
   * @param string $typeMaterial
   * @param string $entityTypeBundle
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getEntityTypeFields(string $typeMaterial, string $entityTypeBundle)
  {
    $fields = [];
    
    $entityFields = $this->entityFieldManager->getFieldDefinitions($typeMaterial, $entityTypeBundle);
    $entityDefinition = $this->entityTypeManager->getDefinition($typeMaterial);
    
    foreach ($entityFields as $entityField) {
      if ($entityField->isReadOnly() || $entityField->getName() === $entityDefinition->getKey('bundle')) {
        continue;
      }
      
      if ($entityField->isRequired()) {
        $fields['required'][$entityField->getName()] = $entityField->getLabel();
      } elseif ($entityField instanceof FieldConfig) {
        $fields['fields'][$entityField->getName()] = $entityField->getLabel();
      }
    }
    
    $fields['fieldsAll'] = array_merge($fields['required'], $fields['fields']);
    
    return $fields;
  }
}
