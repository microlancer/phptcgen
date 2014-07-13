<?php

namespace Example3;

use \Example1;
use \Example2;

class Example3
{
    /**
     * @tc_uses \Example2\Example2::foo, \Example2\Example2::bar
     * @tc_may workJustFine, blowUpIntoPieces
     */
    public function baz()
    {
        // ... some code that uses foo and bar ...
    }
}