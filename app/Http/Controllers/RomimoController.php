<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Services\Romimo\RomimoApiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RomimoController extends Controller
{
    protected RomimoApiService $romimoApiService;

    public function __construct(RomimoApiService $romimoApiService)
    {
        $this->romimoApiService = $romimoApiService;
    }

    public function createPayLoad(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'property_id' => 'required',
        ]);

        $property = Property::find($validated['property_id']);
        $latitude = $validated['latitude'];
        $longitude = $validated['longitude'];

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
            "properties" => [
                [
                    "key" => "nr_camere",
                    "value" => "3"
                ],
            ],
            "pictures" => collect($property->images)->map(function ($image, $index) {
                return [
                    "url" => asset('storage/' . $image->path),
                    "rank" => $index + 1
                ];
            })->toArray(),
        ];

        dd($payload);

        $response = $this->romimoApiService->createOrUpdateArticle($payload);

        return response()->json([
            'message' => 'Anunț trimis către Romimo',
            'romimo_response' => $response,
        ]);
    }
}
