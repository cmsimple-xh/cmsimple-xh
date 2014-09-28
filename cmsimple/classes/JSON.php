<?php

/**
 * Handling of encoding and decoding of JSON from resp. to
 * native PHP data structures. Provides a fallback for PHP < 5.2.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2012-2014 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/**
 * End of string has been reached.
 *
 * @access private
 */
define('XH_JSON_EOS', -1);

/**
 * Unknown terminal symbol.
 *
 * @access private
 */
define('XH_JSON_UNKNOWN', 0);

/**
 * <kbd>NUMBER</kbd>
 *
 * @access private
 */
define('XH_JSON_NUMBER', 1);

/**
 * <kbd>STRING</kbd>
 *
 * @access private
 */
define('XH_JSON_STRING', 2);

/**
 * <kbd>LBRACE</kbd>
 *
 * @access private
 */
define('XH_JSON_LBRACE', 3);

/**
 * <kbd>RBRACE</kbd>
 *
 * @access private
 */
define('XH_JSON_RBRACE', 4);

/**
 * <kbd>LBRACK</kbd>
 *
 * @access private
 */
define('XH_JSON_LBRACK', 5);

/**
 * <kbd>RBRACK</kbd>
 *
 * @access private
 */
define('XH_JSON_RBRACK', 6);

/**
 * <kbd>COMMA</kbd>
 *
 * @access private
 */
define('XH_JSON_COMMA', 7);

/**
 * <kbd>COLON</kbd>
 * @access private
 */
define('XH_JSON_COLON', 8);

/**
 * <kdb>TRUE</kbd>
 *
 * @access private
 */
define('XH_JSON_TRUE', 9);

/**
 * <kbd>FALSE</kbd>
 *
 * @access private
 */
define('XH_JSON_FALSE', 10);

/**
 * <kbd>NULL</kbd>
 *
 * @access private
 */
define('XH_JSON_NULL', 11);

