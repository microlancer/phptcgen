<?php

namespace Example2;

use \Example1;

class Example2
{
    /**
     * @comment_tc_takes Here, we specify the input cases which we want to generate test cases for.
     * @tc_takes xAsTrue xAsFalse xAsNonBoolean
     *
     * @comment_tc_uses Here, we specify methods (dependencies) that will be fetched in planning the test cases.
     * @tc_uses \Example1::multiplyByGlobalPi
     *
     * @comment_tc_may Here, we specify outcomes that you feel are relevant. There may be tons of outcomes 
     * (out of memory, disk failure, exceptions, return values, etc.) but only the ones that you feel are worthy and
     * necessary for any user of this method to write test cases for should be included here.
     * @tc_may returnTrue returnFalse
     */
    public function foo($x)
    {
        if ($x) {
            $example1 = new Example1;
            return $example1->multiplyByGlobalPi(1000);
        }
        return false;
    }
}