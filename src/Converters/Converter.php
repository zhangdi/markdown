<?php


namespace App\Converters;


abstract class Converter
{
    /**
     * @param string $sourceContent
     * @return mixed
     */
    abstract public function convert($sourceContent);
}
