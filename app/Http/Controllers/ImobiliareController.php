<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Services\Imobiliare\Apis\ImobiliareApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SoapClient;
use Exception;

class ImobiliareController extends Controller
{
    protected ImobiliareApiService $imobiliareApiService;

    public function __construct(ImobiliareApiService $imobiliareApiService)
    {
        $this->imobiliareApiService = $imobiliareApiService;
    }

    public function createPayLoad(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'property_id' => 'required',
            ]);

            $property = Property::find($validated['property_id']);

            if (!$property) {
                throw new Exception("Property with ID {$validated['property_id']} not found.");
            }

            $latitude = $validated['latitude'];
            $longitude = $validated['longitude'];

            $payload = [
                'id2' => (int) $property->unique_code,
                'titlu' =>  $property->name,
                'descriere' => $property->description,
                'tara' => 1048,
                'judet' => 2,
                'localitate' => 200,
                'zona' => 7777777,
                'devanzare' => $property->tranzaction === 'Sale' ? 1 : 0,
                'deinchiriat' => $property->tranzaction === 'Rent' ? 1 : 0,
                'siteagentie' => 1,
                'portal' => 1,
                'anuntbonus' => 0,
                'tipspatiu' => 421,
                'suprafatautila' => (int) $property->usable_area,
                'latitudine' => $latitude,
                'longitudine' => $longitude,
                'disp_prop' => 'aW1lZGlhdA==',
                'suprafatateren' => (int) $property->land_area,
                'inaltimespatiu' => 7,
                'tipimobil' => 427,
//                'imagini' => [],

                // VANZARE Quality Fields
                'pretvanzare' => $property->price,
                'monedavanzare' => 172,

                // INCHIRIERE Quality Fields
                'pretinchiriere' => $property->price,
                'monedainchiriere' => 172,

                // Optional Fields
                'adresa' => $property->address,
                'agent' => $property->user->imobiliare_id,
                'anconstructie' => $property->construction_year,
                'nrcamere' => $property->room_numbers,
                'dotari' => null,
                'utilitati' => null,
                'servicii' => null,
                'etaj' => $property->floor,
                'suprafatacurte' => $property->land_area,
                'imoradar24' => 1,
            ];

            Log::channel('imobiliare_apis')->debug('Payload prepared for Imobiliare.ro API.', [
                'property_id' => $property->id,
                'payload' => $payload,
            ]);

            $response = $this->imobiliareApiService->createOrUpdateArticle($payload);

            Log::channel('imobiliare_apis')->debug('Response from Imobiliare.ro API.', [
                'property_id' => $property->id,
                'response' => $response,
            ]);

            if ($response['status'] !== 200) {
                return response()->json([
                    'error' => $response['body']['error'] ?? 'Unknown error from Imobliare.ro',
                ], 500);
            }

            return response()->json([
                'message' => 'Property posted on Imobiliare!',
            ]);

        } catch (Exception $e) {
            Log::channel('imobiliare_apis')->error('Error sending payload to Imobiliare.ro API.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'There was an error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
