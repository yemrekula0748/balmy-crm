<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PhishingSimulationSafetyTest extends TestCase
{
    public function test_simulated_login_fields_cannot_be_serialized_or_autofilled(): void
    {
        $view = file_get_contents(__DIR__ . '/../../resources/views/public/phishing/login.blade.php');
        $lower = strtolower($view);

        $this->assertStringNotContainsString('name="email"', $lower);
        $this->assertStringNotContainsString('name="username"', $lower);
        $this->assertStringNotContainsString('name="password"', $lower);
        $this->assertStringContainsString('autocomplete="new-password"', $lower);
        $this->assertStringNotContainsString('login.microsoftonline.com', $lower);
        $this->assertStringNotContainsString('aadcdn.', $lower);
    }

    public function test_public_controller_rejects_every_posted_field_except_csrf(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../app/Http/Controllers/PhishingSimulationPublicController.php');

        $this->assertStringContainsString(
            "array_diff(array_keys(\$request->all()), ['_token'])",
            $controller
        );
        $this->assertStringNotContainsString("\$request->password", $controller);
        $this->assertStringNotContainsString("\$request->email", $controller);
    }
}
