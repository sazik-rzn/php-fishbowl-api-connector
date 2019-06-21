# php-fishbowl-api-connector
Fishbowl API(JSON) connector for PHP
## usage:
```php
$fb = new FishbowlAPIJSON;
$fb->SetHostInfo('IP or URL to Your Fishbowl API','Port for Your Fishbowl API');
$fb->SetAppInfo("Your App name", "Your App ID", "Your App description");
$fb->Login("Your username in Fishbowl","Your password in Fishbowl");
```
## query example:
```php 
$fb->Request("ExecuteQueryRq", [
  "Query"=>"select PO.datefirstship, POITEM.PARTNUM,PO.NUM from PO inner join POITEM ON POITEM.POID = PO.ID WHERE (POITEM.STATUSID = 10 OR POITEM.STATUSID = 30) ORDER BY datefirstship ASC"
]);
```

## response example (var_dump):
```
["statusCode"]=> int(1000)
  ["Rows"]=> array(1) {
    ["Row"]=> array(390) {
      [0]=>
      string(31) ""datefirstship","PARTNUM","NUM""
      [1]=>
      string(58) ""2018-09-04 00:00:00.0","****/15.5","2***""
      [2]=>
      string(58) ""2018-12-11 00:00:00.0","Hydro-***** Y/17","***3""
      [3]=>
      string(60) ""2018-12-11 00:00:00.0","******","**53""
      ...
```
More info about Fishbowl API: https://www.fishbowlinventory.com/wiki/Fishbowl_API
