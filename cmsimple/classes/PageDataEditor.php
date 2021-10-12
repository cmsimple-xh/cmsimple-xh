<?php

namespace XH;

/**
 * The page data editor.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2021 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @copyright GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
 * @since     1.6
 */
class PageDataEditor
{
    /**
     * Returns the currently unused page data fields.
     *
     * @return array
     */
    private function unusedFields()
    {
        global $pd_router;

        $defaultFields = array('url', 'last_edit');
        $storedFields = $pd_router->storedFields();
        $currentFields = $pd_router->getCurrentInterests();
        $unusedFields = array_diff($storedFields, $currentFields, $defaultFields);
        $unusedFields = array_values($unusedFields);
        return $unusedFields;
    }

    /**
     * Deletes the requested page data fields. Returns number of fields deleted;
     * false if saving failed.
     *
     * @return int|false
     */
    private function deleteFields()
    {
        global $pd_router;

        $fields = $_POST['xh_fields'];
        if (empty($fields)) {
            return 0;
        }
        foreach ($fields as $field) {
            $pd_router->removeInterest($field);
        }
        $ok = XH_saveContents();
        return $ok ? count($fields) : false;
    }

    /**
     * Returns a single field.
     *
     * @param string $field A field name.
     *
     * @return string HTML
     */
    private function renderField($field)
    {
        return '<li><label>'
            . '<input type="checkbox" name="xh_fields[]" value="' . $field . '">'
            . XH_hsc($field)
            . '</label></li>';
    }

    /**
     * Returns a result message.
     *
     * @param mixed $deleted The number of deleted fields resp.
     *                       <var>false</var> on failure.
     *
     * @return string HTML
     */
    private function renderMessage($deleted)
    {
        global $pth, $tx;

        $ptx = $tx['pagedata'];
        if (isset($deleted)) {
            if ($deleted === false) {
                return XH_message('fail', $ptx['fail'], $pth['file']['content']);
            } elseif ($deleted === 0) {
                return XH_message('info', $ptx['nothing']);
            } else {
                $suffix = $deleted == 1 ? '1' : ($deleted >= 5 ? '5' : '2_4');
                return XH_message('success', $ptx['deleted_' . $suffix], $deleted);
            }
        }
        return "";
    }

    /**
     * Returns the editor view.
     *
     * @param mixed $deleted The number of deleted fields, <var>null</var> on
     *                       initial request resp. <var>false</var> on failure.
     *
     * @return string HTML
     */
    private function render($deleted = null)
    {
        global $sn, $tx, $_XH_csrfProtection;

        if (!isset($deleted) && isset($_GET['xh_success'])) {
            $deleted = $_GET['xh_success'];
        }
        $o = $this->renderMessage($deleted);
        if ($deleted === false) {
            return $o;
        }
        $unusedFields = $this->unusedFields();
        if (empty($unusedFields)) {
            $o .= '<p>' . $tx['pagedata']['ok'] . '</p>';
        } else {
            $action = $sn . '?&amp;xh_pagedata&amp;edit';
            $o .= '<form id="xh_pagedata" action="' . $action . '" method="post">'
                . '<p>' . $tx['pagedata']['info'] . '</p>'
                . '<ul>';
            foreach ($unusedFields as $field) {
                $o .= $this->renderField($field);
            }
            $o .= '</ul>'
                . '<input type="submit" class="submit" name="xh_pagedata_delete"'
                . ' value="' . $tx['action']['delete'] . '">'
                . $_XH_csrfProtection->tokenInput()
                . '</form>';
        }
        return $o;
    }

    /**
     * Handles requests to the page data editor.
     *
     * @return string HTML
     */
    public function process()
    {
        global $_XH_csrfProtection;

        if (isset($_POST['xh_pagedata_delete'])) {
            $_XH_csrfProtection->check();
            $deleted = $this->deleteFields();
            if (!$deleted) {
                return $this->render($deleted);
            } else {
                $location = CMSIMPLE_URL . '?&xh_pagedata&xh_success=' . $deleted;
                header('Location: ' . $location, true, 303);
                exit;
            }
        }
        return $this->render();
    }
}
