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

echo "instanceID:$instanceId\n";

$pushMetrics = array();
foreach ($config -> metrics as $metrics) {
    foreach ($metrics as $metricName => $metric) {
       $className = "CWScripts\\plugins\\" . $metric->name;

       echo "MetricName=$metricName\n";
       echo "Metric-name=$metric->name\n";

       if (class_exists($metric->name)){
            $ref = $metric->name;
            $obj = new $ref();
       echo "CLASS EXISTS\n";
            }
       else {
            $monitoringController = new $className($metric, $metric->name);
       echo "NEW CLASS\n";
            }
        $metrics = $monitoringController->getMetric();
        if(is_array($metrics)) {
          $units = $monitoringController->getUnit();
        foreach ($metrics as $metricId => $value) {
         $pushMetrics[$metric->namespace][] =  array(
          'Unit'       => $units[$metricId],
          'MetricName' => $metric->name . " " . $metricId,
          'Value'      => $value,
          'Timestamp'  => time(),
          'Dimensions' => array(
            array('Name' => 'InstanceId', 'Value' => $instanceId),
            array('Name' => 'Metrics', 'Value' => $metricName)
            )
          );
         }
        }
        else {
          $pushMetrics[$metric->namespace][] =  array(
          'Unit'       => $monitoringController->getUnit(),
          'MetricName' => $metric->name,
          'Value'      => $metrics,
          'Timestamp'  => time(),
          'Dimensions' => array(
            array('Name' => 'InstanceId', 'Value' => $instanceId),
            array('Name' => 'Metrics', 'Value' => $metricName)
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
