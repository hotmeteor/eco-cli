<?php

namespace Tests\Unit\Support;

use App\Support\Vault;
use Tests\TestCase;

class VaultTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->contents = [
            'driver' => 'fake',
            'drivers' => [
                'fake' => [
                    'token' => 'ABCD-1234',
                    'org' => 'hotmeteor',
                    'repo' => 'eco-cli',
                ],
            ],
            'hotmeteor' => [
                'eco-cli' => [
                    'KEY' => 'value',
                    'THIS' => 'that',
                    'COLOUR' => "'light blue'",
                ],
            ],
        ];

        file_put_contents(self::vaultFile(), json_encode($this->contents));
    }

    public function test_vault_load()
    {
        $this->assertSame($this->contents, Vault::load());
    }

    public function test_vault_get()
    {
        $this->assertSame('fake', Vault::get('driver'));
        $this->assertSame('value', Vault::get('hotmeteor.eco-cli.KEY'));
    }

    public function test_vault_set()
    {
        Vault::set('driver', 'other');
        Vault::set('hotmeteor.eco-cli.KEY', 'different');
        Vault::set('new', [
            'array' => 'of-values',
        ]);

        $this->assertSame('other', Vault::get('driver'));
        $this->assertSame('different', Vault::get('hotmeteor.eco-cli.KEY'));
        $this->assertSame(['array' => 'of-values'], Vault::get('new'));
    }

    public function test_vault_unset()
    {
        Vault::unset('driver');
        Vault::unset('hotmeteor.eco-cli.KEY');

        $this->assertArrayNotHasKey('driver', Vault::load());
        $this->assertArrayHasKey('hotmeteor', Vault::load());
        $this->assertArrayNotHasKey('KEY', Vault::load()['hotmeteor']['eco-cli']);
    }

    public function test_vault_config()
    {
        Vault::set('drivers.other', [
            'token' => 'EDFG-5678',
            'org' => 'hotmeteor',
            'repo' => 'codetown',
        ]);

        Vault::set('driver', 'fake');

        $this->assertSame('ABCD-1234', Vault::config('token'));

        Vault::set('driver', 'other');

        $this->assertSame('EDFG-5678', Vault::config('token'));
    }
}
