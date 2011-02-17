<?php

require_once 'util.php';

// This requires the PEAR Benchmark package.
require_once 'Benchmark/Timer.php';
require_once 'Benchmark/Iterate.php';

// Include the PieCrust app but with a root directory set
// to the test website's root dir.
define('PIECRUST_ROOT_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('BENCHMARKS_CACHE_DIR', PIECRUST_ROOT_DIR . '_cache');
require_once '../website/_piecrust/PieCrust.class.php';

function run_query($pieCrust, $uri, $bench = null)
{
	$page = new Page($pieCrust, $uri);
	if ($bench != null)
		$bench->setMarker('Created page');
	
	$renderer = new PageRenderer($pieCrust);
	if ($bench != null)
		$bench->setMarker('Created renderer');
	
	$page = $renderer->get($page, null, false);
	if ($bench != null)
		$bench->setMarker('Rendered page');
	
	return $page;
}

?>

<!doctype html>
<html>
	<head>
		<title>PieCrust Benchmarks</title>
	</head>
	<body>
<?php

echo '<h1>Rendering Markdown syntax page</h1>';
	
// Iteration benchmark.
echo '<h2>Iteration Benchmark</h2>';
ensure_cache(BENCHMARKS_CACHE_DIR, true);
$bench = new Benchmark_Iterate();
$bench->start();
$pieCrust = new PieCrust();
$pieCrust->setConfig(array('site' => array('debug' => true, 'enable_cache' => true)));
$runCount = 100;
$bench->run($runCount, 'run_query', $pieCrust, '/markdown-syntax', null);
$bench->stop();

function filter_end_marker($value) { return preg_match('/^end_/', $value['name']); }
function map_diff_time($value) { return $value['diff']; }
$prof = $bench->getProfiling();
$diffValues = array_map('map_diff_time', array_filter($prof, 'filter_end_marker'));
echo '<p>Ran page query '.$runCount.' times.</p>';
echo '<p>Median page query: <strong>'.(median($diffValues)*1000).'ms</strong></p>';
echo '<p>Average page query: <strong>'.(average($diffValues)*1000).'ms</strong></p>';
echo '<p>Max page query: <strong>'.(max($diffValues)*1000).'ms</strong></p>';

// Marked run
echo '<h2>Timed Benchmark</h2>';
ensure_cache(BENCHMARKS_CACHE_DIR, true);
$bench = new Benchmark_Timer();
$bench->start();
run_query($pieCrust, '/markdown-syntax', $bench);
$bench->stop();
$bench->display();


/*echo '<h1>Rendering HTML page</h1>';
$bench = new Benchmark_Iterate();
$bench->start();
$bench->run(10, 'file_get_contents', '_reference/markdown-syntax.html');
$bench->stop();
$bench->display();*/

?>
	</body>
</html>