<?php


class LexoRankException extends \Exception
{
    public function __construct($field1, $field2)
    {
        parent::__construct('The field ' . $field1 . ' is not equal to ' . $field2);
    }
}
