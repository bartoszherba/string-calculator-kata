<?php
declare(strict_types=1);

namespace Bartoszek\StringCalculator;

class Calculator {

  const DELIMITER_PATTERN = '/^\/\/(\[?[^\[\]]+\]?)/';

  protected $delimiters = ['\,','\\\n'];

  public function add(string $string)
  {
    if ('' === $string) {
      return 0;
    }

    if ($this->hasCustomDelimiter($string)) {
      list($rawDelimiter, $string) = $this->disconnectDelimiterAndArgs($string);
      $delimiters = $this->extractDelimiters($rawDelimiter);
      $this->addDelimiters($delimiters);
    }

    $args = $this->extractArguments($string);
    $negativeArgs = $this->extractNegativeArgs($args);

    if ($this->hasNegativeArgs($negativeArgs)) {
      throw new \Exception(join(',',$negativeArgs));
    }

    $result = $this->sumArgs($args);

    return $result;
  }

  protected function hasNegativeArgs(array $negativeArgs): bool
  {
    return (count($negativeArgs) > 0);
  }

  protected function extractNegativeArgs(array $args): array
  {
    $negativeArgs = [];

    foreach($args as $arg) {
      if ((int)$arg < 0) {
        $negativeArgs[] = $arg;
      }
    }

    return $negativeArgs;
  }

  protected function disconnectDelimiterAndArgs(string $string): array
  {
    return explode('\n', $string);
  }

  protected function extractDelimiters(string $rawDelimiter): array
  {
    $delimiters = [];
    do {
      preg_match(self::DELIMITER_PATTERN, $rawDelimiter, $matches);
      if (isset($matches[1])) {
        $delimiter = trim($matches[1],'[]');
        $delimiters[] = $delimiter;
        $rawDelimiter = str_replace($matches[1],'',$rawDelimiter);
      }

    } while (isset($matches[1]));


    return $delimiters;
  }

  protected function addDelimiters(array $delimiters): Calculator
  {
    foreach($delimiters as $delimiter) {
      $this->delimiters[] = $delimiter;
    }

    return $this;
  }

  protected function extractArguments(string $string): array
  {
    $delimiters = join($this->delimiters);
    $pattern = "/[{$delimiters}]/";
    $args = preg_split($pattern,$string);
    $args = array_filter($args, function($arg) {
      return ($arg <= 1000);
    });

    return $args;
  }

  protected function hasCustomDelimiter(string $string): bool
  {
    return (false !== strpos($string,'//'));
  }

  protected function sumArgs(array $args): string
  {
    return (string)array_sum($args);
  }
}
