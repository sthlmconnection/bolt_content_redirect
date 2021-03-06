<?php

namespace Bolt\Extension\SthlmConnection\ContentRedirect;

class Redirect {

  static $dbConnection;
  static $tableName;
  static $validCodes = [301, 302];

  public $source;
  public $contentId;
  public $contentType;
  public $code = null;

  public function __construct($values = []) {
    foreach (['source', 'contentId', 'contentType', 'code'] as $key) {
      if (!empty($values[$key])) {
        $this->{$key} = $values[$key];
      }
    }
  }

  static function load($source) {
    $redirect = null;
    $query = 'SELECT * FROM ' . self::$tableName . ' WHERE source = ?';
    $record = self::$dbConnection->fetchAssoc($query, array($source));

    if (!empty($record)) {
      $values = [
        'source' => $record['source'],
        'contentId' => $record['content_id'],
        'contentType' => $record['content_type'],
        'code' => $record['code'],
      ];
      $redirect = new self($values);
    }

    return $redirect;
  }

  static function exists($source) {
    $existing = self::load($source);
    return !empty($existing);
  }

  public function save() {
    $this->assertValidCode();

    $values = array(
      'source' => $this->source,
      'content_type' => $this->contentType,
      'content_id' => $this->contentId,
      'code' => $this->code,
    );

    if (self::exists($this->source)) {
      $result = self::$dbConnection->update(self::$tableName, $values, ['source' => $this->source]);
    } else {
      $result = self::$dbConnection->insert(self::$tableName, $values);
    }

    return !!$result;
  }

  public function assertValidCode() {
    if ($this->code != null && !in_array($this->code, self::$validCodes)) {
      throw new \InvalidArgumentException('Code must be either null, 301 or 302.');
    }
  }

}
