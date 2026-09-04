<?php

namespace Tests\Unit;

use App\Models\PdksEmployee;
use PHPUnit\Framework\TestCase;

class PdksEmployeePersonnelOriginTest extends TestCase
{
    public function test_it_recognises_foreign_personnel_from_elektra_payroll_type(): void
    {
        $employee = new PdksEmployee([
            'employment_type' => 'YABANCI',
            'raw_payload' => ['UYRUK' => 'TURKMENISTAN'],
        ]);

        $this->assertSame('foreign', $employee->personnelOrigin());
        $this->assertSame('TURKMENISTAN', $employee->nationality());
    }

    public function test_it_recognises_domestic_personnel_from_nationality(): void
    {
        $employee = new PdksEmployee([
            'raw_payload' => ['uyruk' => 'TÜRKİYE'],
        ]);

        $this->assertSame('domestic', $employee->personnelOrigin());
    }

    public function test_it_does_not_guess_when_nationality_is_missing(): void
    {
        $employee = new PdksEmployee([
            'raw_payload' => [],
        ]);

        $this->assertSame('unknown', $employee->personnelOrigin());
        $this->assertNull($employee->nationality());
    }
}
