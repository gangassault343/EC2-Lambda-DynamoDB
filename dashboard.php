<?php
require 'vendor/autoload.php';

use Aws\DynamoDb\DynamoDbClient;
use Aws\Exception\AwsException;

$region = 'ap-south-1';
$tableName = 'EC2CloudTrailLogs';

$client = new DynamoDbClient([
    'region'  => $region,
    'version' => 'latest'
]);

try {
    // Scan table (for small datasets)
    $result = $client->scan([
        'TableName' => $tableName,
        'Limit' => 100
    ]);

    $items = $result['Items'];

} catch (AwsException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Live VM Activity Dashboard</title>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="10">
    <style>
        body {
            font-family: Arial;
            background: #f4f6f9;
            padding: 20px;
        }

        h2 {
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background: #1f2937;
            color: white;
        }

        tr:hover {
            background: #f1f5f9;
        }

        .idtag {
            font-family: monospace;
            background: #eef2ff;
            padding: 3px 6px;
            border-radius: 4px;
        }

        .running { color: green; font-weight: bold; }
        .stopped { color: red; font-weight: bold; }
        .terminated { color: gray; font-weight: bold; }
    </style>
</head>

<body>

<h2>🚀 Live Arun's EC2 Activity Dashboard</h2>

<table>
    <thead>
        <tr>
            <th>Instance ID</th>
            <th>Timestamp</th>
            <th>Availability Zone</th>
            <th>Private IP</th>
            <th>Public IP</th>
            <th>State</th>
        </tr>
    </thead>
    <tbody>

<?php foreach ($items as $item): 
    $instanceId = $item['InstanceId']['S'] ?? '';
    $timestamp = $item['LastUpdated']['S'] ?? '';
    $event = $item['State']['S'] ?? '';
    $az     = $item['AvailabilityZone']['S'] ?? '';
    $privateIP  = $item['PrivateIP']['S'] ?? '';
    $publicIP   = $item['PublicIP']['S'] ?? '';

     
    $class = '';
    if (strpos($event, 'Start') !== false) $class = 'running';
   if (strpos($event, 'Stop') !== false) $class = 'stopped';
    if (strpos($event, 'Terminate') !== false) $class = 'terminated';
?>

<tr>
    <td><span class="idtag"><?= htmlspecialchars($instanceId) ?></span></td>
    <td><?= htmlspecialchars($timestamp) ?></td>
    <td><?= htmlspecialchars($az) ?></td>
    <td><?= htmlspecialchars($privateIP) ?></td>
    <td><?= htmlspecialchars($publicIP) ?></td>
    <td class="<?= $class ?>"><?= htmlspecialchars($event) ?></td>
    
</tr>

<?php endforeach; ?>

    </tbody>
</table>

</body>
<script>
    setTimeout(function() {
        window.location.reload();
    }, 10000); // 10 seconds
</script>
</html>
