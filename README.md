GiantBomb.com PHP Api Wrapper
=============================

#### Requirements


* API Key from [GiantBomb][1]
* [Composer][2]


#### Installation

In your project root, run the following:

```sh
composer require giantbomb/giantbomb-php-api dev-master
```

This will create a `vendor` directory (if you dont already have one), and set up the autoloading classmap.


#### Usage

Once everything is installed, you should be able to load the composer autoloader in your code, and go from there!

Here is a very simple example:
```php
require __DIR__ . '/vendor/autoload.php';

use \GiantBomb\Client\GiantBombClient;

$config = array( 
	'apiKey' => 'your-api-key',
	'cache'  => array(
		'type'    => 'redis', // Or memcached
		'servers' => array( array( 'host' => 'localhost', 'port' => 6397, 'timeout' => 0 ) ), // weight is also a parameter for memcached
		'persistent' => true
	)
);
/**
Memcached also has the "options" parameter for specifiying Memcached options (via constants)
Redis also has the "password" parameter for auth, and "dbindex" for specifying your db 
*/


$client = GiantBombClient::factory( $config );


$requestArgs = array(
	'limit'  => 100, // Max of 100,
	'offset' => 0,   // Default is 0
//	'field_list' => 'name', // Default is not set
//	'sort'	 => 'name|asc', // Default is not set
//	'filter' => 'name:portal 2', // Default is not set
);

// Depending on the query, there are different options afailable. Check out the [service description][3] for more information.

$response = $client->getGames( $requestArgs );

if( $response->getStatusCode() === 1 ) {
    printf( "There are %d total results. Currently showing %d, starting at %d.\n<br /><br />", $response->getNumberOfTotalResults(), $response->getNumberOfPageResults(), $response->getOffset() );
    $games = $response->getResults();
    // HUGE dump. Careful
    //var_dump( $games );

    // Get more info on a single game
    $game = $games->get( 0 )->getDetail()->getResults();

    // All functions are magic functions based on the camel case of the key from the API result. This goes for anything returned from the API
    printf( "Game: %s<br />", $game->getName() );
} else {
    printf( "There was an error: %s", $response->getError() );
}
```

If you have any questions, feel free to make an issue, and I'll help you out!

#### Contribution

If you want to help out, just make a pull request!

[1]: http://api.giantbomb.com/
[2]: http://www.getcomposer.org/
[3]: https://github.com/giantbomb/giantbomb-php-api/blob/master/src/GiantBomb/Resources/config/giant-bomb-1_0.json


[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/giantbomb/giantbomb-php-api/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

