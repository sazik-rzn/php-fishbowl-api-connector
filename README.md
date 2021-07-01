# php-fishbowl-api-connector
Fishbowl API(JSON) connector for PHP
## usage:
```php
$fb = new FishbowlAPIJSON;
$fb->SetHostInfo('IP or URL to Your Fishbowl API','Port for Your Fishbowl API');
$fb->SetAppInfo("Your App name", "Your App ID", "Your App description");
$fb->Login("Your username in Fishbowl","Your password in Fishbowl");
$fb->Logout();
```
## query example:
```php 
$fb->Request("ExecuteQueryRq", [
  "Query"=>"select datefirstship from PO limit 3"
]);
var_dump($fb->Response());
```

## response example (array with var_dump):
```console
[root@web]# php fishbowltest.php
  array(4) {
    ["code"]=>
    int(1000)
    ["message"]=>
    string(9) " Success!"
    ["is_error"]=>
    bool(false)
    ["data"]=>
    array(2) {
      ["statusCode"]=>
      int(1000)
      ["Rows"]=>
      array(1) {
        ["Row"]=>
        array(4) {
          [0]=>
          string(15) ""datefirstship""
          [1]=>
          string(23) ""2012-10-12 00:00:00.0""
          [2]=>
          string(23) ""2012-10-05 00:00:00.0""
          [3]=>
          string(23) ""2012-09-29 00:00:00.0""
        }
      }
    }
  }
[root@web]#
```
More info about Fishbowl API: https://www.fishbowlinventory.com/wiki/Fishbowl_API
