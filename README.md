# Ikarus SPS Client
The client package is an independent library to connect to services using unix sockets or tcp/ip.  
It is basically designed to communicate with a running sps, but you can use it anywhere.

#### Installation
```bin
$ composer require ikarus/sps-client
```

#### Usage
```php
<?php
use Ikarus\SPS\Client\UnixClient;
use Ikarus\SPS\Client\Command\Command;

$client = new UnixClient('/tmp/ikarus-sps.sock');
$response = $client->sendCommand($cmd = new Command('status', ['battery', 'power', 'problems']));

if($response == $client::STATUS_OK) {
    echo $cmd->getResponse(); // Whatever your sps answered
} else {
    echo "Failed to execute command";
}
```
To use tcp/ip clients, change the $client:
````php
<?php
use Ikarus\SPS\Client\TcpClient;

$client = new TcpClient('192.168.1.100', 8686);
// ...
````