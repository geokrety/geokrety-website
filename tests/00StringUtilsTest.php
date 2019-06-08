<?php

use PHPUnit\Framework\TestCase;

class StringUtilsTest extends TestCase {
    public function test_no_wrap_for_short_string() {
        $input = 'abc';

        $output = StringUtils::mb_wordwrap($input);

        $this->assertSame($input, $output);
    }

    public function test_no_wrap_for_short_utf_string() {
        $input = 'тестовая строка';

        $output = StringUtils::mb_wordwrap($input);

        $this->assertSame($input, $output);
    }

    public function test_wrap_for_english_string() {
        $input = 'test string with some words in English without any Unicode and whatever short a b c d e f g h i k l m n o p q r s t u v w x y z symbols';

        $output = StringUtils::mb_wordwrap($input, 30);

        $this->assertSame(wordwrap($input, 30), $output);
    }

    public function test_wrap_for_utf_string() {
        $input = 'тестовая строка на русском языке с разными символами';

        $output = StringUtils::mb_wordwrap($input, 30);

        $this->assertSame("тестовая строка на русском\nязыке с разными символами", $output);
    }

    public function test_custom_wrap_for_english_string() {
        $input = 'f f f f fd f f f f fd';

        $output = StringUtils::mb_wordwrap($input, 10);

        $this->assertSame("f f f f fd\nf f f f fd", $output);
    }

    public function test_custom_wrap_for_russian_string() {
        $input = 'ф ф ф ф фв ф ф ф ф фв';

        $output = StringUtils::mb_wordwrap($input, 10);

        $this->assertSame("ф ф ф ф фв\nф ф ф ф фв", $output);
    }
}
