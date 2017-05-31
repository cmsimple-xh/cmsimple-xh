<?php

namespace XH;

/**
 * Extension for PHPUnit that makes MockObject-style expectations possible for global functions (even PECL functions).
 *
 * Assimilated from <https://github.com/tcz/phpunit-mockfunction/blob/3cf5ea8/PHPUnit/Extensions/MockFunction.php>.
 *
 * @author zoltan.tothczifra
 * @author The CMSimple_XH developers
 */
class RunkitFunctionMock extends FunctionMock
{
    /**
     * Random temporary name of a function there we "save" the original, unmocked function.
     *
     * @var string
     */
    protected $restore_name;

    protected function restoreFunction()
    {
        runkit_function_remove($this->function_name);
        if (isset($this->restore_name)) {
            runkit_function_rename($this->restore_name, $this->function_name);
        }
    }

    protected function doCreateFunction()
    {
        $this->restore_name = 'restore_' . $this->function_name . '_' . $this->id . '_' . uniqid();

        runkit_function_copy($this->function_name, $this->restore_name);
        runkit_function_redefine($this->function_name, $this->getCallback());
    }
}
