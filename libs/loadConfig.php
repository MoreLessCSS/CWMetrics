 <?php
require 'vendor/autoload.php';

use Aws\Credentials\CredentialProvider;
use Aws\CloudWatch\CloudWatchClient;

function getCloudWatchClient($config)
    {
    $provider = CredentialProvider::instanceProfile();
    $memoizedProvider = CredentialProvider::memoize($provider);

    $client = new CloudWatchClient([
        'region'      => 'eu-central-1',
        'version'     => '2010-08-01',
        'credentials' => $memoizedProvider
        ]);
        return $client;
    }

    function getConfigFile()
    {
        return json_decode(file_get_contents(APPLICATION_PATH.'/config/MasterNode/config.json'));
    }
