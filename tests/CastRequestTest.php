<?php

namespace Localdisk\Tests;

use Mockery;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Validation\Factory;
use Illuminate\Container\Container;
use Localdisk\Request\CastAttribute;
use PHPUnit\Framework\Constraint\IsType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Translation\Translator;

class CastRequestTest extends TestCase
{
    public function testCast(): void
    {
        $params = [
            'intAttribute' => '1',
            'floatAttribute' => '4.0',
            'stringAttribute' => 2,
            'boolAttribute' => '1',
            'arrayAttribute' => [
                1,
                2,
            ],
            'scalarAttribute' => 'hoge',
            'jsonAttribute' => '{"a":1,"b":2,"c":3,"d":4,"e":5}',
            'collectionAttribute' => [
                1,
                2,
            ],
            'dateAttribute' => '1969-07-20',
            'datetimeAttribute' => '1969-07-20 22:56:00',
            'timestampAttribute' => '1969-07-20 22:56:00',
        ];
        $query = http_build_query($params);
        $request = $this->createRequest('?'.$query);
        $request->validateResolved();

        $this->assertInternalType(IsType::TYPE_INT, $request->input('intAttribute'));
        $this->assertInternalType(IsType::TYPE_FLOAT, $request->input('floatAttribute'));
        $this->assertInternalType(IsType::TYPE_STRING, $request->input('stringAttribute'));
        $this->assertInternalType(IsType::TYPE_BOOL, $request->input('boolAttribute'));
        $this->assertInternalType(IsType::TYPE_ARRAY, $request->input('arrayAttribute'));
        $this->assertEquals([1, 2], $request->input('arrayAttribute'));
        $this->assertInternalType(IsType::TYPE_ARRAY, $request->input('scalarAttribute'));
        $this->assertEquals(['hoge'], $request->input('scalarAttribute'));
        $this->assertInternalType(IsType::TYPE_ARRAY, $request->input('jsonAttribute'));
        $this->assertInstanceOf(Collection::class, $request->input('collectionAttribute'));
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $request->input('dateAttribute'));
        $this->assertEquals('1969-07-20', $request->input('dateAttribute')->toDateString());
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $request->input('datetimeAttribute'));
        $this->assertEquals('1969-07-20 22:56:00', $request->input('datetimeAttribute')->toDateTimeString());
        $this->assertEquals(-14173440, $request->input('timestampAttribute'));
    }

    /**
     * Create a new request of the given type.
     *
     * @param string $url
     *
     * @return \Illuminate\Foundation\Http\FormRequest
     */
    protected function createRequest(string $url): FormRequest
    {
        $container = tap(new Container, function (Container $container) {
            $container->instance(
                \Illuminate\Contracts\Validation\Factory::class,
                $this->createValidationFactory($container)
            );
        });

        $request = CastFormRequest::create($url);

        return $request->setContainer($container);
    }

    /**
     * Create a new validation factory.
     *
     * @param  \Illuminate\Container\Container $container
     *
     * @return \Illuminate\Validation\Factory
     */
    protected function createValidationFactory($container): Factory
    {
        $translator = Mockery::mock(Translator::class)->shouldReceive('trans')
            ->zeroOrMoreTimes()->andReturn('error')->getMock();

        return new Factory($translator, $container);
    }
}

class CastFormRequest extends FormRequest
{
    use CastAttribute;

    protected $casts = [
        'intAttribute' => 'int',
        'floatAttribute' => 'float',
        'stringAttribute' => 'string',
        'boolAttribute' => 'bool',
        'booleanAttribute' => 'boolean',
        'arrayAttribute' => 'array',
        'scalarAttribute' => 'array',
        'jsonAttribute' => 'json',
        'collectionAttribute' => 'collection',
        'dateAttribute' => 'date',
        'datetimeAttribute' => 'datetime',
        'timestampAttribute' => 'timestamp',
    ];

    protected $dateFormat = 'Y-m-d H:i:s';

    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return true;
    }
}
