<?php
require(__DIR__.'/src/autoload.php');

$input = '';
$output = '';
$timing = Array(
	'start' => microtime(true)
);
$mem = Array(
	'start' => memory_get_peak_usage()
);
if (!empty($_POST['action'])) {
	$obj = new \hexydec\jslite\jslite();
	$timing['fetch'] = microtime(true);
	$mem['fetch'] = memory_get_peak_usage();

	// handle a URL
	if (!empty($_POST['url'])) {

		// parse the URL
		if (($url = parse_url($_POST['url'])) === false) {
			trigger_error('Could not parse URL: The URL is not valid', E_USER_WARNING);

		// check the host name
		} elseif (!isset($url['host'])) {
			trigger_error('Could not parse URL: No host was supplied', E_USER_WARNING);

		// open the document
		} elseif (($input = $obj->open($_POST['url'], $error)) === false) {
			trigger_error('Could not load Javascript: '.$error, E_USER_WARNING);
		}

	// handle directly entered source code
	} elseif (empty($_POST['source'])) {
		trigger_error('No URL or Javascript source was posted', E_USER_WARNING);

	// load the source code
} elseif (!$obj->load($_POST['source'], $error)) {
		trigger_error('Could not parse Javascript: '.$error, E_USER_WARNING);

	// record the HTML
	} else {
		$input = $_POST['source'];
	}

	// if there is some input
	if ($input) {
		$timing['parse'] = microtime(true);
		$mem['parse'] = memory_get_peak_usage();

		// minify the input
		$obj->minify();

		// record timings
		$timing['minify'] = microtime(true);
		$mem['minify'] = memory_get_peak_usage();
		$output = $obj->output();
		$timing['output'] = microtime(true);
		$mem['output'] = memory_get_peak_usage();
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Hexydec JSLite Minifier</title>
		<style>
			html, body {
				margin: 0;
				font-family: Segoe UI;
			}
			.minify__form {
				height: 100vh;
				display: flex;
			}
			.minify__form-wrap {
				display: flex;
				flex-direction: column;
				flex: 1 1 auto;
			}
			.minify__form-heading {
				margin: 10px 10px 0 10px;
				flex: 0 0 auto;
			}
			.minify__form-input {
				flex: 1 1 auto;
				display: flex;
				flex-direction: column;
				margin: 10px 10px 0 10px;
			}
			.minify__form-url {
				display: flex;
				margin: 10px 10px 0 10px;
			}
			.minify__form-url-box {
				flex: 1 1 auto;
			}
			.minify__form-input-box {
				display: block;
				box-sizing: border-box;
				width: 100%;
				flex: 1 1 auto;
			}
			.minify__table {
				margin: 10px;
				font-size: 0.9em;
			}
			.minify__table th, .minify__table td {
				padding: 5px;
				text-align: center;
				border-bottom: 1px solid #CCC;
			}
			.minify__table td:first-child {
				text-align: left;
				font-weight: bold;
			}
		</style>
	</head>
	<body>
		<form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" accept-charset="<?= mb_internal_encoding(); ?>" class="minify__form">
			<div class="minify__form-wrap">
				<h1 class="minify__form-heading">Javascript Minifier</h1>
				<div class="minify__form-input">
					<label for="source">Paste Javascript:</label>
					<textarea name="source" id="source" class="minify__form-input-box"><?= htmlspecialchars($input); ?></textarea>
				</div>
				<div class="minify__form-url">
					<label for="url">or External URL:</label>
					<input type="url" name="url" id="url" class="minify__form-url-box" />
					<button name="action" value="url">Go</button>
				</div>
				<?php if ($output) { ?>
					<div class="minify__form-input">
						<label for="output">Output Javascript:</label>
						<textarea id="output" class="minify__form-input-box"><?= htmlspecialchars($output); ?></textarea>
					</div>
					<table class="minify__table">
						<thead>
							<tr>
								<th></th>
								<th>Input (bytes)</th>
								<th>Output (bytes)</th>
								<th>Diff (bytes)</th>
								<th>% of Original</th>
								<th></th>
								<th>Load</th>
								<th>Parse</th>
								<th>Minify</th>
								<th>Output</th>
								<th>Total (sec) / Peak (kb)</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$ilen = strlen($input);
								$olen = strlen($output);
								$gilen = strlen(gzencode($input));
								$golen = strlen(gzencode($output));
							?>
							<tr>
								<td>Uncompressed</td>
								<td><?= htmlspecialchars(number_format($ilen)); ?></td>
								<td><?= htmlspecialchars(number_format($olen)); ?></td>
								<td><?= htmlspecialchars(number_format($ilen - $olen)); ?></td>
								<td><?= htmlspecialchars(number_format((100 / $ilen) * $olen)); ?>%</td>
								<td style="font-weight:bold;">Time (sec)</td>
								<td><?= htmlspecialchars(number_format($timing['fetch'] - $timing['start'], 4)); ?>s</td>
								<td><?= htmlspecialchars(number_format($timing['parse'] - $timing['fetch'], 4)); ?>s</td>
								<td><?= htmlspecialchars(number_format($timing['minify'] - $timing['parse'], 4)); ?>s</td>
								<td><?= htmlspecialchars(number_format($timing['output'] - $timing['minify'], 4)); ?>s</td>
								<td><?= htmlspecialchars(number_format($timing['output'] - $timing['fetch'], 4)); ?>s</td>
							</tr>
							<tr>
								<td>Compressed</td>
								<td><?= htmlspecialchars(number_format($gilen)); ?></td>
								<td><?= htmlspecialchars(number_format($golen)); ?></td>
								<td><?= htmlspecialchars(number_format($gilen - $golen)); ?></td>
								<td><?= htmlspecialchars(number_format((100 / $gilen) * $golen)); ?>%</td>
								<td style="font-weight:bold;">Peak (kb)</td>
								<td><?= htmlspecialchars(number_format($mem['fetch'] / 1024, 0)); ?>kb</td>
								<td><?= htmlspecialchars(number_format($mem['parse'] / 1024, 0)); ?>kb</td>
								<td><?= htmlspecialchars(number_format($mem['minify'] / 1024, 0)); ?>kb</td>
								<td><?= htmlspecialchars(number_format($mem['output'] / 1024, 0)); ?>kb</td>
								<td><?= htmlspecialchars(number_format(memory_get_peak_usage() / 1024, 0)); ?>kb</td>
							</tr>
						</tbody>
					</table>
				<?php } ?>
			</div>
		</form>
	</body>
</html>
