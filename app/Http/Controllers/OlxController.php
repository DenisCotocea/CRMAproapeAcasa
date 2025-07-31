<?php

namespace App\Http\Controllers;

use App\Models\Property;
use http\Client\Curl\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\Olx\OlxService;
use Illuminate\Support\Facades\Log;

class OlxController extends Controller
{
    protected $olx;

    public function __construct(OlxService $olx)
    {
        $this->olx = $olx;
    }

    public function postAd(Request $request): JsonResponse
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
                $category = 'urn:concept:land-for-sale';
            } else {
                throw new \Exception("Unknown type/tranzaction combination: {$property->type} / {$property->tranzaction}");
            }

            $adData = [
                'title' =>  $property->name,
                'description' => $property->description,
                'category_urn' => $category,
                'contact' => [
                    'name' => $property->user->name,
                    'phone' => $property->user->phone,
                    'email' => $property->user->email,
                    'photo' => config('app.url') . '/' . $property->user->image->path,
                ],
                'price' => [
                    'value' => (int) $property->price,
                    'currency' => 'EUR',
                ],
                'location' => [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'exact' => true,
                ],
                'images' => [
                    $property->images->map(fn ($image) => [
                        'url' => asset($image->path),
                    ])->toArray(),
                ],
                'attributes' => [
                    [
                        'urn' => 'urn:concept:net-area-m2',
                        'value' => $property->usable_area,
                    ],
                    [
                        'urn' => 'urn:concept:construction-year',
                        'value' => $property->construction_year,
                    ],
                    [
                        'urn' => 'urn:concept:building-floors',
                        'value' => $property->total_floors,
                    ],
                    [
                        'urn' => 'urn:concept:area-m2',
                        'value' => $property->surface,
                    ],
                    [
                        'urn' => 'urn:concept:price-per-sq-meter',
                        'value' => $property->calculatePricePerSquareMeter(),
                    ],
                    [
                        'urn' => 'urn:concept:floor',
                        'value' => $this->mapFloorToUrn($property->floor),
                    ],
                    [
                        'urn' => 'urn:concept:number-of-rooms',
                        'value' => 'urn:concept:' . $property->room_numbers,
                    ],
                ],
                'site_urn' => 'urn:site:storiaro',
                'custom_fields' => [
                    'id' => $property->unique_code,
                    'reference_id' => $property->unique_code,
                ],
                'auto_extend' => true,
            ];

            Log::channel('olx_apis')->debug('Payload prepared for Storia.ro API.', [
                'property_id' => $property->id,
                'payload' => $adData,
            ]);

            $response = $this->olx->postAd($adData);

            Log::channel('olx_apis')->debug('Response from Storia.ro API.', [
                'property_id' => $property->id,
                'response' => $response,
            ]);

            if ($response->status() !== 200) {
                return response()->json([
                    'error' => $response['body']['error'] ?? 'Unknown error from Storia.ro',
                ], 500);
            }

            return response()->json([
                'message' => 'Property posted on Storia!',
            ]);

        } catch (Exception $e) {
            Log::channel('olx_apis')->error('Error sending payload to Storia.ro API.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'There was an error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getAuthUrl()
    {
        return response()->json([
            'url' => $this->olx->getAuthUrl()
        ]);
    }

    public function exchangeCode(Request $request)
    {
        $code = $request->input('code');
        return response()->json($this->olx->getToken($code));
    }

    public function updateAd($id, Request $request)
    {
        $payload = $this->olx->generateAdPayload($request->all());
        return response()->json($this->olx->updateAd($id, $payload));
    }

    public function deleteAd($id)
    {
        return response()->json($this->olx->deleteAd($id));
    }

    public function activateAd($id)
    {
        return response()->json($this->olx->activateAd($id));
    }

    public function deactivateAd($id)
    {
        return response()->json($this->olx->deactivateAd($id));
    }

    public function applyPromotion($id, Request $request)
    {
        return response()->json($this->olx->applyPromotion($id, $request->all()));
    }

    public function handleCallback(Request $request)
    {
        $code = $request->query('code');

        if (!$code) {
            return response()->json(['error' => 'Missing code'], 400);
        }

        $tokenData = $this->olx->getToken($code);

        return response()->json([
            'message' => 'Successfully authenticated',
            'token' => $tokenData,
        ]);
    }

    public function getApartmentCategoryId(Property $property): string
    {
        $dealType = strtolower($property->tranzaction) === 'sale' ? 'de vanzare' : 'de inchiriat';

        $categoryMap = [
            'de inchiriat' => 'urn:concept:apartments-for-rent',
            'de vanzare' => 'urn:concept:apartments-for-sale',
        ];

        return $categoryMap[$dealType];
    }

    public function getGrasonieraCategoryId(Property $property)
    {
        $dealType = strtolower($property->tranzaction) === 'sale' ? 'de vanzare' : 'de inchiriat';

        $categoryMap = [
            'de inchiriat' => 'urn:concept:garsoniera-for-rent',
            'de vanzare' => 'urn:concept:garsoniera-for-sale',
        ];

        return $categoryMap[$dealType];
    }

    public function getHouseCategoryId(Property $property)
    {
        $dealType = strtolower($property->tranzaction) === 'sale' ? 'de vanzare' : 'de inchiriat';

        $categoryMap = [
            'de inchiriat' => 'urn:concept:house-for-rent',
            'de vanzare' => 'urn:concept:house-for-sale',
        ];

        return $categoryMap[$dealType];
    }

    public function mapFloorToUrn(?int $floor): ?string
    {
        $floorMap = [
            0 => 'urn:concept:ground-floor',
            1 => 'urn:concept:1st-floor',
            2 => 'urn:concept:2nd-floor',
            3 => 'urn:concept:3rd-floor',
            4 => 'urn:concept:4th-floor',
            5 => 'urn:concept:5th-floor',
            6 => 'urn:concept:6th-floor',
            7 => 'urn:concept:7th-floor',
            8 => 'urn:concept:8th-floor',
            9 => 'urn:concept:9th-floor',
            10 => 'urn:concept:10th-floor',
            11 => 'urn:concept:11th-floor-and-above',
            12 => 'urn:concept:11th-floor-and-above',
            13 => 'urn:concept:11th-floor-and-above',
        ];

        return $floorMap[$floor] ?? null;
    }
}
