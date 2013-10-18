# Fishbans-php

__Fishbans-php__ is a very small PHP wrapper for for the [Fishbans](http://fishbans.com/) [API](http://fishbans.com/docs/) service.

Fishbans-php is made to help you fetch and work with Fishbans data easily and quickly, and is very small and lightweight

## Installation

Copy the file to somewhere you can access it, and include it. Create a new instance of the main class and you'll be ready to go!

```php
<?php
include 'Fishbans.php';
$fishbans = new Fishbans;
```


## Usage

We have two methods available for accessing the API - getBans() and getBansService(), matching up to the two pages on their [API documentation](http://fishbans.com/docs/)

Each of these methods grabs data from Fishbans and gives you an object to use to access the data.

```php
<?php
$bans = $fishbans->getBans('nasonfish');
var_dump($bans->raw()); // Get the raw array of json data we got.
echo $bans->ban_total(); // Get the total amount of bans the user has.
foreach($bans->bans() as $ip => $name){ // Get all the bans this user has as array($ip => $name)
   echo "Don't join the server " . $ip . '!';
}
var_dump($bans->bans_service()['mcbans']); // Get the almost-raw data of all bans by service, dump mcbans ban amount and info.
```
The `$fishbans->getBansService('username', 'service');` returns the same object, but with only the single service data available, currently.
