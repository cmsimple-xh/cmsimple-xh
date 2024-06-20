<?php

/**
 * General editor interface of TinyMCE5.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   TinyMCE5
 * @author    Emanuel Marinello <marinello@pixolution.ch>
 * @author    Christoph M. Becker <cmbecker69@gmx.de> (program structure)
 * @copyright 2011-2017 Christoph M. Becker <http://3-magi.net/>
 * @copyright 2021 CMSimple_XH <https://www.cmsimple-xh.org>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      https://github.com/cmsimple-xh/cmsimple-xh
 */

Use Tinymce6\Editor;

/**
 * Writes the basic JavaScript of the editor to the `head' element.
 * No editors are actually created. Multiple calls are allowed.
 * This is called from init_EDITOR() automatically, but not from EDITOR_replace().
 *
 * @return void
 */
// @codingStandardsIgnoreStart
function include_tinymce6()
{
// @codingStandardsIgnoreEnd
    Editor::doInclude();
}

/**
 * Returns the JavaScript to actually instantiate a single editor a
 * `textarea' element.
 *
 * To actually create the editor, the caller has to write the the return value
 * to the (X)HTML output, properly enclosed as `script' element,
 * after the according `textarea' element,
 * or execute the return value by other means.
 *
 * @param string $elementId The id of the `textarea' element that should become
 *                          an editor instance.
 * @param string $config    The configuration string.
 *
 * @return string The JavaScript to actually create the editor.
 */
// @codingStandardsIgnoreStart
function tinymce6_replace($elementId, $config = '')
{
// @codingStandardsIgnoreEnd
    return Editor::replace($elementId, $config);
}

/**
 * Instantiates the editor(s) on the textarea(s) given by $classes.
 * $config is exactly the same as for EDITOR_replace().
 *
 * @param string $classes The classes of the textarea(s) that should become
 *                        an editor instance.
 * @param string $config  The configuration string.
 *
 * @return void
 */
// @codingStandardsIgnoreStart
function init_tinymce6($classes = array(), $config = false)
{
// @codingStandardsIgnoreEnd
    return Editor::init($classes, $config);
}