/**
 * Handles encoding and decoding of JSON from resp. to native PHP data structures.
 *
 * It is a <i>simplified</i> alternative to <kbd>json_encode()</kbd> and
 * <kbd>json_decode()</kbd> that can be used as fallback for ancient PHP versions.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class XH_JSON
{
    /**
     * The set of "first" tokens.
     *
     * @var array
     *
     * @access protected
     */
    var $first;

    /**
     * The string to parse.
     *
     * Already parsed parts will be truncated.
     *
     * @var string
     *
     * @access protected
     */
    var $str;

    /**
     * The current token.
     *
     * @var int
     *
     * @access protected
     */
    var $sym;

    /**
     * The current value (for strings and numbers).
     *
     * @var mixed
     *
     * @access protected
     */
    var $value;

    /**
     * Error flag.
     *
     * @var bool
     *
     * @access protected
     */
    var $error;

    /**
     * Constructs an instance.
     *
     * @access public
     */
    function XH_JSON()
    {
        $this->first = array(
            'object' => array(XH_JSON_LBRACE),
            'pair' => array(XH_JSON_STRING),
            'array' => array(XH_JSON_LBRACK),
            'value' => array(
                XH_JSON_STRING, XH_JSON_NUMBER, XH_JSON_LBRACE, XH_JSON_LBRACK,
                XH_JSON_TRUE, XH_JSON_FALSE, XH_JSON_NULL
            )
        );
    }

    /**
     * Quotes a string for use as JSON string.
     *
     * @param string $string A string.
     *
     * @return string
     *
     * @access protected
     */
    function quote($string)
    {
        $string = addcslashes($string, "\"\\/");
        $string = preg_replace('/[\x00-\x1f]/s', '\u00$1', $string);
        return $string;
    }

    /**
     * Returns UTF-8 chars for JSON unicode escape sequence.
     *
     * @param array $matches Matches from the previous preg_match().
     *
     * @return string
     *
     * @access protected
     */
    function unescape($matches)
    {
        if (isset($matches[3])) {
            $n = hexdec($matches[3]);
        } else {
            $n = hexdec($matches[1]);
            $n2 = hexdec($matches[2]);
        }
        if ($n >= 0 && $n <= 0x007f) {
            return chr($n);
        } elseif ($n <= 0x07ff) {
            return chr(0xc0 | ($n >> 6))
                . chr(0x80 | ($n & 0x003f));
        } elseif ($n >= 0xD800 && $n <= 0xDBFF) {
            $high = $n - 0xD800;
            $low = $n2 - 0xDC00;
            $codePoint = 0x010000 + (($high << 10) | $low);
            return chr(0xF0 | ($codePoint >> 18))
                . chr(0x80 | (($codePoint >> 12) & 0x3f))
                . chr(0x80 | (($codePoint >> 6) & 0x3f))
                . chr(0x80 | ($codePoint & 0x3f));
        } else {
            return chr(0xe0 | ($n >> 12))
                . chr(0x80 | (($n >> 6) & 0x003f))
                . chr(0x80 | ($n & 0x003f));
        }
    }

    /**
     * Scans the next token and sets $this->sym accordingly.
     *
     * @return void
     *
     * @access protected
     */
    function getSym()
    {
        $this->str = preg_replace('/^\s*/', '', $this->str);
        if (empty($this->str)) {
            $this->sym = XH_JSON_EOS;
            return;
        }
        switch ($this->str{0}) {
        case '-': case '0': case '1': case '2': case '3': case '4':
        case '5': case '6': case '7': case '8': case '9':
            $pattern = '/-?(?:0|[1-9][0-9]*(?:\.[0-9]+)?(?:[eE][-+]?[0-9]+)?)/';
            preg_match($pattern, $this->str, $m);
            $i = intval($m[0]);
            $this->value = $i == $m[0] ? $i : floatval($m[0]);
            $this->str = substr($this->str, strlen($m[0]));
            $this->sym = XH_JSON_NUMBER;
            break;
        case '"':
            $pattern = '/^"((?:[^"\\\\]|[\\\\](?:["\\\\\/bfnrt]|'
                . 'u[0-9a-fA-F]{4}))*)/';
            preg_match($pattern, $this->str, $m);
            $m[1] = preg_replace_callback(
                '/\\\\u([dD][89abAB][0-9a-fA-F]{2})\\\\u([dD][c-fC-F][0-9a-fA-F]{2})'
                . '|\\\\u([0-9a-fA-F]{4})/',
                array($this, 'unescape'), $m[1]
            );
            $this->value = stripcslashes($m[1]);
            $this->str = substr($this->str, strlen($m[0]) + 1);
            $this->sym = XH_JSON_STRING;
            break;
        case '{':
            $this->str = substr($this->str, 1);
            $this->sym = XH_JSON_LBRACE;
            break;
        case '}':
            $this->str = substr($this->str, 1);
            $this->sym = XH_JSON_RBRACE;
            break;
        case '[':
            $this->str = substr($this->str, 1);
            $this->sym = XH_JSON_LBRACK;
            break;
        case ']':
            $this->str = substr($this->str, 1);
            $this->sym = XH_JSON_RBRACK;
            break;
        case ',':
            $this->str = substr($this->str, 1);
            $this->sym = XH_JSON_COMMA;
            break;
        case ':':
            $this->str = substr($this->str, 1);
            $this->sym = XH_JSON_COLON;
            break;
        case 't':
            if (strpos($this->str, 'true') === 0) {
                $this->str = substr($this->str, 4);
                $this->sym = XH_JSON_TRUE;
            } else {
                $this->sym = XH_JSON_UNKNOWN;
            }
            break;
        case 'f':
            if (strpos($this->str, 'false') === 0) {
                $this->str = substr($this->str, 5);
                $this->sym = XH_JSON_FALSE;
            } else {
                $this->sym = XH_JSON_UNKNOWN;
            }
            break;
        case 'n':
            if (strpos($this->str, 'null') === 0) {
                $this->str = substr($this->str, 4);
                $this->sym = XH_JSON_NULL;
            } else {
                $this->sym = XH_JSON_UNKNOWN;
            }
            break;
        default:
            $this->sym = XH_JSON_UNKNOWN;
        }
    }

    /**
     * Expect and consume a terminal symbol.
     *
     * Set <var>$error</var> to true, if symbol was not found.
     *
     * @param string $terminal A terminal symbol.
     *
     * @return void
     *
     * @access protected
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
     * @param object $res The parsed object.
     *
     * @return void
     *
     * @access protected
     */
    function parseObject(&$res)
    {
        $this->accept(XH_JSON_LBRACE);
        $res = array();
        if (in_array($this->sym, $this->first['pair'])) {
            $this->parsePair($key, $val);
            $res[$key]= $val;
            while ($this->sym == XH_JSON_COMMA) {
                $this->accept(XH_JSON_COMMA);
                $this->parsePair($key, $val);
                $res[$key] = $val;
            }
        }
        $this->accept(XH_JSON_RBRACE);
    }

    /**
     * Parse a pair.
     *
     * <samp>pair -> STRING COLON value</samp>
     *
     * @param string $key The parsed key.
     * @param mixed  $val The parsed value.
     *
     * @return void
     *
     * @access protected
     */
    function parsePair(&$key, &$val)
    {
        $this->accept(XH_JSON_STRING);
        $key = $this->value;
        $this->accept(XH_JSON_COLON);
        $this->parseValue($val);
    }

    /**
     * Parse an array.
     *
     * <samp>array -> LBRACK [ value { COMMA value } ] RBRACK</samp>
     *
     * @param array $res The parsed array.
     *
     * @return void
     *
     * @access protected
     */
    function parseArray(&$res)
    {
        $this->accept(XH_JSON_LBRACK);
        $res = array();
        if (in_array($this->sym, $this->first['value'])) {
            $this->parseValue($res1);
            $res[] = $res1;
            while ($this->sym == XH_JSON_COMMA) {
                $this->accept(XH_JSON_COMMA);
                $this->parseValue($res1);
                $res[] = $res1;
            }
        }
        $this->accept(XH_JSON_RBRACK);
    }

    /**
     * Parse a value.
     *
     * <samp>value -> STRING | NUMBER | object | array | TRUE | FALSE | NULL</samp>
     *
     * @param mixed $res The parsed value.
     *
     * @return void
     *
     * @access protected
     */
    function parseValue(&$res)
    {
        switch ($this->sym) {
        case XH_JSON_STRING:
            $res = $this->value;
            $this->getSym();
            break;
        case XH_JSON_NUMBER:
            $res = $this->value;
            $this->getSym();
            break;
        case XH_JSON_LBRACE:
            $this->parseObject($res);
            break;
        case XH_JSON_LBRACK:
            $this->parseArray($res);
            break;
        case XH_JSON_TRUE:
            $res = true;
            $this->getSym();
            break;
        case XH_JSON_FALSE:
            $res = false;
            $this->getSym();
            break;
        case XH_JSON_NULL:
            $res = null;
            $this->getSym();
            break;
        }
    }

    /**
     * Returns a PHP value encoded as JSON string.
     *
     * <kbd>XH_JSON::encode($value)</kbd> is equivalent to
     * <kbd>json_encode($value, JSON_UNESCAPED_UNICODE)</kbd>.
     *
     * @param mixed $value A PHP value.
     *
     * @return string
     *
     * @access public
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
     * <kbd>XH_JSON::decode($string)</kbd> is equivalent to
     * <kbd>json_decode($string, true)</kbd> for valid UTF-8.
     *
     * If the input is erroneous, NULL is returned. To distinguish this from
     * a real NULL value, use {@link XH_JSON::lastError()}.
     *
     * @param string $string A JSON string.
     *
     * @return mixed
     *
     * @access public
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
     * Returns whether an error has occurred
     * during the last {@link XH_JSON::decode()}.
     *
     * @return bool
     *
     * @access public
     */
    function lastError()
    {
        return $this->error;
    }
}

?>
