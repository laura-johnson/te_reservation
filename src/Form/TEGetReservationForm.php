<?php

namespace Drupal\te_reservation\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\te_reservation\TEReservationEmailParserService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandler;

/**
 * A simple form with a submit button that demonstrates
 * using the Travel Engine Email Parser service.
 */
class TEGetReservationForm extends FormBase {

  /**
   * Travel Engine Email Parser
   *
   * @var TEReservationEmailParserService
   */
  protected $teReservationEmailParser;

  /**
   * Messenger
   *
   * @var Messenger
   */
  protected $messenger;

  /**
   * Module Handler
   *
   * @var ModuleHandler
   */
  protected $moduleHandler;

  /**
   * @param TEReservationEmailParserService $reservation_email_parser
   * @param Messenger $messenger
   * @param ModuleHandler $module_handler
   */
  public function __construct(TEReservationEmailParserService $reservation_email_parser, Messenger $messenger, ModuleHandler $module_handler) {
    $this->teReservationEmailParser = $reservation_email_parser;
    $this->messenger = $messenger;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('te_reservation.reservation_email_parser'),
      $container->get('messenger'),
      $container->get('module_handler'),
    );
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
    // Get the path to the email directory.
    $directory = $this->config('te_reservation.settings')->get('email_directory');
    $path = $this->moduleHandler->getModule('te_reservation')->getPath() . $directory;
    // The glob() function searches for all the pathnames matching a pattern
    // Use it to get .eml file paths in the chosen directory.
    if (!$file_paths = glob($path . "/*.eml")) {
      $this->messenger->addError(t('Files with .eml extension are not present at @path.', ['@path' => $path]));
    }
    // Send each file to the email parser service and display the parsed data.
    foreach ($file_paths as $index => $file_path) {
      if ($reservation_items = $this->teReservationEmailParser->getReservationData($file_path)) {
        // Itemize the results as a simple separator.
        $this->messenger->addMessage(t('Reservation @number', ['@number' => $index + 1]), TRUE);
        // Display the reservation data values as a message.
        foreach ($reservation_items as $key => $item) {
          $this->messenger->addMessage(t('@key: @item', ['@key' => $key, '@item' => $item]), TRUE);
        }
      }
      else {
        $this->messenger->addError(t('File @file_path is malformed or is not an .eml file.', ['@file_path' => $file_path]));
      }
    }
  }

}
