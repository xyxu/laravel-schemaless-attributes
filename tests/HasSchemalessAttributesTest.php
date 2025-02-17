<?php

namespace Spatie\SchemalessAttributes\Tests;

use Illuminate\Support\Collection;

class HasSchemalessAttributesTest extends TestCase
{
    /** @var \Spatie\SchemalessAttributes\Tests\TestModel */
    protected $testModel;

    /** @var \Spatie\SchemalessAttributes\Tests\TestModel */
    protected $testModelUsedTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->testModel = TestModel::create();

        $this->testModelUsedTrait = new TestModel();
    }

    /** @test */
    public function getting_a_non_existing_schemaless_attribute_returns_null()
    {
        $this->assertNull($this->testModel->schemaless_attributes->non_existing);
    }

    /** @test */
    public function default_value_can_be_passed_when_getting_a_non_existing_schemaless_attribute()
    {
        $this->assertEquals('default', $this->testModel->schemaless_attributes->get('non_existing', 'default'));
    }

    /** @test */
    public function an_schemaless_attribute_can_be_set()
    {
        $this->testModel->schemaless_attributes->name = 'value';

        $this->assertEquals('value', $this->testModel->schemaless_attributes->name);
    }

    /** @test */
    public function an_schemaless_attribute_can_be_set_from_json()
    {
        $this->testModel->schemaless_attributes = json_encode(['name' => 'value']);

        $this->assertEquals('value', $this->testModel->schemaless_attributes->name);
    }

    /** @test */
    public function an_schemaless_attribute_uses_fallback_empty_array_on_non_valid_values()
    {
        $this->testModel->schemaless_attributes = 'string';
        $this->assertEquals([], $this->testModel->schemaless_attributes->all());

        $this->testModel->schemaless_attributes = '""'; // not array json
        $this->assertEquals([], $this->testModel->schemaless_attributes->all());

        $this->testModel->schemaless_attributes = "{'name':'value'}"; // invalid json format
        $this->assertEquals([], $this->testModel->schemaless_attributes->all());

        $this->testModel->schemaless_attributes = null;
        $this->assertEquals([], $this->testModel->schemaless_attributes->all());

        $this->testModel->schemaless_attributes = false;
        $this->assertEquals([], $this->testModel->schemaless_attributes->all());

        $this->testModel->schemaless_attributes = 1;
        $this->assertEquals([], $this->testModel->schemaless_attributes->all());

        $this->testModel->schemaless_attributes = 0.1;
        $this->assertEquals([], $this->testModel->schemaless_attributes->all());
    }

    /** @test */
    public function it_can_determine_if_it_has_a_schemaless_attribute()
    {
        $this->assertFalse($this->testModel->schemaless_attributes->has('name'));

        $this->testModel->schemaless_attributes->name = 'value';

        $this->assertTrue($this->testModel->schemaless_attributes->has('name'));
    }

    /** @test */
    public function schemaless_attributes_will_get_saved_with_the_model()
    {
        $this->testModel->schemaless_attributes->name = 'value';

        $this->testModel->save();

        $this->assertEquals('value', $this->testModel->schemaless_attributes->name);
    }

    /** @test */
    public function checking_existing_schemaless_attribute_is_empty_with_direct_access()
    {
        $this->testModel->schemaless_attributes->name = 'value';
        $this->testModel->save();

        $this->assertNotEmpty($this->testModel->schemaless_attributes->name);
        $this->assertEmpty($this->testModel->schemaless_attributes->first_name);
    }

    /** @test */
    public function checking_existing_schemaless_attribute_is_empty_with_access_via_get()
    {
        $this->testModel->schemaless_attributes->name = 'value';
        $this->testModel->save();

        $this->assertNotEmpty($this->testModel->schemaless_attributes->get('name'));
        $this->assertEmpty($this->testModel->schemaless_attributes->get('first_name'));
    }

    /** @test */
    public function it_can_handle_an_array()
    {
        $array = [
            'one' => 'value',
            'two' => 'another value',
        ];

        $this->testModel->schemaless_attributes->array = $array;

        $this->assertEquals($array, $this->testModel->schemaless_attributes->array);
    }

    /** @test */
    public function it_can_get_values_using_dot_notation()
    {
        $this->testModel->schemaless_attributes->rey = ['side' => 'light'];
        $this->testModel->schemaless_attributes->snoke = ['side' => 'dark'];

        $this->assertEquals('light', $this->testModel->schemaless_attributes->get('rey.side'));
    }

    /** @test */
    public function it_can_set_values_using_dot_notation()
    {
        $this->testModel->schemaless_attributes->rey = ['side' => 'light'];
        $this->testModel->schemaless_attributes->set('rey.side', 'dark');

        $this->assertEquals('dark', $this->testModel->schemaless_attributes->get('rey.side'));
    }

    /** @test */
    public function it_can_get_values_using_wildcards_notation()
    {
        $this->testModel->schemaless_attributes->rey = ['sides' => [
            ['name' => 'light'],
            ['name' => 'neutral'],
            ['name' => 'dark'],
        ]];

        $this->assertEquals(['light', 'neutral', 'dark'], $this->testModel->schemaless_attributes->get('rey.sides.*.name'));
    }

    /** @test */
    public function it_can_set_values_using_wildcard_notation()
    {
        $this->testModel->schemaless_attributes->rey = ['sides' => [
            ['name' => 'light'],
            ['name' => 'neutral'],
            ['name' => 'dark'],
        ]];

        $this->testModel->schemaless_attributes->set('rey.sides.*.name', 'dark');

        $this->assertEquals(['dark', 'dark', 'dark'], $this->testModel->schemaless_attributes->get('rey.sides.*.name'));
    }

    /** @test */
    public function it_can_set_all_schemaless_attributes_at_once()
    {
        $array = [
            'rey' => ['side' => 'light'],
            'snoke' => ['side' => 'dark'],
        ];

        $this->testModel->schemaless_attributes = $array;

        $this->assertEquals($array, $this->testModel->schemaless_attributes->all());
    }

    /** @test */
    public function it_can_forget_a_single_schemaless_attribute()
    {
        $this->testModel->schemaless_attributes->name = 'value';

        $this->assertEquals('value', $this->testModel->schemaless_attributes->name);

        $this->testModel->schemaless_attributes->forget('name');

        $this->assertNull($this->testModel->schemaless_attributes->name);
    }

    /** @test */
    public function it_can_forget_a_schemaless_attribute_using_dot_notation()
    {
        $this->testModel->schemaless_attributes->member = ['name' => 'John', 'age' => 30];

        $this->testModel->schemaless_attributes->forget('member.age');

        $this->assertEquals($this->testModel->schemaless_attributes->member, ['name' => 'John']);
    }

    /** @test */
    public function it_can_get_all_schemaless_attributes()
    {
        $this->testModel->schemaless_attributes = ['name' => 'value'];

        $this->assertEquals(['name' => 'value'], $this->testModel->schemaless_attributes->all());
    }

    /** @test */
    public function it_will_use_the_correct_datatype()
    {
        $this->testModel->schemaless_attributes->bool = true;
        $this->testModel->schemaless_attributes->float = 12.34;

        $this->testModel->save();

        $this->testModel->refresh();

        $this->assertTrue($this->testModel->schemaless_attributes->bool);
        $this->assertSame(12.34, $this->testModel->schemaless_attributes->float);
    }

    /** @test */
    public function it_can_be_handled_as_an_array()
    {
        $this->testModel->schemaless_attributes['name'] = 'value';

        $this->assertEquals('value', $this->testModel->schemaless_attributes['name']);

        $this->assertTrue(isset($this->testModel->schemaless_attributes['name']));

        unset($this->testModel->schemaless_attributes['name']);

        $this->assertFalse(isset($this->testModel->schemaless_attributes['name']));

        $this->assertNull($this->testModel->schemaless_attributes['name']);
    }

    /** @test */
    public function it_can_be_counted()
    {
        $this->assertCount(0, $this->testModel->schemaless_attributes);

        $this->testModel->schemaless_attributes->name = 'value';

        $this->assertCount(1, $this->testModel->schemaless_attributes);
    }

    /** @test */
    public function it_can_be_used_as_an_arrayable()
    {
        $this->testModel->schemaless_attributes->name = 'value';

        $this->assertEquals(
            $this->testModel->schemaless_attributes->toArray(),
            $this->testModel->schemaless_attributes->all()
        );
    }

    /** @test */
    public function it_can_add_and_save_schemaless_attributes_in_one_go()
    {
        $array = [
            'name' => 'value',
            'name2' => 'value2',
        ];

        $testModel = TestModel::create(['schemaless_attributes' => $array]);

        $this->assertEquals($array, $testModel->schemaless_attributes->all());
    }

    /** @test */
    public function it_can_and_save_schemaless_attributes_from_json()
    {
        $array = [
            'name' => 'value',
            'name2' => 'value2',
        ];

        $testModel = TestModel::create(['schemaless_attributes' => json_encode($array)]);

        $this->assertEquals($array, $testModel->schemaless_attributes->all());
    }

    /** @test */
    public function it_can_and_save_schemaless_attributes_as_null_when_non_valid_values()
    {
        $testModel = TestModel::create(['schemaless_attributes' => 'string']);
        $this->assertEquals(null, $testModel->getAttributes()['schemaless_attributes']);

        $testModel = TestModel::create(['schemaless_attributes' => '""']); // not array json
        $this->assertEquals(null, $testModel->getAttributes()['schemaless_attributes']);

        $testModel = TestModel::create(['schemaless_attributes' => "{'name':'value'}"]); // invalid json format
        $this->assertEquals(null, $testModel->getAttributes()['schemaless_attributes']);

        $testModel = TestModel::create(['schemaless_attributes' => null]);
        $this->assertEquals(null, $testModel->getAttributes()['schemaless_attributes']);

        $testModel = TestModel::create(['schemaless_attributes' => false]);
        $this->assertEquals(null, $testModel->getAttributes()['schemaless_attributes']);

        $testModel = TestModel::create(['schemaless_attributes' => 1]);
        $this->assertEquals(null, $testModel->getAttributes()['schemaless_attributes']);

        $testModel = TestModel::create(['schemaless_attributes' => 0.1]);
        $this->assertEquals(null, $testModel->getAttributes()['schemaless_attributes']);
    }

    /** @test */
    public function it_has_a_scope_to_get_models_with_the_given_schemaless_attributes()
    {
        TestModel::truncate();

        $model1 = TestModel::create(['schemaless_attributes' => [
            'name' => 'value',
            'name2' => 'value2',
            'arr' => [
                'subKey1' => 'subVal1',
            ],
        ]]);

        $model2 = TestModel::create(['schemaless_attributes' => [
            'name' => 'value',
            'name2' => 'value2',
            'arr' => [
                'subKey1' => 'subVal1',
            ],
        ]]);

        $model3 = TestModel::create(['schemaless_attributes' => [
            'name' => 'value',
            'name2' => 'value3',
            'arr' => [
                'subKey1' => 'subVal2',
            ],
        ]]);

        $this->assertContainsModels([
            $model1, $model2,
        ], TestModel::withSchemalessAttributes(['name' => 'value', 'name2' => 'value2'])->get());

        $this->assertContainsModels([
            $model3,
        ], TestModel::withSchemalessAttributes(['name' => 'value', 'name2' => 'value3'])->get());

        $this->assertContainsModels([
        ], TestModel::withSchemalessAttributes(['name' => 'value', 'non-existing' => 'value'])->get());

        $this->assertContainsModels([
            $model1, $model2, $model3,
        ], TestModel::withSchemalessAttributes([])->get());

        $this->assertContainsModels([
            $model1, $model2, $model3,
        ], TestModel::withSchemalessAttributes('name', 'value')->get());

        $this->assertContainsModels([
            $model1, $model2,
        ], TestModel::withSchemalessAttributes('name2', '!=', 'value3')->get());

        $this->assertContainsModels([
            $model3,
        ], TestModel::withSchemalessAttributes('arr->subKey1', 'subVal2')->get());

        $this->assertContainsModels([
            $model1, $model2,
        ], TestModel::withSchemalessAttributes('arr->subKey1', '!=', 'subVal2')->get());

        $this->assertContainsModels([
        ], TestModel::withSchemalessAttributes('name', 'non-existing-value')->get());
    }

    /** @test */
    public function it_can_set_multiple_attributes_one_after_the_other()
    {
        $this->testModel->schemaless_attributes->name = 'value';
        $this->testModel->schemaless_attributes->name2 = 'value2';

        $this->assertEquals([
            'name' => 'value',
            'name2' => 'value2',
        ], $this->testModel->schemaless_attributes->all());
    }

    /** @test */
    public function it_returns_an_array_that_can_be_looped()
    {
        $this->testModel->schemaless_attributes->name = 'value';
        $this->testModel->schemaless_attributes->name2 = 'value2';

        $attributes = $this->testModel->schemaless_attributes->all();

        $this->assertCount(2, $attributes);

        foreach ($attributes as $key => $value) {
            $this->assertNotNull($key);
            $this->assertNotNull($value);
        }
    }

    /** @test */
    public function it_can_multiple_attributes_at_once_by_passing_an_array_argument()
    {
        $this->testModel->schemaless_attributes->set([
            'foo' => 'bar',
            'baz' => 'buzz',
            'arr' => [
                'subKey1' => 'subVal1',
                'subKey2' => 'subVal2',
            ],
        ]);

        $this->assertEquals('bar', $this->testModel->schemaless_attributes->foo);
        $this->assertCount(2, $this->testModel->schemaless_attributes->arr);
        $this->assertEquals('subVal1', $this->testModel->schemaless_attributes->arr['subKey1']);
    }

    /** @test */
    public function if_an_iterable_is_passed_to_set_it_will_defer_to_setMany()
    {
        $this->testModel->schemaless_attributes->set([
            'foo' => 'bar',
            'baz' => 'buzz',
            'arr' => [
                'subKey1' => 'subVal1',
                'subKey2' => 'subVal2',
            ],
        ]);

        $this->assertEquals('bar', $this->testModel->schemaless_attributes->foo);
        $this->assertCount(2, $this->testModel->schemaless_attributes->arr);
        $this->assertEquals('subVal1', $this->testModel->schemaless_attributes->arr['subKey1']);
    }

    /** @test */
    public function it_can_call_collection_method_pop()
    {
        $this->testModel->schemaless_attributes->set([
            'foo' => 'bar',
            'baz' => 'buzz',
            'arr' => [
                'subKey1' => 'subVal1',
                'subKey2' => 'subVal2',
            ],
        ]);

        $item = $this->testModel->schemaless_attributes->pop();

        $this->assertEquals([
            'subKey1' => 'subVal1',
            'subKey2' => 'subVal2',
        ], $item);
        $this->assertEquals([
            'foo' => 'bar',
            'baz' => 'buzz',
        ], $this->testModel->schemaless_attributes->toArray());
    }

    /** @test */
    public function it_can_call_collection_method_sum()
    {
        $this->testModel->schemaless_attributes->set([
            ['price' => 10],
            ['price' => 5],
        ]);

        $this->assertEquals(15, $this->testModel->schemaless_attributes->sum('price'));
        $this->assertEquals([
            ['price' => 10],
            ['price' => 5],
        ], $this->testModel->schemaless_attributes->toArray());
    }

    /** @test */
    public function it_can_call_collection_method_slice()
    {
        $this->testModel->schemaless_attributes->set([
            'foo' => 'bar',
            'baz' => 'buzz',
            'lorem' => 'ipsum',
            'dolor' => 'amet',
        ]);

        $this->assertEquals([
            'baz' => 'buzz',
            'lorem' => 'ipsum',
        ], $this->testModel->schemaless_attributes->slice(1, 2)->toArray());
        $this->assertEquals([
            'foo' => 'bar',
            'baz' => 'buzz',
            'lorem' => 'ipsum',
            'dolor' => 'amet',
        ], $this->testModel->schemaless_attributes->toArray());
    }

    /** @test */
    public function it_can_call_collection_method_only()
    {
        $this->testModel->schemaless_attributes->set([
            'foo' => 'bar',
            'baz' => 'buzz',
            'lorem' => 'ipsum',
            'dolor' => 'amet',
        ]);

        $this->assertEquals([
            'baz' => 'buzz',
            'dolor' => 'amet',
        ], $this->testModel->schemaless_attributes->only('baz', 'dolor')->toArray());
        $this->assertEquals([
            'foo' => 'bar',
            'baz' => 'buzz',
            'lorem' => 'ipsum',
            'dolor' => 'amet',
        ], $this->testModel->schemaless_attributes->toArray());
    }

    /** @test */
    public function an_schemaless_attribute_can_be_set_if_the_has_schemaless_atrributes_trait_is_used(): void
    {
        $this->testModelUsedTrait->schemaless_attributes->name = 'value';

        $this->assertEquals('value', $this->testModelUsedTrait->schemaless_attributes->name);
    }

    protected function assertContainsModels(array $expectedModels, Collection $actualModels)
    {
        $assertionFailedMessage = 'Expected '.count($expectedModels).' models. Got '.$actualModels->count().' models';

        $this->assertEquals(count($expectedModels), $actualModels->count(), $assertionFailedMessage);
    }
}
