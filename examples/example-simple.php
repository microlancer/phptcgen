<?php

namespace Example1;

class Foo
{
    /**
     * @tc_takes xAsInteger, xAsString, xAsBoolean, xAsEmptyArray, xAsPopulatedArray, validGlobal, invalidGlobal
     * @uses nothing
     * @may throwBadGlobalException, throwBadInputException
     */
    public function multiplyByGlobalPi($x)
    {
        global $pi;
        if (!isset($pi) || !is_numeric($pi)) {
            throw new \Exception("Invalid value for PI in global.");
        }
        if (!is_numeric($x)) {
            throw new \Exception("Invalid parameter for x, must be a number.");
        }
        return $x * $pi;
    }
}

