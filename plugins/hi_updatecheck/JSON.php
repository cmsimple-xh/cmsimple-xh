<?php

/**
 * Handle encoding and decoding of JSON from resp. to native PHP data structures.
 *
 * @package   CMB
 * @copyright Copyright (c) 2012-2013 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   $Id: JSON.php 116 2013-05-29 17:54:57Z hi $
 */


/**
 * End of string has been reached.
 *
 * @access private
 */
define('CMB_JSON_EOS', -1);

/**
 * Unknown terminal symbol.
 *
 * @access private
 */
define('CMB_JSON_UNKNOWN', 0);

/**
 * <kbd>NUMBER</kbd>
 *
 * @access private
 */
define('CMB_JSON_NUMBER', 1);

/**
 * <kbd>STRING</kbd>
 *
 * @access private
 */
define('CMB_JSON_STRING', 2);

/**
 * <kbd>LBRACE</kbd>
 *
 * @access private
 */
define('CMB_JSON_LBRACE', 3);

/**
 * <kbd>RBRACE</kbd>
 *
 * @access private
 */
define('CMB_JSON_RBRACE', 4);

/**
 * <kbd>LBRACK</kbd>
 *
 * @access private
 */
define('CMB_JSON_LBRACK', 5);

/**
 * <kbd>RBRACK</kbd>
 *
 * @access private
 */
define('CMB_JSON_RBRACK', 6);

/**
 * <kbd>COMMA</kbd>
 *
 * @access private
 */
define('CMB_JSON_COMMA', 7);

/**
 * <kbd>COLON</kbd>
 * @access private
 */
define('CMB_JSON_COLON', 8);

/**
 * <kdb>TRUE</kbd>
 *
 * @access private
 */
define('CMB_JSON_TRUE', 9);

/**
 * <kbd>FALSE</kbd>
 *
 * @access private
 */
define('CMB_JSON_FALSE', 10);

/**
 * <kbd>NULL</kbd>
 *
 * @access private
 */
define('CMB_JSON_NULL', 11);


/**
 * Handles encoding and decoding of JSON from resp. to native PHP data structures.
 *
 * It is a <i>simplified</i> alternative to <kbd>json_encode()</kbd> and <kbd>json_decode()</kbd>
 * that can be used as fallback for ancient PHP versions.
 *
 * @package CMB
 */
class CMB_JSON
{
    /**
     * The set of "first" tokens.
     *
     * @access private
     *
     * @var array
     */
    var $first;

    /**
     * The string to parse.
     *
     * Already parsed parts will be truncated.
     *
     * @access private
     *
     * @var string
     */
    var $str;

    /**
     * The current token.
     *
     * @access private
     *
     * @var int
     */
    var $sym;

    /**
     * The current value (for strings and numbers).
     *
     * @access private
     *
     * @var mixed
     */
    var $value;

    /**
     * Error flag.
     *
     * @access private
     *
     * @var bool
     */
    var $error;

    /**
     * Constructor.
     *
     * @access private
     */
    function CMB_JSON()
    {
        $this->first = array(
            'object' => array(CMB_JSON_LBRACE),
            'pair' => array(CMB_JSON_STRING),
            'array' => array(CMB_JSON_LBRACK),
            'value' => array(CMB_JSON_STRING, CMB_JSON_NUMBER,
                             CMB_JSON_LBRACE, CMB_JSON_LBRACK,
                             CMB_JSON_TRUE, CMB_JSON_FALSE,
                             CMB_JSON_NULL));
    }

    /**
     * Quotes a string for use as JSON string.
     *
     * @access private
     *
     * @param  string $string
     * @return string
     */
    function quote($string)
    {
        $string = addcslashes($string, "\"\\");
        $string = preg_replace('/[\x00-\x1f]/s', '\u00$1', $string);
        return $string;
    }

    /**
     * Returns UTF-8 chars for JSON unicode escape sequence.
     *
     * Ignores BOM and illegal surrogates.
     *
     * @access  private
     *
     * @param   array $matches
     * @return  string
     */
    function unescape($matches)
    {
        $n = hexdec($matches[1]);
        if ($n >= 0 && $n <= 0x007f) {
            return chr($n);
        } elseif ($n <= 0x07ff) {
            return chr(0xc0 | ($n >> 6))
                . chr(0x80 | ($n & 0x003f));
        } elseif($n == 0xfeff) {
            return ''; // skip BOM
        } elseif ($n >= 0xd800 && $n <= 0xdfff) {
            // TODO: what to do here?
            return '';
        } else {
            return chr(0xe0 | ($n >> 12))
                . chr(0x80 | (($n >> 6) & 0x003f))
                . chr(0x80 | ($n & 0x003f));
        }
    }

