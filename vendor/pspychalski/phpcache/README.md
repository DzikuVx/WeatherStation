PhpCache
========

PhpCache is object PHP >=5 cache wrapper that offers similar way of handling various caching mechanisms:
* APC (deprecated)
* Memcached
* Redis
* Filesystem (deprecated)
* Session (deprecated)
* Variable (temporary, not available between requests)

#Example usage

```
require_once 'PhpCache.php';
\PhpCache\PhpCache::$sDefaultMechanism = 'Redis';
$cache = \PhpCache\Factory::getInstance()->create();

$key = new \PhpCache\CacheKey('myKey');
$cache->set($key, 'Lorem ipsum');
if ($cache->check($key)) {
    var_dump($cache->get($key));
} else {
    var_dump('Data not found');
}
```

#Methods available for all caching mechanisms:
* set - sets cache value based on provided key
* get - get data from cache based on provided key. Returns false when no data for key
* check - checks if data for provided key is set
* clear - removed data for key
* clearAll - flushes cache