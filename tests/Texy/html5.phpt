<?php

/**
 * Test: HTML5
 */

use Tester\Assert;
use Texy\Texy;

require __DIR__ . '/../bootstrap.php';


test(function () {
	$texy = new Texy;
	Assert::same("<div data-test=\"hello\"></div>\n", $texy->process('<div data-test=hello>'));
});

test(function () {
	$texy = new Texy;
	Assert::same("<p data-attr=\"val\">hello</p>\n", $texy->process('hello .{data-attr: val}'));
});

test(function () {
	$texy = new Texy;
	Assert::same("<div data-test=\"hello\"></div>\n", $texy->process('<div data-test=hello>'));
});

test(function () {
	$texy = new Texy;
	$texy->setOutputMode($texy::HTML5);
	Assert::matchFile(
		__DIR__ . '/expected/html5-tags.html',
		$texy->process(file_get_contents(__DIR__ . '/sources/html5-tags.texy'))
	);
});