    /**
     * Scans the next token and sets $this->sym accordingly.
     *
     * @access  private
     *
     * @return  void
     */
    function getSym()
    {
        $this->str = preg_replace('/^\s*/', '', $this->str); // TODO: all UTF-8 whitespace
        if (empty($this->str)) {
            $this->sym = CMB_JSON_EOS;
            return;
        }
        switch ($this->str{0}) {
        case '-': case '0': case '1': case '2': case '3': case '4':
        case '5': case '6': case '7': case '8': case '9':
            preg_match('/-?(?:0|[1-9][0-9]*(?:\.[0-9]+)?(?:[eE][-+]?[0-9]+)?)/',
                       $this->str, $m);
            $i = intval($m[0]);
            $this->value = $i == $m[0] ? $i : floatval($m[0]);
            $this->str = substr($this->str, strlen($m[0]));
            $this->sym = CMB_JSON_NUMBER;
            break;
        case '"':
            preg_match('/^"((?:[^"\\\\]|[\\\\](?:["\\\\\/bfnrt]|u[0-9a-fA-F]{4}))*)/',
                       $this->str, $m);
            $m[1] = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/',
                                          array($this, 'unescape'),
                                          $m[1]);
            $this->value = stripcslashes($m[1]);
            $this->str = substr($this->str, strlen($m[0]) + 1);
            $this->sym = CMB_JSON_STRING;
            break;
        case '{':
            $this->str = substr($this->str, 1);
            $this->sym = CMB_JSON_LBRACE;
            break;
        case '}':
            $this->str = substr($this->str, 1);
            $this->sym = CMB_JSON_RBRACE;
            break;
        case '[':
            $this->str = substr($this->str, 1);
            $this->sym = CMB_JSON_LBRACK;
            break;
        case ']':
            $this->str = substr($this->str, 1);
            $this->sym = CMB_JSON_RBRACK;
            break;
        case ',':
            $this->str = substr($this->str, 1);
            $this->sym = CMB_JSON_COMMA;
            break;
        case ':':
            $this->str = substr($this->str, 1);
            $this->sym = CMB_JSON_COLON;
            break;
        case 't':
            if (strpos($this->str, 'true') === 0) {
                $this->str = substr($this->str, 4);
                $this->sym = CMB_JSON_TRUE;
            } else {
                $this->sym = CMB_JSON_UNKNOWN;
            }
            break;
        case 'f':
            if (strpos($this->str, 'false') === 0) {
                $this->str = substr($this->str, 5);
                $this->sym = CMB_JSON_FALSE;
            } else {
                $this->sym = CMB_JSON_UNKNOWN;
            }
            break;
        case 'n':
            if (strpos($this->str, 'null') === 0) {
                $this->str = substr($this->str, 4);
                $this->sym = CMB_JSON_NULL;
            } else {
                $this->sym = CMB_JSON_UNKNOWN;
            }
            break;
        default:
            $this->sym = CMB_JSON_UNKNOWN;
        }
    }

    /**
     * Expect and consume a terminal symbol.
     *
     * Set <var>$error</var> to true, if symbol was not found.
     *
     * @access  private
     *
     * @param   string $terminal
     * @return  void
     */
    function accept($terminal)
    {
        if ($this->sym != $terminal) {
            $this->error = true;
        }
        $this->getSym();
    }


    /**
     * Parse an object.
     *
     * <samp>object -> LBRACE [ pair { COMMA pair ] RBRACE</samp>
     *
     * @access private
     *
     * @param  object &$res
     * @return void
     */
    function parseObject(&$res)
    {
        $this->accept(CMB_JSON_LBRACE);
        $res = array();
        if (in_array($this->sym, $this->first['pair'])) {
            $this->parsePair($key, $val);
            $res[$key]= $val;
            while ($this->sym == CMB_JSON_COMMA) {
                $this->accept(CMB_JSON_COMMA);
                $this->parsePair($key, $val);
                $res[$key] = $val;
            }
        }
        $this->accept(CMB_JSON_RBRACE);
    }


    /**
     * Parse a pair.
     *
     * <samp>pair -> STRING COLON value</samp>
     *
     * @access  private
     *
     * @param   string &$key
     * @param   mixed &$val
     * @return  void
     */
    function parsePair(&$key, &$val)
    {
        $this->accept(CMB_JSON_STRING);
        $key = $this->value;
        $this->accept(CMB_JSON_COLON);
        $this->parseValue($val);
    }


    /**
     * Parse an array.
     *
     * <samp>array -> LBRACK [ value { COMMA value } ] RBRACK</samp>
     *
     * @access  private
     *
     * @param   array &$res
     * @return  void
     */
    function parseArray(&$res)
    {
        $this->accept(CMB_JSON_LBRACK);
        $res = array();
        if (in_array($this->sym, $this->first['value'])) {
            $this->parseValue($res1);
            $res[] = $res1;
            while ($this->sym == CMB_JSON_COMMA) {
                $this->accept(CMB_JSON_COMMA);
                $this->parseValue($res1);
                $res[] = $res1;
            }
        }
        $this->accept(CMB_JSON_RBRACK);
    }


    /**
     * Parse a value.
     *
     * <samp>value -> STRING | NUMBER | object | array | TRUE | FALSE | NULL</samp>
     *
     * @access private
     *
     * @param   mixed &$res
     * @return  void
     */
    function parseValue(&$res)
    {
        switch ($this->sym) {
        case CMB_JSON_STRING:
            $res = $this->value;
            $this->getSym();
            break;
        case CMB_JSON_NUMBER:
            $res = $this->value;
            $this->getSym();
            break;
        case CMB_JSON_LBRACE:
            $this->parseObject($res);
            break;
        case CMB_JSON_LBRACK:
            $this->parseArray($res);
            break;
        case CMB_JSON_TRUE:
            $res = true;
            $this->getSym();
            break;
        case CMB_JSON_FALSE:
            $res = false;
            $this->getSym();
            break;
        case CMB_JSON_NULL:
            $res = null;
            $this->getSym();
            break;
        }
    }

    /**
     * Returns a PHP value encoded as JSON string.
     *
     * <kbd>encode($value)</kbd> should be equivalent to
     * <kbd>json_encode($value, JSON_UNESCAPED_SLASHES
     * | JSON_UNESCAPED_UNICODE)</kbd>.
     *
     * @access public
     *
     * @param  mixed $value
     * @return string
     */
    function encode($value)
    {
        switch (gettype($value)) {
        case 'boolean':
            return $value ? 'true' : 'false';
        case 'integer':
        case 'double':
            return $value;
        case 'string':
            return '"' . $this->quote($value) . '"';
        case 'array':
            if (array_keys($value) === range(0, count($value) - 1)) {
                // encode as array
                $elts = array();
                foreach ($value as $val) {
                    $elts[] = $this->encode($val);
                }
                return '[' . implode(',', $elts) . ']';
            } else {
                // encode as object
                $members = array();
                foreach ($value as $key => $val) {
                    $members[] = '"' . $this->quote($key) . '":'
                        . $this->encode($val);
                }
                return '{' . implode(',', $members) . '}';
            }
        case 'object':
            return $this->encode(get_object_vars($value));
        case 'NULL':
            return 'null';
        default:
            $msg = __FUNCTION__ . '(): type is unsupported, encoded as null';
            trigger_error($msg, E_USER_WARNING);
            return 'null';
        }
    }
    /**
     * Returns the JSON string decoded as PHP value.
     *
     * <kbd>decode($string)</kbd> should be equivalent to
     * <kbd>json_decode($string, true)</kbd> for valid UTF-8.
     *
     * If the input is erroneous, NULL is returned. To distinguish this from
     * a real NULL value, use {@link CMB_JSON::lastError()}.
     *
     * @access  public
     *
     * @param   string $string
     * @return  mixed
     */
    function decode($string)
    {
        $this->str = $string;
        $this->sym = $this->value = null;
        $this->error =  false;
        $this->getSym();
        $this->parseValue($res);
        return $this->error ? null : $res;
    }

    /**
     * Returns whether an error has occurred during the last {@link CMB_JSON::decode()}.
     *
     * @access public
     *
     * @return bool
     */
    function lastError()
    {
        return $this->error;
    }

    /**
     * Returns the unique instance of the class.
     *
     * @access public
     *
     * @return object
     */
    function instance()
    {
        static $instance;

        if (!isset($instance)) {
            $instance = new CMB_JSON();
        }
        return $instance;
    }
}

?>
