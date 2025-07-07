<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Services\Romimo\RomimoApiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class RomimoController extends Controller
{
    protected RomimoApiService $romimoApiService;

    public function __construct(RomimoApiService $romimoApiService)
    {
        $this->romimoApiService = $romimoApiService;
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

            $propertyMappings = [
                'room_numbers' => ['key' => 'nr_camere', 'name' => 'Număr camere'],
                'floor' => ['key' => 'etaj', 'name' => 'Etaj'],
                'surface' => ['key' => 'suprafata', 'name' => 'Suprafață'],
                'construction_year' => ['key' => 'an_constructie', 'name' => 'An construcție'],
                'balcony' => ['key' => 'balcon', 'name' => 'Balcon', 'boolean' => true],
                'garage' => ['key' => 'garaj', 'name' => 'Garaj', 'boolean' => true],
                'elevator' => ['key' => 'lift', 'name' => 'Lift', 'boolean' => true],
                'parking' => ['key' => 'parcare', 'name' => 'Parcare', 'boolean' => true],
                'usable_area' => ['key' => 'suprafata_utila', 'name' => 'Suprafață utilă'],
            ];

            $propertyData = [];

            foreach ($propertyMappings as $field => $map) {
                $value = $property->$field;

                if (!is_null($value)) {
                    if (!empty($map['boolean'])) {
                        $value = $value ? 'Da' : 'Nu';
                    }

                    $propertyData[] = [
                        'key' => $map['key'],
                        'value' => $value,
                    ];
                }
            }

            $payload = [
                "user" => [
                    "email" => $property->user->email,
                ],
                "ad" => [
                    "active" => true,
                    "promoted" => false,
                    "externalid" => $property->unique_code,
                    "category" => 312,
                    "price" => (int) $property->price,
                    "currency" => "EUR",
                    "title" => $property->name,
                    "text" => $property->description,
                    "validFrom" => now()->toIso8601String(),
                    "validTo" => now()->addYear()->toIso8601String(),
                ],
                "contact" => [
                    "contactName" => $property->user->name,
                    "contactEmail" => $property->user->email,
                    "contactPhone" => $property->user->phone,
                ],
                "location" => [
                    "countyName" => $property->county,
                    "cityName" => $property->city,
                    "latitude" => $latitude,
                    "longitude" => $longitude,
                ],
                "properties" => $propertyData,
                "pictures" => collect($property->images)->map(function ($image, $index) {
                    return [
                        "url" => asset('storage/' . $image->path),
                        "rank" => $index + 1
                    ];
                })->toArray(),
            ];

            Log::channel('romimo_apis')->debug('Payload prepared for Romimo API.', [
                'property_id' => $property->id,
                'payload' => $payload,
            ]);

            $response = $this->romimoApiService->createOrUpdateArticle($payload);

            Log::channel('romimo_apis')->debug('Response from Romimo API.', [
                'property_id' => $property->id,
                'response' => $response,
            ]);

            if ($response['status'] !== 200) {
                return response()->json([
                    'error' => $response['body']['error'] ?? 'Unknown error from Romimo.',
                ], 500);
            }

            return response()->json([
                'message' => 'Property posted on Romimo!',
            ]);

        } catch (Exception $e) {
            Log::channel('romimo_apis')->error('Error sending payload to Romimo API.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'There was an error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
