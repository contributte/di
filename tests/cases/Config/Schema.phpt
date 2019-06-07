<?php declare(strict_types = 1);

/**
 * Test: Config\Schema
 */

use Contributte\DI\Config\Node;
use Contributte\DI\Config\Schema;
use Nette\InvalidStateException;
use Nette\Utils\AssertionException;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Unknown options
test(static function (): void {
	Assert::exception(static function (): void {
		Schema::root()
			->validate(['email' => 'foo@bar.baz', 'foo' => 1]);
	}, InvalidStateException::class, 'Unknown configuration option email, foo');
});

// String validator
test(static function (): void {
	Assert::exception(static function (): void {
		Schema::root()
			->add(Node::create('email')->isString())
			->validate(['email' => 25]);
	}, AssertionException::class, 'The variable "email" expects to be string, int 25 given.');
});

// Integer validator
test(static function (): void {
	Assert::exception(static function (): void {
		Schema::root()
			->add(Node::create('count')->isInt())
			->validate(['count' => '25']);
	}, AssertionException::class, "The variable \"count\" expects to be int, string '25' given.");
});

// Array validator
test(static function (): void {
	Assert::exception(static function (): void {
		Schema::root()
			->add(Node::create('data')->isArray())
			->validate(['data' => 25]);
	}, AssertionException::class, 'The variable "data" expects to be array, int 25 given.');
});

// Float validator
test(static function (): void {
	Assert::exception(static function (): void {
		Schema::root()
			->add(Node::create('count')->isFloat())
			->validate(['count' => 1]);
	}, AssertionException::class, 'The variable "count" expects to be float, int 1 given.');
});

// Children validator
test(static function (): void {
	Assert::exception(static function (): void {
		Schema::root()
			->add(Node::create('students')->children([
				Node::create('name')->isString(),
			]))
			->validate(['students' => 1]);
	}, AssertionException::class, 'The variable "students" expects to be array, int 1 given.');

	Assert::exception(static function (): void {
		Schema::root()
			->add(Node::create('students')->children([
				Node::create('name')->isString(),
			]))
			->validate(['students' => [['name' => 1]]]);
	}, AssertionException::class, 'The variable "name" expects to be string, int 1 given.');
});

// Nested validator
test(static function (): void {
	Assert::exception(static function (): void {
		Schema::root()
			->add(Node::create('address')->nested([
				Node::create('street')->isString(),
			]))
			->validate(['address' => 1]);
	}, AssertionException::class, 'The variable "address" expects to be array, int 1 given.');

	Assert::exception(static function (): void {
		Schema::root()
			->add(Node::create('address')->nested([
				Node::create('street')->isString(),
			]))
			->validate(['address' => ['street' => 1]]);
	}, AssertionException::class, 'The variable "street" expects to be string, int 1 given.');
});

// Success data processing
test(static function (): void {
	$data = Schema::root()
		->add(Node::create('url1')->isString())
		->add(Node::create('url2')->isString()->setDefault('www.foo.baz2'))
		->add(Node::create('url3')->isString()->nullable()->setDefault('www.foo.baz3'))
		->process([
			'url1' => 'www.foo.bar1',
			'url3' => null,
		]);

	Assert::equal('www.foo.bar1', $data['url1']);
	Assert::equal('www.foo.baz2', $data['url2']);
	Assert::null($data['url3']);
});

// Success data children processing
test(static function (): void {
	$data = Schema::root()
		->add(Node::create('students')->children([
			Node::create('name')->isString(),
			Node::create('surname')->isString()->setDefault('Doe'),
		]))
		->process(['students' => [['name' => 'John']]]);

	Assert::equal('John', $data['students'][0]['name']);
	Assert::equal('Doe', $data['students'][0]['surname']);

	$data = Schema::root()
		->add(Node::create('students1')->children([
			Node::create('students2')->children([
				Node::create('name')->isString(),
				Node::create('surname')->isString()->setDefault('Doe'),
			]),
		]))
		->process(['students1' => [['students2' => [['name' => 'John']]]]]);

	Assert::equal('John', $data['students1'][0]['students2'][0]['name']);
	Assert::equal('Doe', $data['students1'][0]['students2'][0]['surname']);
});

// Success data nested processing
test(static function (): void {
	$data = Schema::root()
		->add(Node::create('address')->nested([
			Node::create('street')->isString(),
			Node::create('zip')->isInt()->setDefault(12345),
		]))
		->process(['address' => ['street' => 'Prague']]);

	Assert::equal('Prague', $data['address']['street']);
	Assert::equal(12345, $data['address']['zip']);

	$data = Schema::root()
		->add(Node::create('address1')->nested([
			Node::create('address2')->nested([
				Node::create('street')->isString(),
				Node::create('zip')->isInt()->setDefault(12345),
			]),
		]))
		->process(['address1' => ['address2' => ['street' => 'Prague']]]);

	Assert::equal('Prague', $data['address1']['address2']['street']);
	Assert::equal(12345, $data['address1']['address2']['zip']);
});

// Success data nested+children processing
test(static function (): void {
	$data = Schema::root()
		->add(Node::create('students')->children([
			Node::create('address')->nested([
				Node::create('street')->isString(),
				Node::create('zip')->isInt()->setDefault(12345),
			]),
		]))
		->process(['students' => [['address' => ['street' => 'Prague']]]]);

	Assert::equal('Prague', $data['students'][0]['address']['street']);
	Assert::equal(12345, $data['students'][0]['address']['zip']);
});
