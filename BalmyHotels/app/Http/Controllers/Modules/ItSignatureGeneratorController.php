<?php

namespace App\Http\Controllers\Modules;

class ItSignatureGeneratorController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'it_signature_generator',
            ['index'],
            [],
            [],
            [],
            []
        );
    }

    public function index()
    {
        return view('modules.bilgi_islem.signature_generator.index');
    }
}
