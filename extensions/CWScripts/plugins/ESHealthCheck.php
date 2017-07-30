<?php
namespace CWScripts\plugins;
use CWScripts\MetricSignature;
use Closure;

class ESHealthCheck extends MetricSignature
{
  private $elasticUrl;
  private $state;

public function __construct($config, $name)
     {
       parent::__construct($config, $name);
       $this->elasticUrl = $this->config->elasticUrl;
     }

public function getMetric()
   {
       $elasticHealth = @file_get_contents($this->elasticUrl);
       if ($elasticHealth === false) {
          $this->state = false;
           return 0;
       }
       $elasticHealth = json_decode($elasticHealth, true);
       $status = array("green" => 2, "yellow" => 1, "red" => 0);
       return array(
         "status" => $status[$elasticHealth['status']],
         "number_of_nodes" => $elasticHealth['number_of_nodes'],
         "number_of_data_nodes" => $elasticHealth['number_of_data_nodes'],
         "active_primary_shards" => $elasticHealth['active_primary_shards'],
         "active_shards" => $elasticHealth['active_shards'],
         "unassigned_shards" => $elasticHealth['unassigned_shards'],
         "active_shards_percent_as_number" => $elasticHealth['active_shards_percent_as_number']
       );
   }
   public function getUnit($alarmName=null)
   {
   if ($this->state === false){
   return "None";
   exit;
   };
       if($alarmName === null) {
         return array(
         "status" => "None",
         "number_of_nodes" => "Count",
         "number_of_data_nodes" => "Count",
         "active_primary_shards" => "Count",
         "active_shards" => "Count",
         "unassigned_shards" => "Count",
         "active_shards_percent_as_number" => "Percent"
       );
     }
     else {
       switch ($alarmName) {
         case $this->name . " status warning":
           return "None";
         case $this->name . " status error":
         echo "HIER UNITS RETURN NON\n";
           return "None";
       }
     }
   }

public function getAlarms()
    {
        return array(
                    array("ComparisonOperator" => "LessThanThreshold",
                          "Threshold" => 2,
                          "Name" => $this->name . " status warning"
                    ),
                    array("ComparisonOperator" => "LessThanThreshold",
                          "Threshold" => 1,
                          "Name" => $this->name . " status error"
                    )
        );
    }

    /**
     * @return The metrics name associate to an alarm name.
     */

    public function getMetricName($alarm) {
      switch ($alarm) {
        case $this->name . " status warning":
          return $this->name . " status";
        case $this->name . " status error":
          return $this->name . " status";
      }
    }
}