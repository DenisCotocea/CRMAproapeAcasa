<?php

namespace App\Http\Controllers;

use App\Models\Property;
use http\Client\Curl\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\Olx\OlxService;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class OlxController extends Controller
{
    protected $olx;

    public function __construct(OlxService $olx)
    {
        $this->olx = $olx;
    }

    public function redirectToOlx()
    {
        $clientId = env('OLX_CLIENT_ID');
        $state = 'securetoken';

        $url = "https://storia.ro/ro/crm/authorization?response_type=code&client_id={$clientId}&state={$state}";

        return response()->json(['redirect' => $url], 200);
    }

    public function postAd(Request $request)
    {
        try {
            if($this->olx->getAccessToken() === null) {
                if($this->olx->getRefreshToken() === null){
                    return redirect()->route('olx.connect');
                }else{
                    $this->olx->refreshToken();
                }
            }

            $validated = $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'property_id' => 'required',
            ]);

            $property = Property::with(['images'])->findOrFail($validated['property_id']);

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

            $contact = [
                'name'  => $property->user->name,
                'phone' => $property->user->phone,
                'email' => $property->user->email,
            ];

            if ($property->user?->image?->path) {
                $contact['photo'] = url($property->user->image->path);
            }

            $images = $property->images
                ->filter(fn ($img) => $img && filled($img->path))
                ->map(fn ($img) => ['url' => Storage::disk('public')->url($img->path)])
                ->values()
                ->toArray();

            $adData = [
                'title' =>  $property->name,
                'description' => $property->description,
                'category_urn' => $category,
                'contact'      => $contact,
                'price' => [
                    'value' => (int) $property->price,
                    'currency' => 'EUR',
                ],
                'location' => [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'exact' => true,
                ],
                'images' => $images,
                'attributes' => [
                    [
                        'urn' => 'urn:concept:net-area-m2',
                        'value' => (string) $property->usable_area,
                    ],
                    [
                        'urn' => 'urn:concept:construction-year',
                        'value' => (string) $property->construction_year,
                    ],
                    [
                        'urn' => 'urn:concept:building-floors',
                        'value' => (string) $property->total_floors,
                    ],
                    [
                        'urn' => 'urn:concept:area-m2',
                        'value' => (string) $property->surface,
                    ],
                    [
                        'urn' => 'urn:concept:price-per-sq-meter',
                        'value' => (string) $property->calculatePricePerSquareMeter(),
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
                'status'      => $response->status(),
                'headers'     => $response->headers(),
                'json'        => $response->json(),
                'body'        => $response->body(),
            ]);

            if (!in_array($response->status(), [200, 201])) {
                return response()->json([
                    'error' => $response['body']['error'] ?? 'Unknown error from Storia.ro',
                ], 500);
            }

            $property->olx_uuid = $response->json()['data']['uuid'] ?? null;
            $property->imported_olx = 1;
            $property->save();

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

    public function deactivateAd(Request $request)
    {
        try {
            if ($this->olx->getAccessToken() === null) {
                if ($this->olx->getRefreshToken() === null) {
                    return redirect()->route('olx.connect');
                }
                $this->olx->refreshToken();
            }

            $validated = $request->validate(['property_id' => 'required|integer']);
            $property  = Property::find($validated['property_id']);

            if (!$property) {
                throw new \Exception("Property ID {$validated['property_id']} not found.");
            }

            $advertUuid = $property->olx_uuid;

            Log::channel('olx_apis')->debug('Deleting advert on Storia/OLX', [
                'property_id' => $property->id,
                'advert_uuid' => $advertUuid,
            ]);

            $response = $this->olx->deactivateAd($advertUuid);

            if (!in_array($response->status(), [200, 204], true)) {
                return response()->json([
                    'error' => $response->json('error.title') ?? $response->body() ?? 'Unknown error from Storia/OLX',
                    'status' => $response->status(),
                ], 500);
            }

            $property->imported_olx = 0;
            $property->save();

            return response()->json(['message' => 'Property desactivated from Storia/OLX.']);
        } catch (\Throwable $e) {
            Log::channel('olx_apis')->error('Delete advert failed', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'There was an error: '.$e->getMessage()], 500);
        }
    }

    public function handleCallback(Request $request)
    {
        $code = $request->query('code');

        if (!$code) {
            return redirect()->route('properties.portfolioView')->with('error', 'Missing code.');
        }

        $tokenData = $this->olx->getToken($code);

        return redirect()->route('properties.portfolioView')->with('success', 'API Token refresh successful.');
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
