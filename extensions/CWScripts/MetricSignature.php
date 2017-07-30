<?php
namespace CWScripts;

abstract class MetricSignature
{
    protected $config;
    protected $name;

    public function __construct($config, $name)
    {
        $this->config = $config;
        $this->name = $name;
        echo "this->config: . $config . "\n";
        echo "this->name:" . $name . "\n";
    }
    abstract public function getMetric();

    abstract public function getUnit();

    abstract public function getAlarms();

    public function getMetricName($alarm) {
      return $this->name;
    }
}
