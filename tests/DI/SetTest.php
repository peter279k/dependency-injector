<?php

namespace DependencyInjector\Test\DI;

use DependencyInjector\DI;
use DependencyInjector\Test\example\DataProvider\DatabaseObject;
use DependencyInjector\Test\example\Factory\Cache;
use DependencyInjector\Test\example\Singleton\MySingleton;
use PHPUnit\Framework\TestCase;

class SetTest extends TestCase
{
    /** Test that DI::set() stores something.
     * @test */
    public function storesSomethingNotCallable()
    {
        $something = [$this, 'nonExistingMethod'];

        DI::set('something', $something);

        self::assertEquals($something, DI::get('something'));
    }

    /** @test */
    public function storesValue()
    {
        $something = [$this, 'getDependencyExample'];
        DI::set('array', $something, false, true);

        $result = DI::get('array');

        self::assertSame($something, $result);
    }

    /** Test that the function got not executed before get.
     * @test */
    public function doesNotExecute()
    {
        $calls = 0;

        DI::set('dontCall', function () use (&$calls) {
            $calls = $calls + 1;
        });

        self::assertSame(0, $calls);
    }

    /** Test that DI::set() overrides created instances.
     * @test */
    public function overridesInstances()
    {
        DI::set('microtime', function () {
            return microtime(true);
        });
        $result1 = DI::get('microtime');

        DI::set('microtime', function () {
            return microtime(true);
        });
        $result2 = DI::get('microtime');

        self::assertNotSame($result1, $result2);
    }

    /** @test */
    public function acceptsClassNames()
    {
        DI::set('dbo', DatabaseObject::class);

        $dbo = DI::get('dbo');

        self::assertInstanceOf(DatabaseObject::class, $dbo);
    }

    /** @test */
    public function acceptsFactories()
    {
        DI::set('memcache', Cache::class);

        $memcache = DI::get('memcache');

        self::assertRegExp('/^' . preg_quote(Cache::class) . '#\d+$/', $memcache);
    }

    /** @test */
    public function usesGetInstanceOnSingletons()
    {
        DI::set('singleton', MySingleton::class);

        $singleton = DI::get('singleton');

        self::assertInstanceOf(MySingleton::class, $singleton);
    }

    /** @test */
    public function acceptsArrays()
    {
        DI::set([
            'dbo' => DatabaseObject::class,
            'dbo_class' => [DatabaseObject::class, true, true]
        ]);

        $dbo = DI::get('dbo');
        $class = DI::get('dbo_class');

        self::assertInstanceOf(DatabaseObject::class, $dbo);
        self::assertSame(DatabaseObject::class, $class);
    }

    /** @test */
    public function arraysWithStaticCallables()
    {
        DI::set([
            'fb' => [__CLASS__, 'initDependency'],
        ]);

        $result = DI::get('fb');

        self::assertSame('foobar', $result);
    }

    public static function initDependency()
    {
        return 'foobar';
    }
}
