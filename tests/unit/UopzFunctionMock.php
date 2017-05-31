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
class UopzFunctionMock extends FunctionMock
{
    /**
     * The formerly assigned function overload.
     *
     * @var string
     */
    protected $restore_closure = null;

    protected function restoreFunction()
    {
        if (isset($this->restore_closure)) {
            uopz_set_return($this->function_name, $this->restore_closure, true);
        } else {
            uopz_unset_return($this->function_name);
        }
    }

    protected function doCreateFunction()
    {
        $this->restore_closure = uopz_get_return($this->function_name);
        uopz_set_return($this->function_name, $this->getCallback(), true);
    }
}
