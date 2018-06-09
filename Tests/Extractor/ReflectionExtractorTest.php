<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\PropertyInfo\Tests\Extractor;

use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\Tests\Fixtures\AdderRemoverDummy;
use Symfony\Component\PropertyInfo\Type;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class ReflectionExtractorTest extends TestCase
{
    /**
     * @var ReflectionExtractor
     */
    private $extractor;

    protected function setUp()
    {
        $this->extractor = new ReflectionExtractor();
    }

    public function testGetProperties()
    {
        $this->assertSame(
            array(
                'bal',
                'parent',
                'collection',
                'B',
                'Guid',
                'array',
                'emptyVar',
                'foo',
                'foo2',
                'foo3',
                'foo4',
                'foo5',
                'files',
                'a',
                'DOB',
                'Id',
                '123',
                'self',
                'realParent',
                'c',
                'd',
                'e',
                'f',
                'g',
            ),
            $this->extractor->getProperties('Symfony\Component\PropertyInfo\Tests\Fixtures\Dummy')
        );

        $this->assertNull($this->extractor->getProperties('Symfony\Component\PropertyInfo\Tests\Fixtures\NoProperties'));
    }

    public function testGetPropertiesWithCustomPrefixes()
    {
        $customExtractor = new ReflectionExtractor(array('add', 'remove'), array('is', 'can'));

        $this->assertSame(
            array(
                'bal',
                'parent',
                'collection',
                'B',
                'Guid',
                'array',
                'emptyVar',
                'foo',
                'foo2',
                'foo3',
                'foo4',
                'foo5',
                'files',
                'c',
                'd',
                'e',
                'f',
            ),
            $customExtractor->getProperties('Symfony\Component\PropertyInfo\Tests\Fixtures\Dummy')
        );
    }

    public function testGetPropertiesWithNoPrefixes()
    {
        $noPrefixExtractor = new ReflectionExtractor(array(), array(), array());

        $this->assertSame(
            array(
                'bal',
                'parent',
                'collection',
                'B',
                'Guid',
                'array',
                'emptyVar',
                'foo',
                'foo2',
                'foo3',
                'foo4',
                'foo5',
                'files',
            ),
            $noPrefixExtractor->getProperties('Symfony\Component\PropertyInfo\Tests\Fixtures\Dummy')
        );
    }

    public function testGetPropertiesPhp71()
    {
        $noPrefixExtractor = new ReflectionExtractor();

        $this->assertSame(
            array(
                'string',
                'stringOrNull',
                'foo',
                'buz',
                'bar',
                'baz',
                'intWithAccessor',
            ),
            $noPrefixExtractor->getProperties('Symfony\Component\PropertyInfo\Tests\Fixtures\Php71Dummy')
        );
    }

    /**
     * @dataProvider typesProvider
     */
    public function testExtractors($property, array $type = null)
    {
        $this->assertEquals($type, $this->extractor->getTypes('Symfony\Component\PropertyInfo\Tests\Fixtures\Dummy', $property, array()));
    }

    public function typesProvider()
    {
        return array(
            array('a', null),
            array('b', array(new Type(Type::BUILTIN_TYPE_OBJECT, true, 'Symfony\Component\PropertyInfo\Tests\Fixtures\ParentDummy'))),
            array('c', array(new Type(Type::BUILTIN_TYPE_BOOL))),
            array('d', array(new Type(Type::BUILTIN_TYPE_BOOL))),
            array('e', null),
            array('f', array(new Type(Type::BUILTIN_TYPE_ARRAY, false, null, true, new Type(Type::BUILTIN_TYPE_INT), new Type(Type::BUILTIN_TYPE_OBJECT, false, 'DateTime')))),
            array('donotexist', null),
            array('staticGetter', null),
            array('staticSetter', null),
            array('self', array(new Type(Type::BUILTIN_TYPE_OBJECT, false, 'Symfony\Component\PropertyInfo\Tests\Fixtures\Dummy'))),
            array('realParent', array(new Type(Type::BUILTIN_TYPE_OBJECT, false, 'Symfony\Component\PropertyInfo\Tests\Fixtures\ParentDummy'))),
        );
    }

    /**
     * @dataProvider php7TypesProvider
     */
    public function testExtractPhp7Type($property, array $type = null)
    {
        $this->assertEquals($type, $this->extractor->getTypes('Symfony\Component\PropertyInfo\Tests\Fixtures\Php7Dummy', $property, array()));
    }

    public function php7TypesProvider()
    {
        return array(
            array('foo', array(new Type(Type::BUILTIN_TYPE_ARRAY, false, null, true))),
            array('bar', array(new Type(Type::BUILTIN_TYPE_INT))),
            array('baz', array(new Type(Type::BUILTIN_TYPE_ARRAY, false, null, true, new Type(Type::BUILTIN_TYPE_INT), new Type(Type::BUILTIN_TYPE_STRING)))),
            array('buz', array(new Type(Type::BUILTIN_TYPE_OBJECT, false, 'Symfony\Component\PropertyInfo\Tests\Fixtures\Php7Dummy'))),
            array('biz', array(new Type(Type::BUILTIN_TYPE_OBJECT, false, 'stdClass'))),
            array('donotexist', null),
        );
    }

    /**
     * @dataProvider php71TypesProvider
     */
    public function testExtractPhp71Type($property, array $type = null)
    {
        $this->assertEquals($type, $this->extractor->getTypes('Symfony\Component\PropertyInfo\Tests\Fixtures\Php71Dummy', $property, array()));
    }

    public function php71TypesProvider()
    {
        return array(
            array('foo', array(new Type(Type::BUILTIN_TYPE_ARRAY, true, null, true))),
            array('buz', array(new Type(Type::BUILTIN_TYPE_NULL))),
            array('bar', array(new Type(Type::BUILTIN_TYPE_INT, true))),
            array('baz', array(new Type(Type::BUILTIN_TYPE_ARRAY, false, null, true, new Type(Type::BUILTIN_TYPE_INT), new Type(Type::BUILTIN_TYPE_STRING)))),
            array('donotexist', null),
            array('string', array(new Type(Type::BUILTIN_TYPE_STRING, false))),
            array('stringOrNull', array(new Type(Type::BUILTIN_TYPE_STRING, true))),
            array('intPrivate', array(new Type(Type::BUILTIN_TYPE_INT, false))),
            array('intWithAccessor', array(new Type(Type::BUILTIN_TYPE_INT, false))),
        );
    }

    public function testExtractPhp71TypeWithParentConstructor()
    {
        $property = 'string';
        $type = array(new Type(Type::BUILTIN_TYPE_STRING, false));
        $this->assertEquals($type, $this->extractor->getTypes('Symfony\Component\PropertyInfo\Tests\Fixtures\Php71DummyChild', $property, array()));
        $this->assertEquals($type, $this->extractor->getTypes('Symfony\Component\PropertyInfo\Tests\Fixtures\Php71DummyChild2', $property, array()));
        $this->assertEquals($type, $this->extractor->getTypes('Symfony\Component\PropertyInfo\Tests\Fixtures\Php71DummyChild3', $property, array()));
    }

    /**
     * @dataProvider defaultValueProvider
     */
    public function testExtractWithDefaultValue($property, $type)
    {
        $this->assertEquals($type, $this->extractor->getTypes('Symfony\Component\PropertyInfo\Tests\Fixtures\DefaultValue', $property, array()));
    }

    public function defaultValueProvider()
    {
        return array(
            array('defaultInt', array(new Type(Type::BUILTIN_TYPE_INT, false, 'Symfony\Component\PropertyInfo\Tests\Fixtures\DefaultValue'))),
            array('defaultFloat', array(new Type(Type::BUILTIN_TYPE_FLOAT, false, 'Symfony\Component\PropertyInfo\Tests\Fixtures\DefaultValue'))),
            array('defaultString', array(new Type(Type::BUILTIN_TYPE_STRING, false, 'Symfony\Component\PropertyInfo\Tests\Fixtures\DefaultValue'))),
            array('defaultArray', array(new Type(Type::BUILTIN_TYPE_ARRAY, false, 'Symfony\Component\PropertyInfo\Tests\Fixtures\DefaultValue'))),
            array('defaultNull', array(new Type(Type::BUILTIN_TYPE_NULL, false, 'Symfony\Component\PropertyInfo\Tests\Fixtures\DefaultValue'))),
        );
    }

    /**
     * @dataProvider getReadableProperties
     */
    public function testIsReadable($property, $expected)
    {
        $this->assertSame(
            $expected,
            $this->extractor->isReadable('Symfony\Component\PropertyInfo\Tests\Fixtures\Dummy', $property, array())
        );
    }

    public function getReadableProperties()
    {
        return array(
            array('bar', false),
            array('baz', false),
            array('parent', true),
            array('a', true),
            array('b', false),
            array('c', true),
            array('d', true),
            array('e', false),
            array('f', false),
            array('g', true),
            array('Id', true),
            array('id', true),
            array('Guid', true),
            array('guid', false),
            array('guid', false),
        );
    }

    /**
     * @dataProvider getWritableProperties
     */
    public function testIsWritable($property, $expected)
    {
        $this->assertSame(
            $expected,
            $this->extractor->isWritable('Symfony\Component\PropertyInfo\Tests\Fixtures\Dummy', $property, array())
        );
    }

    public function getWritableProperties()
    {
        return array(
            array('bar', false),
            array('baz', false),
            array('parent', true),
            array('a', false),
            array('b', true),
            array('c', false),
            array('d', false),
            array('e', true),
            array('f', true),
            array('g', false),
            array('Id', false),
            array('Guid', true),
            array('guid', false),
        );
    }

    public function testSingularize()
    {
        $this->assertTrue($this->extractor->isWritable(AdderRemoverDummy::class, 'analyses'));
        $this->assertTrue($this->extractor->isWritable(AdderRemoverDummy::class, 'feet'));
        $this->assertEquals(array('analyses', 'feet'), $this->extractor->getProperties(AdderRemoverDummy::class));
    }
}
