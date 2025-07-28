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
                'room_numbers' => ['key' => 'roomno'],
                'floor' => ['key' => 'storey'],
                'construction_year' => ['key' => 'yearofbuilding'],
                'garage' => ['key' => 'garageno', 'boolean' => true],
                'parking' => ['key' => 'parking', 'boolean' => true],
                'usable_area' => ['key' => 'livingspace'],
                'comfort' => ['key' => 'comfort'],
                'land_area' => ['key' => 'propertyspace'],
                'heating' => ['key' => 'heating'],
                'furniture' => ['key' => 'furniture'],
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

            $category = null;

            $type = strtolower($property->type);
            $tranzaction = strtolower($property->tranzaction);

            if ($type === 'apartament') {
                $category = $this->getApartmentCategoryId($property);
            } elseif ($type === 'garsoniera') {
                $category = $this->getGrasonieraCategoryId($property);
            } elseif (in_array($type, ['casa', 'house', 'vilă', 'vila'])) {
                $category = $this->getHouseCategoryId($property);
            } elseif (in_array($type, ['teren', 'land'])) {
                $category = $tranzaction === 'sale' ? 354 : 329;
            } else {
                throw new \Exception("Unknown type/tranzaction combination: {$property->type} / {$property->tranzaction}");
            }

            $payload = [
                "user" => [
                    "email" => $property->user->email,
                ],
                "ad" => [
                    "active" => true,
                    "promoted" => (bool)$property->promoted,
                    "externalid" => $property->unique_code,
                    "category" => $category,
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

            if($response['status'] === 200) {
                $property->imported_romimo = 1;
                $property->romimo_url = $response['body']['romimoUrl'] ?? null;
                $property->publi24_url = $response['body']['publi24Url'] ?? null;
                $property->save();
            }

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

    public function deactivateFromRomimo(Property $property)
    {
        $response = $this->romimoApiService->deactivateRomimo($property);

        if ($response['status'] === 204) {
            $property->update([
                'imported_romimo' => 0,
                'romimo_url' => null,
                'publi24_url' => null,
            ]);

            return redirect()->back()->with('success', 'Property deactivated from Romimo.');
        }

        return redirect()->back()->with('error', 'Failed to deactivate the property: ' . ($response['body']['detail'] ?? 'Unknown error.'));
    }

    public function getCategories(){
        $this->romimoApiService->getCategories();
    }

    public function getProperties(){
        $this->romimoApiService->getProperties();
    }

    public function getApartmentCategoryId(Property $property): int
    {
        $dealType = strtolower($property->tranzaction) === 'sale' ? 'de vanzare' : 'de inchiriat';
        $rooms = $property->room_numbers;

        $categoryMap = [
            'de inchiriat' => [
                1 => 312,
                2 => 313,
                3 => 314,
                4 => 315,
                5 => 316,
                6 => 317,
            ],
            'de vanzare' => [
                1 => 337,
                2 => 338,
                3 => 339,
                4 => 340,
                5 => 341,
                6 => 342,
            ],
        ];

        if (!isset($categoryMap[$dealType][$rooms])) {
            throw new \Exception("No apartment category for {$rooms} rooms and tranzaction '{$dealType}'");
        }

        return $categoryMap[$dealType][$rooms];
    }

    public function getGrasonieraCategoryId(Property $property)
    {
        $dealType = strtolower($property->tranzaction) === 'sale' ? 'de vanzare' : 'de inchiriat';

        $categoryMap = [
            'de inchiriat' => [
                1 => 318,
            ],
            'de vanzare' => [
                1 => 343,
            ],
        ];

        if (!isset($categoryMap[$dealType])) {
            throw new \Exception("No garsoniera category for tranzaction '{$dealType}'");
        }

        return $categoryMap[$dealType][1];
    }

    public function getHouseCategoryId(Property $property)
    {
        $dealType = strtolower($property->tranzaction) === 'sale' ? 'de vanzare' : 'de inchiriat';

        $categoryMap = [
            'de inchiriat' => [
                1 => 44,
            ],
            'de vanzare' => [
                1 => 347,
            ],
        ];

        if (!isset($categoryMap[$dealType])) {
            throw new \Exception("No garsoniera category for tranzaction '{$dealType}'");
        }

        return $categoryMap[$dealType][1];
    }
}
