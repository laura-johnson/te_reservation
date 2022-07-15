<?php

namespace Drupal\te_reservation;

use PhpMimeMailParser\Parser;

class TEReservationEmailParserService {

  /**
   * The PhpMimeMailParser email parser.
   *
   * @var Parser
   */
  protected $parser;

  /**
   * TEReservationEmailParserService constructor.
   */
  public function __construct() {
    $this->parser = new Parser();
  }

  /**
   * Get an array of reservation data using a file path to an .eml file.
   *
   * @param string $file_path
   *  The path to the .eml file
   *
   * @return array
   */
  public function getReservationData(string $file_path) : array {
    // Make sure we have an .eml file.
    $extension = pathinfo($file_path, PATHINFO_EXTENSION);
    if ($extension != 'eml') {
      return [];
    }

    // Get the file contents and add them to the email parser.
    $this->parser->setText(file_get_contents($file_path));

    // Create reservation data array.
    $reservation_data = [];
    // Get airline name.
    $reservation_data['airline'] = $this->getAirlineName();
    // Get record locator.
    $reservation_data['record_locator'] = $this->getBodyParam('recordLocator');
    // Get passenger first and last names, concatenate name string with t(),
    // make lower case with uppercase first letter.
    $reservation_data['passenger_name'] = t('@first_name @last_name', [
      '@first_name' => ucfirst(strtolower($this->getBodyParam('firstName'))),
      '@last_name' => ucfirst(strtolower($this->getBodyParam('lastName'))),
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
    $result = explode('<', $header_from);
    return $result[0];
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
