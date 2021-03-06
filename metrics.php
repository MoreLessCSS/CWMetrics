<?php
define('APPLICATION_PATH', realpath(dirname(__FILE__)));
include APPLICATION_PATH . '/vendor/autoload.php';
require_once  './libs/loadConfig.php';
use Aws\CloudWatch\CloudWatchClient;

$config = getConfigFile();
if ($config === false)
    {
        echo "please provide a valid configuration";
        die();
    }
$instanceId = file_get_contents("http://169.254.169.254/latest/meta-data/instance-id");
$instanceName = file_get_contents("http://169.254.169.254/latest/meta-data/hostname");

echo "\ninstanceID:$instanceId\n";

$pushMetrics = array();
foreach ($config -> metrics as $metrics) {
    foreach ($metrics as $metricName => $metric) {
    $className = "CWScripts\\plugins\\" . $metric->plugin;
    $CWController = new $className($metric, $metric->plugin);
    $metrics = $CWController->getMetric();

        if(is_array($metrics)) {
        echo "hier 1\n";
          $units = $CWController->getUnit();
        foreach ($metrics as $metricId => $value) {
         $pushMetrics[$metric->namespace][] =  array(
          'Unit'       => $units[$metricId],
          'MetricName' => $metricName . " " . $metricId,
          'Value'      => $value,
          'Timestamp'  => time(),
          'Dimensions' => array(
            array('Name' => 'InstanceId', 'Value' => $instanceId),
            array('Name' => 'Instance Name', 'Value' => $instanceName)
            )
          );
         }
        }
        else {
        echo "hier 2\n";
          $pushMetrics[$metric->namespace][] =  array(
          'Unit'       => $CWController->getUnit(),
          'MetricName' => $metricName,
          'Value'      => $metrics,
          'Timestamp'  => time(),
          'Dimensions' => array(
            array('Name' => 'InstanceId', 'Value' => $instanceId),
            array('Name' => 'Instance Name', 'Value' => $instanceName)
            )
          );

        }
    }
}

$CWClient = getCloudWatchClient($config);

foreach ($pushMetrics as $namespace => $metricData) {
    $CWClient->putMetricData(array(
            'Namespace'  =>$namespace,
            'MetricData' => $metricData
    ));
}
