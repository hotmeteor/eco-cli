<?php

namespace Tests\Unit\Support;

use App\Support\Helpers;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    public function test_format_key()
    {
        $this->assertSame('KEY', Helpers::formatKey('key'));
        $this->assertSame('KEY_NAME', Helpers::formatKey('keyName'));
        $this->assertSame('KEY_NAME', Helpers::formatKey('key_name'));
        $this->assertSame('KEY', Helpers::formatKey('  key  '));
        $this->assertSame('KEY_NAME', Helpers::formatKey('  keyName  '));
    }

    public function test_format_value()
    {
        $this->assertSame('value', Helpers::formatValue('value'));
        $this->assertSame('value', Helpers::formatValue('  value  '));
        $this->assertSame("'the value'", Helpers::formatValue('the value'));
        $this->assertSame("'the value'", Helpers::formatValue('  the value  '));
    }
}
