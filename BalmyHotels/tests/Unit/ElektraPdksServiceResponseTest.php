<?php

namespace Tests\Unit;

use App\Services\Pdks\ElektraPdksService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ElektraPdksServiceResponseTest extends TestCase
{
    public function test_it_flattens_the_nested_result_set_returned_by_elektra(): void
    {
        Http::fake([
            '*' => Http::response([
                'ResultSets' => [[
                    [
                        ['SICILID' => 1, 'ADISOYADI' => 'Birinci Personel'],
                        ['SICILID' => 2, 'ADISOYADI' => 'İkinci Personel'],
                    ],
                ]],
            ]),
        ]);

        $rows = app(ElektraPdksService::class)->fetchRows(
            ['endpoint' => 'https://example.test', 'api_key' => 'test-key'],
            ['TENANTID' => 32904, 'FIRMAID' => '3617', 'CALISIYOR' => 1]
        );

        $this->assertCount(2, $rows);
        $this->assertSame(1, $rows[0]['SICILID']);
        $this->assertSame(2, $rows[1]['SICILID']);
    }
}
