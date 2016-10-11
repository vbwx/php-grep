<?php
// PHP grep
// Copyright (C) 2012 Bernhard Waldbrunner
/*
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="generator" content="TextMate + PHP">
	<meta name="author" content="Bernhard Waldbrunner">
	<style type="text/css">
	/* <![CDATA[ */
	* {
		font-family: "Lucida Sans Unicode", "Lucida Grande", sans-serif;
		font-size: 10pt;
	}
	label {
		width: 5em;
		display: inline-block;
	}
	ol {
		margin-left: 5em;
		padding-left: 0.3em;
	}
	input[type=submit] {
		font-size: 12pt;
	}
	/* ]]> */
	</style>
	<title>grep</title>
</head>
<body>
<?php
if (get_magic_quotes_gpc())
{
    function stripslashes_gpc (&$value)
    {
        $value = stripslashes($value);
    }
    array_walk_recursive($_GET, 'stripslashes_gpc');
    array_walk_recursive($_POST, 'stripslashes_gpc');
    array_walk_recursive($_COOKIE, 'stripslashes_gpc');
    array_walk_recursive($_REQUEST, 'stripslashes_gpc');
}

/**
*	powered by @cafewebmaster.com
*	free for private use
*	please support us with donations
*/
define("SLASH", stristr($_SERVER['SERVER_SOFTWARE'], "win") ? "\\" : "/");

$path	= (@$_GET['path'] ? $_GET['path'] : dirname(__FILE__));
$q		= @$_GET['q'];
$filter = (@$_GET['filter'] ? $_GET['filter'] : "*");
$self   = $_SERVER['PHP_SELF'];
$links  = (@$_GET['links'] ? 'checked="checked"' : '');
$regex  = (@$_GET['regex'] ? 'checked="checked"' : '');
$trim   = strlen($path) + 1;

function php_grep ($q, $path)
{
	global $filter, $trim, $links, $regex;
	$fp = opendir($path);
	$ret = "";
	while (($f = readdir($fp)) !== false)
	{
		$file_path = $path.SLASH.$f;
		if ($f == "." or $f == ".." or $file_path == __FILE__ or
		   (!$links and is_link($file_path)) or
		   (is_file($file_path) and !fnmatch($filter, $f)))
			continue;
		if (is_dir($file_path))
			$ret .= php_grep($q, $file_path);
		else if ($regex ? preg_match($q, file_get_contents($file_path)) :
				 stristr(file_get_contents($file_path), $q))
			$ret .= "<li>".htmlspecialchars(substr($file_path, $trim))."</li>\n";
	}
	closedir($fp);
	return $ret;
}

$results = "";
if ($q)
{
	$results = php_grep($q, $path);
	$results = ($results ? "<ol>\n".$results."</ol>\n" : '<p>No matches.</p>');
}

$path = htmlspecialchars($path);
$q = htmlspecialchars($q);
$filter = htmlspecialchars($filter);

echo <<<HTML
<form method="get" action="$self">
	<div><label for="path">Path:</label><input type="text" id="path" name="path" size="70" value="$path"></div>
	<div><label for="query">String:</label><input type="text" id="query" name="q" size="70" value="$q"></div>
	<div><label for="filter">Filter:</label><input type="text" id="filter" name="filter" size="30" value="$filter"></div>
	<div><label for="links">Symlinks:</label><input type="checkbox" id="links" name="links" $links></div>
	<div><label for="regex">RegExp:</label><input type="checkbox" id="regex" name="regex" $regex> (Don't forget the delimiters!)</div>
	<div><label></label><input type="submit" value="Search"></div>
</form>
$results
HTML;
?>
</body>
</html>
