<?php
namespace CWScripts\plugins;
use CWScripts\MetricSignature;

class PortMonitor extends MetricSignature
{
  private $domain;
  private $port;



  public function __construct($config, $name)
     {
       parent::__construct($config, $name);
       $this->domain = $this->config->domain;
       $this->port = $this->config->port;
        
     }

  public function getMetric()
  {
          $starttime = microtime(true);
          $file      = @fsockopen($this->domain, $this->port, $errno, $errstr, 10);
          $stoptime  = microtime(true);
          $status    = 0;

          if (!$file) {
              $status = -1;  // Site is down
            echo "status NOT: " . $status . "\n";
            return 1;
          } else {

              fclose($file);
              $status = ($stoptime - $starttime) * 1000;
              $status = floor($status);
              echo "status OK: " . $status . "\n";
         return 0;
          }
  }

  public function getUnit()
  {
      return "None";
  }

  public function getAlarms()
  {
      return array(
       array("ComparisonOperator" => "LessThanThreshold",
        "Threshold" => 1,
        "Name" => $this->name)
        );
  }
}
