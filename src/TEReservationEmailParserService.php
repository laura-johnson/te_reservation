<?php

namespace Drupal\te_reservation;

use PhpMimeMailParser\Parser;

class TEReservationEmailParserService {

  /**
   * The email parser.
   *
   * @var Parser $parser
   */
  protected $parser;

  /**
   * TEReservationEmailParserService constructor.
   */
  public function __construct() {
    $this->parser = new Parser();
  }

  /**
   * Get airline name. Parses the 'from' header
   * returning everything before the email address,
   * which should be the airline name if this pattern holds.
   *
   * @param string $path_to_file
   *  The path to the .eml file
   *
   * @return array
   */
  public function getReservationData(string $path_to_file) : array {
    $this->parser->setText(file_get_contents($path_to_file));

    $reservation_data = [];
    $reservation_data['airline'] = t('@airline', ['@airline' => $this->getAirlineName()]);
    $reservation_data['record_locator'] = t('@record_locator', [
      '@record_locator' => $this->getBodyParam('recordLocator')
    ]);
    $reservation_data['passenger_name'] = t('@first_name @last_name', [
      '@first_name' => $this->getBodyParam('firstName'),
      '@last_name' => $this->getBodyParam('lastName')
    ]);
    return $reservation_data;
  }

  /**
   * Get airline name. Parses the 'from' header
   * returning everything before the email address,
   * which should be the airline name if the format holds.
   *
   * @return string
   */
  protected function getAirlineName() : string {
    $header_from = $this->parser->getHeader('from');
    $parts = explode('<', $header_from);
    return $parts[0];
  }

  /**
   * Get a parameter from the body. The airline email contains a query string
   * with the parameters firstName, lastName, and recordLocator.
   * Use one of these to get the corresponding value.
   *
   * @param string $param
   *  Name of the parameter to get.
   *
   * @return string
   */
  protected function getBodyParam(string $param) : string {
    $body = $this->parser->getMessageBody('html');
    $split = preg_split("/{$param}=/",$body);
    $result = explode('&', $split[1]);
    return $result[0];
  }

}
