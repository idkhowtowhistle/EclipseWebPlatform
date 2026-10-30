<?php
/**
 * Setup artifacts consumed by the Eclipse launcher.
 *
 *   GET /setup/2013/version      -> current client version string
 *   GET /setup/2013/<version>    -> gzipped tar of the 2013 client
 *   GET /setup/launcher          -> EclipseLauncher_win-x64.exe
 *
 * Files live outside the repository (SITE_CONFIG["games"]["setupDir"]) so the
 * client package and the built launcher are never committed.
 */
require __DIR__ . "/api/private/config.php";

$type = $_GET["type"] ?? "";
$year = (int)($_GET["year"] ?? SITE_CONFIG["games"]["clientVersion"]);
$version = (string)($_GET["version"] ?? "");
$dir = rtrim((string)SITE_CONFIG["games"]["setupDir"], "/\\");

function setupFail($code = 404, $msg = "Not found")
{
	http_response_code($code);
	header("content-type: text/plain; charset=utf-8");
	header("cache-control: no-store");
	die($msg);
}

function setupVersionFile($dir, $year)
{
	return $dir . "/" . (int)$year . "/version";
}

function setupCurrentVersion($dir, $year)
{
	$path = setupVersionFile($dir, $year);
	if (!is_file($path)) return false;
	$version = trim((string)file_get_contents($path));
	return $version === "" ? false : $version;
}

function setupSendFile($path, $filename, $contentType)
{
	if (!is_file($path) || !is_readable($path)) setupFail(404, "Artifact missing");

	$size = filesize($path);
	$fp = fopen($path, "rb");
	if ($fp === false) setupFail(500, "Cannot read artifact");

	header("content-type: " . $contentType);
	header("content-length: " . $size);
	header("content-disposition: attachment; filename=\"" . $filename . "\"");
	header("cache-control: public, max-age=300");
	header("x-content-type-options: nosniff");
	header("accept-ranges: bytes");

	// basic single-range support so interrupted downloads can resume
	if (!empty($_SERVER["HTTP_RANGE"]) && preg_match('/^bytes=(\d*)-(\d*)$/', $_SERVER["HTTP_RANGE"], $m)) {
		$start = $m[1] === "" ? 0 : (int)$m[1];
		$end = $m[2] === "" ? $size - 1 : (int)$m[2];
		if ($start > $end || $end >= $size) {
			fclose($fp);
			header("content-range: bytes */" . $size);
			setupFail(416, "Invalid range");
		}

		fseek($fp, $start);
		$length = $end - $start + 1;

		http_response_code(206);
		header("content-range: bytes " . $start . "-" . $end . "/" . $size);
		header("content-length: " . $length);

		$remaining = $length;
		while ($remaining > 0 && !feof($fp)) {
			$chunk = fread($fp, min(262144, $remaining));
			if ($chunk === false || $chunk === "") break;
			echo $chunk;
			$remaining -= strlen($chunk);
			flush();
		}
		fclose($fp);
		exit;
	}

	fpassthru($fp);
	fclose($fp);
	exit;
}

switch ($type) {
	case "version":
		$current = setupCurrentVersion($dir, $year);
		if ($current === false) setupFail(404, "No client package for " . (int)$year);

		header("content-type: text/plain; charset=utf-8");
		header("cache-control: no-store");
		die($current);

	case "client":
		$current = setupCurrentVersion($dir, $year);
		if ($current === false) setupFail(404, "No client package for " . (int)$year);
		// only the advertised version is served, never an arbitrary file name
		if ($version !== $current) setupFail(404, "Unknown version");

		setupSendFile($dir . "/" . (int)$year . "/client.tar.gz", "client.tar.gz", "application/gzip");

	case "launcher":
		// the updater may request /setup/<filename> instead of /setup/launcher
		$fileArg = $_GET["filename"] ?? null;
		$filename = (string)SITE_CONFIG["games"]["launcherFile"];
		if ($fileArg !== null && $fileArg !== $filename) setupFail(404, "Unknown setup artifact");

		setupSendFile($dir . "/" . $filename, $filename, "application/octet-stream");

	default:
		setupFail(404, "Unknown setup artifact");
}
