<?php

/**
 * Copyright 2013-2019 Christoph M. Becker
 *
 * This file is part of ReqCheck_XH.
 *
 * ReqCheck_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * ReqCheck_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ReqCheck_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

if (isset($_GET['phpinfo'])) {
    echo phpinfo();
    exit;
}

$version = '@CMSIMPLE_XH_VERSION@';
$title = "$version – Requirements Check";

$checks = array();
$checks['the Webserver is supported'] = preg_match('/apache|nginx|iis|litespeed/i', $_SERVER['SERVER_SOFTWARE']) ? 'okay' : 'warn';
$checks['the PHP Version is at least 5.5.0'] = version_compare(PHP_VERSION, '5.5.0', '>=') ? 'okay' : 'fail';
foreach (array('json', 'mbstring', 'session') as $ext) {
    $checks['the PHP extension "' . $ext . '" is installed'] = extension_loaded($ext) ? 'okay' : 'fail';
}
$checks['safe_mode is off'] = !ini_get('safe_mode') ? 'okay' : 'warn';
$checks['session.use_trans_sid is off'] = !ini_get('session.use_trans_sid') ? 'okay' : 'warn';
$checks['session.use_only_cookies is on'] = ini_get('session.use_only_cookies') ? 'okay' : 'warn';
$checks['session.cookie_lifetime is zero'] = ini_get('session.cookie_lifetime') == 0 ? 'okay' : 'warn';
$checks['the function fsockopen is available'] = function_exists('fsockopen') ? 'okay' : 'warn';

$fail = $warn = false;
foreach ($checks as $state) {
    switch ($state) {
        case 'fail':
            $fail = true;
            break;
        case 'warn':
            $warn = true;
            break;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo $title?></title>
<style type="text/css">
/*! system-font.css v2.0.2 | CC0-1.0 License | github.com/jonathantneal/system-font-css */
@font-face{font-family:system-ui;font-style:normal;font-weight:300;src:local(".SFNSText-Light"),local(".HelveticaNeueDeskInterface-Light"),local(".LucidaGrandeUI"),local("Segoe UI Light"),local("Ubuntu Light"),local("Roboto-Light"),local("DroidSans"),local("Tahoma")}@font-face{font-family:system-ui;font-style:italic;font-weight:300;src:local(".SFNSText-LightItalic"),local(".HelveticaNeueDeskInterface-Italic"),local(".LucidaGrandeUI"),local("Segoe UI Light Italic"),local("Ubuntu Light Italic"),local("Roboto-LightItalic"),local("DroidSans"),local("Tahoma")}@font-face{font-family:system-ui;font-style:normal;font-weight:400;src:local(".SFNSText-Regular"),local(".HelveticaNeueDeskInterface-Regular"),local(".LucidaGrandeUI"),local("Segoe UI"),local("Ubuntu"),local("Roboto-Regular"),local("DroidSans"),local("Tahoma")}@font-face{font-family:system-ui;font-style:italic;font-weight:400;src:local(".SFNSText-Italic"),local(".HelveticaNeueDeskInterface-Italic"),local(".LucidaGrandeUI"),local("Segoe UI Italic"),local("Ubuntu Italic"),local("Roboto-Italic"),local("DroidSans"),local("Tahoma")}@font-face{font-family:system-ui;font-style:normal;font-weight:500;src:local(".SFNSText-Medium"),local(".HelveticaNeueDeskInterface-MediumP4"),local(".LucidaGrandeUI"),local("Segoe UI Semibold"),local("Ubuntu Medium"),local("Roboto-Medium"),local("DroidSans-Bold"),local("Tahoma Bold")}@font-face{font-family:system-ui;font-style:italic;font-weight:500;src:local(".SFNSText-MediumItalic"),local(".HelveticaNeueDeskInterface-MediumItalicP4"),local(".LucidaGrandeUI"),local("Segoe UI Semibold Italic"),local("Ubuntu Medium Italic"),local("Roboto-MediumItalic"),local("DroidSans-Bold"),local("Tahoma Bold")}@font-face{font-family:system-ui;font-style:normal;font-weight:700;src:local(".SFNSText-Bold"),local(".HelveticaNeueDeskInterface-Bold"),local(".LucidaGrandeUI"),local("Segoe UI Bold"),local("Ubuntu Bold"),local("Roboto-Bold"),local("DroidSans-Bold"),local("Tahoma Bold")}@font-face{font-family:system-ui;font-style:italic;font-weight:700;src:local(".SFNSText-BoldItalic"),local(".HelveticaNeueDeskInterface-BoldItalic"),local(".LucidaGrandeUI"),local("Segoe UI Bold Italic"),local("Ubuntu Bold Italic"),local("Roboto-BoldItalic"),local("DroidSans-Bold"),local("Tahoma Bold")}
body {
	font: 15px/1.25 system-ui, sans-serif;
	color: #333;
	margin: 0;
	padding: 0;
}
div.xhReqcheck {
	margin: 2em auto;
	padding: 1em;
	max-width: 90%;
}
div.xhLogo {
	background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iRWJlbmVfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHdpZHRoPSI1MjBweCIgaGVpZ2h0PSI1MjBweCIgdmlld0JveD0iMzcuNjQgMTYwLjk0NSA1MjAgNTIwIiBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDM3LjY0IDE2MC45NDUgNTIwIDUyMCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+PHJlY3QgeD0iMzcuNjQiIHk9IjE2MC45NDUiIGZpbGw9IiNGRkZGRkYiIHdpZHRoPSI1MjAiIGhlaWdodD0iNTIwIi8+PHBhdGggZmlsbD0iI2VlZWVlZSIgZD0iTTM1OS40ODMsNjA2LjE2NWMwLDcuNzQ5LTYuNDk0LDExLjU5Ni0xNS40MzgsMTIuNjY0Yy0zLjg0MywwLjQ4OC04LjcxMywwLjcyNC0xNC42NywwLjcyNGMtNS43NjUsMC03LjY0Ni0wLjIzNS0xMS41OTQtMC43MjRjLTEuNTE3LTAuMTcxLTIuOTQ1LTAuNDA2LTQuMjQ5LTAuNjIxYy0wLjA0MywwLTAuMDQzLDAtMC4wODYsMGMtNC4yNDktMC4zMTctNy43OTMtMC44NTQtMTAuMjkzLTEuNDc0Yy0yLjY0Ni0wLjc2OC00LjUyMy0xLjcwNy01LjkxNS0yLjg2MWMtMS4yODUtMS4xNzQtMi40NTktMi40MzQtMy40NDMtMy44MmwtMTA0LjE3LTE1MC43OTFsLTc0LjE4MSwxMTAuNzkyYy0wLjg1NCwxLjM4OC0yLjEwNCwyLjY0Ni0zLjM1MSwzLjgwMmMtMS4zODgsMS4xNzQtMy40MDcsMi4xNTQtNS45MTYsMi44NjFjLTIuNjM3LDAuNjM4LTYuMDMyLDEuMTc0LTEwLjE5NSwxLjQ3NWMtNC4xMiwwLjMxNy05LjY2NCwwLjU1LTE2LjU1NywwLjU1Yy03LjAyNSwwLTEyLjkzMS0wLjMyLTE3LjQ0Ny0wLjg1NHMtNy43ODItMS4zODctOS45MzgtMi42NDljLTIuMTQ2LTEuMTU0LTIuOTkxLTIuODU4LTIuOTA1LTQuNzRjMC4xMzktMi4wMDgsMS4zMDItNC40NjQsMy40MDYtNy4zNDZsMTAzLjA3OC0xMzYuMTU0TDU4LjA4MiwyOTUuODA5Yy0xLjg3OC0yLjk1Ni0zLjA0My01LjQxMy0zLjEyOS03LjI5MmMtMC4xMzgtMi4wMTgsMC45ODItMy42MjEsMy4zNTEtNS4wMDhjMi4yODctMS4zODksNS45MDUtMi4xOTgsMTAuNjk4LTIuNjQ3YzQuNzgzLTAuMzk2LDExLjA0OC0wLjYyLDE4Ljk3MS0wLjYyYzcuMDI0LDAsMTIuNzksMC4yMjQsMTcuMDQsMC41MzNjNC4zMDEsMC4zMTEsNy42OTUsMC43MTUsMTAuMDY3LDEuMzQ0YzIuNTA4LDAuNjcxLDQuMzg3LDEuMzg5LDUuNTA5LDIuNDU3YzEuMjQ5LDEuMDc3LDIuNDEzLDIuMzI3LDMuMzk0LDMuNzE0bDY1LjMyOCwxMDAuNjI1bDEwNi4zNjItMTU3LjcyYzAuOTQzLTEuMTYzLDEuOTctMi4zMjQsMy4yMy0zLjM5NWMxLjIzOS0xLjAyNywyLjg1OS0xLjg4LDUuMDE3LTIuNjM4YzUuNTc2LTIuMDYxLDE5Ljg1Ny0yLjgxOSwyNS40NTEtMi44MTljNS45NTcsMCwxMC44MjcsMC4yNjYsMTQuNjcxLDAuNzE1YzMuODQzLDAuNDksNi44OTYsMS4xMTEsOS4xODMsMS44NzhjMi4zNjYsMC43MTYsMy45MjcsMS43NCw0Ljg2NywyLjk5M2MwLjkzOSwxLjE2NCwxLjM4OCwyLjYwMiwxLjM4OCw0LjA3NnYxNjYuMjZoMTExLjIyN1YyODkuMDkxYzAtMS41MTcsMC40NDYtMi45MDQsMS40NzMtNC4xMTJjMC45MzgtMS4yNSwyLjYwMy0yLjI0MSw0LjgwMy0yLjk1NmMyLjE3OC0wLjgwMiw1LjMxNy0xLjQzMiw5LjE2LTEuODc5YzMuOTQ4LTAuNDkzLDYuOTM5LTAuNzE2LDEyLjg5My0wLjcxNmM1Ljg5NywwLDEwLjcsMC4yMjQsMTQuNTQ0LDAuNzE2YzMuOCwwLjQ0Nyw2Ljg3OCwxLjA3Nyw5LjE1OSwxLjg3OWMyLjI0MywwLjcxNSwzLjkwOSwxLjcwNyw0LjkzMiwyLjk1NmMxLjAyNSwxLjIwNywxLjQ3NiwyLjU5NSwxLjQ3Niw0LjExMnYyODAuODNjMCwxLjQ3Mi0wLjQ1LDIuOTAzLTEuNDc2LDQuMDU3Yy0xLjAyMiwxLjI1OC0yLjY4OCwyLjI4Ny00LjkzMiwyLjk2OGMtMi4yODEsMC44MTItNS4zNTksMS40MzEtOS4xNTksMS44NzhjLTMuODQyLDAuNDkyLTguNjQ2LDAuNzA0LTE0LjU0NCwwLjcwNGMtNS45NTQsMC04Ljk0NC0wLjIxMi0xMi44OTMtMC43MDRjLTMuODQzLTAuNDUtNi45ODItMS4wODEtOS4xNi0xLjg3OGMtMi4yLTAuNjgxLTMuODY1LTEuNzEtNC44MDMtMi45NjhjLTEuMDI2LTEuMTUzLTEuNDczLTIuNTg1LTEuNDczLTQuMDU3VjQ0OS42NzFIMzU5LjQ3OXYxNTYuNDkzSDM1OS40ODN6IE0zMDIuMDQxLDMxNS43NjJsLTgwLjIsMTA3LjU2NGw4MC4yLDEwOC4zMjdWMzE1Ljc2MnoiLz48L3N2Zz4=) no-repeat left top;
	margin: 0 0 1em 1em;
	float: right;
	height: 3em;
	width: 3em;
	background-size: contain;
}
h1, h2 {
	font-weight: normal;
}
/* xh messages */
.okay, .warn, .fail, li.okay, li.warn, li.fail {
	font: normal 15px/1.25 system-ui, sans-serif !important;
	padding: .475em .5em .5em 2em !important;
	margin: .5em 0 !important;
	text-align: left;
	clear: both;
}
.okay {
	background: #e1f8cb url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iRWJlbmVfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHdpZHRoPSIxNnB4IiBoZWlnaHQ9IjE2cHgiIHZpZXdCb3g9IjAgMCAxNiAxNiIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMTYgMTYiIHhtbDpzcGFjZT0icHJlc2VydmUiPjxnIGlkPSJzdWNjZXNzIj48Zz48cGF0aCBmaWxsPSIjMzNDQzMzIiBkPSJNMTUuNzEsNi44MzVsLTcuNDc0LDcuNDc0bC0xLjQwMywxLjQwM0M2LjY0NywxNS44OTgsNi4zODksMTYsNi4xMzEsMTZjLTAuMjU4LDAtMC41MTYtMC4xMDQtMC43MDItMC4yODhsLTEuNDA0LTEuNDAzbC0zLjczNi0zLjczNkMwLjEwMywxMC4zODUsMCwxMC4xMjcsMCw5Ljg2OXMwLjEwMy0wLjUxNiwwLjI4OS0wLjcwMmwxLjQwNC0xLjQwNGMwLjE4NS0wLjE4NiwwLjQ0NC0wLjI4OCwwLjcwMS0wLjI4OGMwLjI1OSwwLDAuNTE3LDAuMTAzLDAuNzAzLDAuMjg4bDMuMDM1LDMuMDQ1bDYuNzcyLTYuNzgxYzAuMTg2LTAuMTg3LDAuNDQzLTAuMjg5LDAuNzAxLTAuMjg5czAuNTE3LDAuMTAzLDAuNzAzLDAuMjg5bDEuNDAxLDEuNDA0QzE1Ljg5Niw1LjYxNywxNiw1Ljg3NSwxNiw2LjEzM0MxNiw2LjM5MSwxNS44OTYsNi42NDksMTUuNzEsNi44MzV6Ii8+PC9nPjwvZz48L3N2Zz4=) no-repeat .5em .5em;
	color: #37620d;
	border: 1px solid #c6d880;
}
.warn {
	background: #ffffbb url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iRWJlbmVfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHdpZHRoPSIxNnB4IiBoZWlnaHQ9IjE2cHgiIHZpZXdCb3g9IjAgMCAxNiAxNiIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMTYgMTYiIHhtbDpzcGFjZT0icHJlc2VydmUiPjxnIGlkPSJ3YXJuaW5nIj48cmVjdCB4PSI2IiB5PSI1LjU3IiB3aWR0aD0iNCIgaGVpZ2h0PSI5Ii8+PGc+PHBhdGggZmlsbD0iI0ZGQ0MwMCIgZD0iTTE1Ljg1NywxNC4zMTJjMC4xOTcsMC4zNDksMC4xODgsMC43NzYtMC4wMTksMS4xMjVDMTUuNjM0LDE1Ljc4NiwxNS4yNTksMTYsMTQuODU3LDE2SDEuMTQyYy0wLjQwMiwwLTAuNzc3LTAuMjE1LTAuOTgyLTAuNTYzcy0wLjIxNC0wLjc3Ni0wLjAxOC0xLjEyNUw3LDEuNzM5YzAuMTk2LTAuMzY2LDAuNTgtMC41OTgsMS0wLjU5OHMwLjgwNSwwLjIzMiwxLDAuNTk4TDE1Ljg1NywxNC4zMTJ6IE05LjI4NSw1Ljk4YzAtMC4wNTMtMC4wMjYtMC4xMjUtMC4wOS0wLjE2OUM5LjE0Myw1Ljc2Niw5LjA2Myw1LjcxMyw4Ljk4Miw1LjcxM0g3LjAxOGMtMC4wODEsMC0wLjE2MSwwLjA1NC0wLjIxNSwwLjA5OEM2Ljc0MSw1Ljg1NSw2LjcxNCw1Ljk0NSw2LjcxNCw1Ljk5OGwwLjE1Miw0LjA4MmMwLDAuMTE1LDAuMTM0LDAuMjA1LDAuMzA0LDAuMjA1aDEuNjUyYzAuMTYsMCwwLjI5NC0wLjA5LDAuMzAzLTAuMjA1TDkuMjg1LDUuOTh6IE05LjE0MywxMS43MjNjMC0wLjE2MS0wLjEyNS0wLjI5NS0wLjI4NS0wLjI5NUg3LjE0MmMtMC4xNiwwLTAuMjg1LDAuMTM0LTAuMjg1LDAuMjk1djEuNjk1YzAsMC4xNjEsMC4xMjUsMC4yOTUsMC4yODUsMC4yOTVoMS43MTVjMC4xNiwwLDAuMjg1LTAuMTM0LDAuMjg1LTAuMjk1VjExLjcyM3oiLz48L2c+PC9nPjwvc3ZnPg==) no-repeat .5em .5em;
	color: #756730;
	border: 1px solid #ffd324;
}
.fail {
	background: #ffeae5 url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iRWJlbmVfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHdpZHRoPSIxNnB4IiBoZWlnaHQ9IjE2cHgiIHZpZXdCb3g9IjAgMCAxNiAxNiIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMTYgMTYiIHhtbDpzcGFjZT0icHJlc2VydmUiPjxnIGlkPSJmYWlsIj48cmVjdCB4PSI2IiB5PSI1LjU3IiBmaWxsPSIjRkZGRjAwIiB3aWR0aD0iNCIgaGVpZ2h0PSI5Ii8+PGc+PHBhdGggZmlsbD0iI0ZGNDAwMCIgZD0iTTE1Ljg1NywxNC4zMTJjMC4xOTcsMC4zNDksMC4xODgsMC43NzYtMC4wMTksMS4xMjVDMTUuNjM0LDE1Ljc4NiwxNS4yNTksMTYsMTQuODU3LDE2SDEuMTQyYy0wLjQwMiwwLTAuNzc3LTAuMjE1LTAuOTgyLTAuNTYzcy0wLjIxNC0wLjc3Ni0wLjAxOC0xLjEyNUw3LDEuNzM5YzAuMTk2LTAuMzY2LDAuNTgtMC41OTgsMS0wLjU5OHMwLjgwNSwwLjIzMiwxLDAuNTk4TDE1Ljg1NywxNC4zMTJ6IE05LjI4NSw1Ljk4YzAtMC4wNTMtMC4wMjYtMC4xMjUtMC4wOS0wLjE2OUM5LjE0Myw1Ljc2Niw5LjA2Myw1LjcxMyw4Ljk4Miw1LjcxM0g3LjAxOGMtMC4wODEsMC0wLjE2MSwwLjA1NC0wLjIxNSwwLjA5OEM2Ljc0MSw1Ljg1NSw2LjcxNCw1Ljk0NSw2LjcxNCw1Ljk5OGwwLjE1Miw0LjA4MmMwLDAuMTE1LDAuMTM0LDAuMjA1LDAuMzA0LDAuMjA1aDEuNjUyYzAuMTYsMCwwLjI5NC0wLjA5LDAuMzAzLTAuMjA1TDkuMjg1LDUuOTh6IE05LjE0MywxMS43MjNjMC0wLjE2MS0wLjEyNS0wLjI5NS0wLjI4NS0wLjI5NUg3LjE0MmMtMC4xNiwwLTAuMjg1LDAuMTM0LTAuMjg1LDAuMjk1djEuNjk1YzAsMC4xNjEsMC4xMjUsMC4yOTUsMC4yODUsMC4yOTVoMS43MTVjMC4xNiwwLDAuMjg1LTAuMTM0LDAuMjg1LTAuMjk1VjExLjcyM3oiLz48L2c+PC9nPjwvc3ZnPg==) no-repeat .5em .5em;
	color: #f30;
	border: 1px solid #f2a197;
}
ul {
	list-style: none
}
.bigger {
	font-size: 1.25em;
	font-weight: normal;
}
/* responsive part */
@media (min-width: 768px) {
div.xhReqcheck {
	max-width: 75%;
}
div.xhLogo {
	height: 4em;
	width: 4em;
}
}
@media (min-width: 992px) {
div.xhReqcheck {
	max-width: 50%;
}
div.xhLogo {
	height: 5em;
	width: 5em;
}
}
a {
	color: #333;
	text-decoration: none;
	border-bottom: 1px dotted #333;
}

</style>
</head>
<body>
<div class="xhReqcheck">
<div class="xhLogo"> </div>
<h1><?php echo $title?></h1>
<?php if ($fail):?>
<p class="fail"><span class="bigger"><strong>Sorry, there appear to be serious issues!</strong></span><br>Most likely <?php echo $version?> will not run on this server with the current PHP configuration!</p>
<?php elseif ($warn):?>
<p class="warn"><span class="bigger"><strong>Hmm, there appear to be minor issues</strong>!</span><br><?php echo $version?> may not run smoothly on this server with the current PHP configuration!</p>
<?php else:?>
<p class="okay"><span class="bigger"><strong>All is well!</strong></span><br><?php echo $version?> is supposed to run smoothly on this server!</p>
<?php endif?>
<h2>Details</h2>
<?php foreach ($checks as $check => $state):?>
<div class="<?php echo $state?>">Checking that <?php echo $check?> – <?php echo $state?></div>
<?php endforeach?>
<p><a href="?phpinfo" target="_blank">See PHP Info [new window]</a></p>
</div>
</body>
</html>