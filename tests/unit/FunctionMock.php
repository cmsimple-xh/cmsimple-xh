<?php

namespace XH;

/**
 * Extension for PHPUnit that makes MockObject-style expectations possible for global functions (even PECL functions).
 *
 * Assimilated from <https://github.com/tcz/phpunit-mockfunction/blob/3cf5ea8/PHPUnit/Extensions/MockFunction.php>.
 *
 * @author zoltan.tothczifra
 */
class FunctionMock
{
    /**
     * Incremental ID of the current object instance to be able to find it.
     *
     * @see self::$instances
     * @var integer
     */
    protected $id;

    /**
     * Flag to tell if the function mocking is active or not (replacement is in place).
     *
     * @var boolean
     */
    protected $active = false;

    /**
     * Standard PHPUnit MockObject used to test invocations of the mocked function.
     *
     * @var object
     */
    protected $mock_object;

    /**
     * The name of the original function that gets mocked.
     *
     * @var string
     */
    protected $function_name;

    /**
     * Random temporary name of a funstion there we "save" the original, unmocked function.
     *
     * If the function did not exist before mocking, it's empty.
     *
     * @var string
     */
    protected $restore_name;

    /**
     * Value of the incremental ID that next time will be assigned to an instance of this class.
     *
     * @var integer
     */
    protected static $next_id = 1;

    /**
     * List of active mock object instances (those that are not restored) with their ID as key.
     *
     * @var FunctionMock[]
     */
    protected static $instances = array();

    /**
     * Constructor setting up object.
     *
     * @param string $function_name Name of the function to mock. Doesn't need to exist, might be newly created.
     * @param object $testcase The calling test case.
     */
    public function __construct($function_name, $testcase)
    {
        if (!function_exists('runkit_function_redefine')) {
            trigger_error('Runkit is not installed.', E_USER_ERROR);
        }
        
        $this->id               = self::$next_id;
        $this->function_name    = $function_name;
        $this->mock_object      =
            $testcase->getMockBuilder(
                'Mock_' . str_replace('::', '__', $this->function_name) . '_' . $this->id
            )
            ->disableAutoload()
            ->setMethods(array('invoked'))
            ->getMock();

        ++self::$next_id;
        self::$instances[$this->id] = $this;

        $this->createFunction();
    }

    /**
     * Called when all the referneces to the object are removed (even self::$instances).
     *
     * Makes sure the replaced functions are finally cleared in case runkit
     * "forgets" to remove them in the end of the request.
     * It is still highly recommended to call restore() explicitly!
     */
    public function __destruct()
    {
        $this->restore();
    }

    /**
     * Clean-up function.
     *
     * Removes mocked function and restored the original was there is any.
     * Also removes the reference to the object from self::$instances.
     */
    public function restore()
    {
        if ($this->active) {
            runkit_function_remove($this->function_name);
            if (isset($this->restore_name)) {
                runkit_function_rename($this->restore_name, $this->function_name);
            }
            $this->active = false;
        }

        if (isset(self::$instances[$this->id])) {
            unset(self::$instances[$this->id]);
        }
    }

    /**
     * Callback method to be used in runkit function when it is invoked.
     *
     * It takes the parameters of the function call and passes them to the mock object.
     *
     * @param type $arguments 0-indexed array of arguments with which the mocked function was called.
     * @return mixed
     */
    public function invoked(array $arguments)
    {
        return call_user_func_array(array($this->mock_object, __FUNCTION__), $arguments);
    }
    
    /**
     * Proxy to the 'expects' of the mock object.
     *
     * Also calld method() so after this the mock object can be used to set
     * parameter constraints and return values.
     *
     * @return object
     */
    public function expects()
    {
        $arguments = func_get_args();
        return call_user_func_array(array($this->mock_object, __FUNCTION__), $arguments)->method('invoked');
    }

    /**
     * Returns an instance of this class selected by its ID. Used in the runkit function.
     *
     * @param integer $id
     * @return object
     */
    public static function findMock($id)
    {
        if (!isset(self::$instances[$id])) {
            throw new Exception('Mock object not found, might be destroyed already.');
        }
        return self::$instances[$id];
    }

    /**
     * Creates runkit function to be used for mocking, taking care of callback to this object.
     *
     * Also temporary renames the original function if there is.
     */
    protected function createFunction()
    {
        if (function_exists($this->function_name)) {
            $this->restore_name = 'restore_' . $this->function_name . '_' . $this->id . '_' . uniqid();

            runkit_function_copy($this->function_name, $this->restore_name);
            runkit_function_redefine($this->function_name, $this->getCallback());
        } else {
            trigger_error(
                sprintf('Cannot stub or mock function "%s" which does not exist', $this->function_name),
                E_USER_NOTICE
            );
        }

        $this->active = true;
    }

    /**
     * Gives back the closure of the runkit function replacing the original.
     *
     * The function is quite simple - find the function mock instance (of this class)
     * that created it, then calls its invoked() method with the parameters of its invokation.
     *
     * @return callable
     */
    protected function getCallback()
    {
        $className = __CLASS__;
        $id = $this->id;
        return function () use ($className, $id) {
            $mock = $className::findMock($id);
            return $mock->invoked(func_get_args());
        };
    }
}
