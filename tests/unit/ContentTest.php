<?php

namespace XH;

class ContentTest extends TestCase
{
    /**
     * @return void
     * 
     * @see https://github.com/cmsimple-xh/cmsimple-xh/issues/276
     */
    public function testInvalidUtf8IsNotSuppressed()
    {
        global $c, $s, $edit;

        $c = [0 => "<!--XH_ml1:Start-->\xC2"];
        $s = 0;
        $edit = false;
        $this->assertEquals("\xC2", content());
    }
}
