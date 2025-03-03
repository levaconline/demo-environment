<?php

class Lib extends Db
{
    public function __construct()
    {
        parent::__construct();
    }

    public function init() {}

    /**
     * Convert string with - or/and _ to cammel case.
     * Usuallu for generating class names.
     * Note: This is not perfect solution, but it is good for demo cases.
     * Please when you crate new section don't forget write cammel case class name. (use this method)
     */
    protected function toCamelCase(string $string): string
    {
        $string = str_replace(
            ' ',
            '',
            ucwords(
                str_replace(
                    ['_', '-'],
                    ' ',
                    $string
                )
            )
        );

        return $string;
    }
}
