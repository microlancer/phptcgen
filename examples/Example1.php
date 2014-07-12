<?php

class Example1
{
    /**
     * @tc_takes xAsInteger, xAsString, xAsBoolean, xAsEmptyArray, xAsPopulatedArray, validGlobal, invalidGlobal
     * @tc_uses nothing
     * @tc_may throwBadGlobalException, throwBadInputException
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

