<?php
namespace PhpCache;

require_once 'src/PhpCache.php';

class PhpCacheTest extends \PHPUnit_Framework_TestCase {

    public function testCreateFactory() {
        $oFactory = PhpCache::getInstance();
        $this->assertInstanceOf('PhpCache\PhpCache', $oFactory);
    }

    /**
     * @expectedException \PhpCache\Exception
     */
    public function testUnexistingConnector() {
        $oFactory = PhpCache::getInstance();
        $oFactory->create('Malina');
    }

    private function processConnector($sName) {
        $oFactory = PhpCache::getInstance();
        $oCache = $oFactory->create($sName);

        $this->assertInstanceOf('phpCache\\' . $sName, $oCache);

        $oCache->clearAll();

        $aKeys = array();
        $aKeys[0] = new CacheKey('Test1');
        $aKeys[1] = new CacheKey('Test1', 'Prop1');
        $aKeys[2] = new CacheKey($oFactory);
        $aKeys[3] = new CacheKey($oFactory, 'Prop2');

        foreach($aKeys as $oKey) {
            $this->assertInstanceOf('phpCache\CacheKey', $oKey);

            $this->assertFalse($oCache->check($oKey));

            $sValue = 'test Value';
            $oCache->set($oKey, $sValue);

            $this->assertTrue($oCache->check($oKey));
            $this->assertEquals($sValue, $oCache->get($oKey));

            $oCache->clear($oKey);

            $this->assertFalse($oCache->check($oKey));
            $this->assertFalse($oCache->get($oKey));

            $aSetValue = array(1 => 2, 'key' => 'This is key');

            $oCache->set($oKey, $aSetValue);

            $aGet = $oCache->get($oKey);

            $this->assertInternalType('array', $aGet);

            $this->assertArrayHasKey(1, $aGet);
            $this->assertEquals(2, $aGet[1]);

            $this->assertArrayHasKey('key', $aGet);
            $this->assertEquals('This is key', $aGet['key']);

            $oCache->clear($oKey);

            $aSetValue = new \stdClass();
            $aSetValue->key1 = 2;
            $aSetValue->key2 = 'This is key';

            $oCache->set($oKey, $aSetValue);

            $aGet = $oCache->get($oKey);

            $this->assertInstanceOf('\stdClass', $aGet);
            $this->assertEquals(2, $aGet->key1);
            $this->assertEquals('This is key', $aGet->key2);

            $oCache->clear($oKey);
        }

    }

    public function testApc() {
        $this->processConnector('Apc');
    }

    public function testMemcached() {
        $this->processConnector('Memcached');
    }

    public function testFile() {
        $this->processConnector('File');
    }

    public function testSession() {
        $this->processConnector('Session');
    }

    public function testVariable() {
        $this->processConnector('Variable');
    }

    public function testRedis() {
        $this->processConnector('Redis');
    }

}
 