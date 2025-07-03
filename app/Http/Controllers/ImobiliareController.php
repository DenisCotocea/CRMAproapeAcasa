<?php

namespace App\Http\Controllers;

use App\Services\Imobiliare\Apis\ImobiliareApiService;
use Illuminate\Http\Request;
use SoapClient;
use Exception;

class ImobiliareController extends Controller
{
    protected ImobiliareApiService $imobiliareApiService;

    public function __construct(ImobiliareApiService $imobiliareApiService)
    {
        $this->imobiliareApiService = $imobiliareApiService;
    }

    public function showMap()
    {
        return $this->imobiliareApiService->showMap();
    }
}
