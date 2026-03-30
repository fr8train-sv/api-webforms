# SkeletonMS

[![PHP](https://img.shields.io/badge/PHP-%5E8.3-blueviolet)](https://www.php.net/manual/en/)
[![poweredby](https://img.shields.io/badge/powered%20by-Slim4-green)](https://www.slimframework.com/docs/v4/)
![Bitbucket open issues](https://img.shields.io/bitbucket/issues/collettedevelopmentgroup/skeletonms)

Skeleton MicroService powered by [Slim4 Framework](https://www.slimframework.com/).

## Installation

Use Composer to install SkeletonMS locally.

```bash
composer create-project fr8train/skeleton-ms [directory] [version] 
```

If you want to include the git link to send updates to the repository, use:

```bash
composer create-project --keep-vcs fr8train/skeleton-ms [directory] [version]
```

## Post-Installation

**Please create a .env file at the root directory of the project.** 

If you would like to use the provided Database Singleton for MySQL, please add these to your .env file:

```dotenv
MYSQL_HOST=""
MYSQL_DATABASE=""
MYSQL_USERNAME=""
MYSQL_PASSWORD=""
```

Seriously, I just ran into this again. Without at least a blank .env file, you will receive a HTTP 500 error. A blank one is required for the project to run, and we recommend that you use it as the location to store any sensitive information such as passwords or keys. To retrieve any data stored here, use [vlucas/phpdotenv](https://packagist.org/packages/vlucas/phpdotenv).

If for some reason you get an error message indicating that a class in the code cannot be found, first try to reload the autoloaded classes through Composer.

```bash
composer dump-autoload
```

## Useful Design Practices and Libraries
### ramsey/uuid - PHP UUIDv7 libray
This was added to our project to increase security, as working with UUIDs continues to rise and leave predictable int unsigned IDs behind. We're using specifically his UUIDv7 object as v7 UUIDs are universally unique lexicographically-sortable identifiers meaning they're UUIDs that can be sorted and searched in databases in meaningful ways (see his documentation [here](https://uuid.ramsey.dev/en/stable/rfc4122/version7.html)). Here's the [main link](https://uuid.ramsey.dev/en/stable/) to his documentation.

Here is an example on how to use it with IDs set up in MySQL 8 using a `binary(16)` column.

```php
/* 
 *  FOR AN OBJECT USING RAMSEY'S UUIDv7 CLASS
 *  YOU CAN USE THE FOLLOWING TO GET THE BINARY ID
*/
    
$id = Ramsey\Uuid\Uuid::uuid7()->getBytes();

/* 
 *  FOR A RAW BINARY ID, USE THE FOLLOWING TO GET THE UUID OBJECT
*/

$id = Ramsey\Uuid\Uuid::fromBytes($id);
```

### sorskod/db PDO wrapper
This is a great little library for making DB work a lot simpler to manage. Information and practices can be found at his [Github Page](https://github.com/salebab/database) or his similar [Packgist](https://packagist.org/packages/sorskod/db). One thing that isn't implicit in his documentation to note, if you would like to bind your parameters for SQL Injection scrubbing please follow this example:

```php
$results = $this->db->execQueryString("insert into your_table (col_a, col_b, col_c)
values (:cola, :colb, :colc)", [
                'cola' => $valueA,
                'colb' => $valueB,
                'colc' => (int) $valueC // EXAMPLE: say col_c was created as a boolean,
                // don't forget to cast to type int or it will throw an error
]);
```

### CastableTrait
The Castable Trait provides a quick assignment to your PHP custom objects or classes as long as you remember to include default values in the constructor of your custom object or class.
```php
// CUSTOM CLASS DEFINITION
class ServiceResponse
{
    use CastableTrait;

    public int $http_code;
    public string $message;
    public mixed $payload;
    public Exception|Throwable|null $error;

    public function __construct(int       $http_code = 200,
                                string    $message = '',
                                mixed     $payload = null,
                                Exception|Throwable $error = null)
    {
        $this->http_code = $http_code;
        $this->message = $message;
        $this->payload = $payload;
        $this->error = $error;
    }
}

// ACTUAL CASTING
// BECAUSE OF DEFAULT VALUES ANY ONE OF THESE PROPERTIES CAN ACTUALLY BE SKIPPED IN DECLARATION
return ServiceResponse::cast([
    'http_code' => 500,
    'message' => 'Internal Server Error',
    'payload' => [
        'trace' => $exception->getTrace()
    ],
    'error' => $exception // instanceof Exception, Throwable, or null
]);
```
### ServiceResponse Service Design
Implementing Services by always returning ServiceResponses can prove to make your code much easier to navigate and reduce bugs by following the design:
```php
<?php

namespace services;

use models\HelloWorld;
use models\ServiceResponse;

class HelloWorldService
{
    public static function hello(HelloWorld $world) :  ServiceResponse
    {
        /*
         *  DO YOUR SERVICE LOGIC
         */

        // ANY OF THESE PROPERTIES ON SERVICE RESPONSE CAN TECHNICALLY BE BLANK BECAUSE OF DEFAULT VALUES
        if (isset($exception)) {
            return ServiceResponse::cast([
                'http_code' => 500,
                'message' => 'Internal Server Error',
                'payload' => [
                    'trace' => $exception->getTrace()
                ],
                'exception' => $exception // instanceof Exception, Throwable, or null
            ]);
        }

        return ServiceResponse::cast([
            'message' => $world->message,
            'payload' => [
                'Foo' => 'Bar'
            ]
        ]);
    }
}
```
See how handling the ServiceResponse makes Controller handling much easier:
```php
$serviceResponse = HelloWorldService::hello(ObjectFactory::loadClass(HelloWorld::class, $data));

return match ($serviceResponse->http_code) {
    200 => $this->json($response, [
        'message' => $serviceResponse->message,
        'payload' => $serviceResponse->payload,
    ]),
    default => $this->error($response, $this->log, $serviceResponse)
};
```
### Validation
We recently added both the `Required` attribute and the `ValidatableTrait` to our project. 

The `Required` attribute is a simple way to ensure that a property has to have a value when attempting to communicate with our API. Simply add it to a property to ensure that when the `->validate()` call is made, we will determine whether or not it is 'empty'. See both the HelloWorld Model and Service to see it in action. 

The `ValidatableTrait` provides the methodology for validating the incoming data as well as formulating the response in a single location.
## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](https://choosealicense.com/licenses/mit/)