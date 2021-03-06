<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Tests for core Jelly methods.
 *
 * @package Jelly
 * @group   jelly
 * @group   jelly.core
 */
class Jelly_CoreTest extends Unittest_TestCase {

	/**
	 * Provides for test_register
	 */
	public function provider_register()
	{
		return array(
			array('test_alias', TRUE),
			array(new Model_Test_Alias, TRUE),
			
			// Model_Invalid exists but does not extend Jelly_Model
			array('test_invalid', FALSE),
			
			// Model_Unknown does not exist
			array('test_unknown', FALSE),
			
			// Shouldn't throw any exceptions
			array(NULL, FALSE),
		);
	}

	/**
	 * Tests Jelly::register()
	 * 
	 * @dataProvider provider_register
	 */
	public function test_register($model, $expected)
	{
		$this->assertSame(Jelly::register($model), $expected);
	}
	
	/**
	 * Tests Jelly::meta() and that meta objects are correctly returned.
	 * 
	 * @dataProvider provider_register
	 */
	public function test_meta($model, $expected)
	{
		$result = Jelly::meta($model);
		
		// Should return a Jelly_Meta instance
		if ($expected === TRUE)
		{
			$this->assertTrue($result instanceof Jelly_Meta);
			$this->assertTrue($result->initialized());
		}
		else
		{
			$this->assertFalse($result);
		}
	}
	
	/**
	 * Provider for test_model_name
	 */
	public function provider_model_name()
	{
		return array(
			array('model_test_alias', 'test_alias'),
			array(new Model_Test_Alias, 'test_alias'),
			array('test_alias', 'test_alias'), // Should not chomp if there is no prefix
		);
	}
	
	/**
	 * Tests Jelly::model_name().
	 * 
	 * @dataProvider provider_model_name
	 */
	public function test_model_name($model, $expected)
	{
		$this->assertSame($expected, Jelly::model_name($model));
	}
	
	/**
	 * Provider for test_class_name
	 */
	public function provider_class_name()
	{
		return array(
			array('test_alias', 'model_test_alias'),
			array(new Model_Test_Alias, 'model_test_alias'),
			array('model_test_alias', 'model_model_test_alias'), // Should add prefix even if it already exists
		);
	}
	
	/**
	 * Tests Jelly::class_name()
	 * 
	 * @dataProvider provider_class_name
	 */
	public function test_class_name($model, $expected)
	{
		$this->assertSame($expected, Jelly::class_name($model));
	}
}