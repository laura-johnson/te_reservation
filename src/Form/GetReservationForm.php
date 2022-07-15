<?php

namespace Drupal\te_reservation\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\te_reservation\TEReservationEmailParserService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandler;

/**
 * Class GetReservationForm.
 */
class GetReservationForm extends FormBase {

  /**
   * Travel Engine Email Parser
   *
   * @var TEReservationEmailParserService
   */
  protected $teReservationEmailParser;

  /**
   * Module Handler
   *
   * @var ModuleHandler
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->teReservationEmailParser = $container->get('te_reservation.reservation_email_parser');
    $instance->messenger = $container->get('messenger');
    $instance->moduleHandler = $container->get('module_handler');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'get_reservation_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Get New Reservations'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get the path to the .eml files.
    $directory = $this->config('te_reservation.settings')->get('email_directory');
    $path = $this->moduleHandler->getModule('te_reservation')->getPath() . $directory;
    $files = glob($path . "/*.eml");
    // Send each file to the email parser service and display the parsed data.
    foreach ($files as $index => $file) {
      $reservation_items = $this->teReservationEmailParser->getReservationData($file);
      // This acts as a simple separator.
      $this->messenger->addMessage(t('Reservation @number', ['@number' => $index + 1]), TRUE);
      // Display the reservation data values.
      foreach ($reservation_items as $key => $value) {
        $this->messenger->addMessage(t('@key: @value', ['@key' => $key, '@value' => $value]), TRUE);
      }
    }
  }

}
