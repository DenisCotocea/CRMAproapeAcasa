<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Services\Imobiliare\Apis\ImobiliareApiService;
use App\Models\User;
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

            $images = [];

            foreach ($property->images as $index => $image) {
                $path = public_path($image->path);

                if (!file_exists($path)) {
                    continue;
                }

                $blob = file_get_contents($path);
                [$width, $height] = getimagesize($path);
                $images[] = [
                    'blob' => $blob,
                    'width' => $width,
                    'height' => $height,
                    'position' => $index + 1,
                    'timestamp' => time(),
                    'tip' => 'imagine',
                ];
            }

            $payload = [
                'id2' => (int) $property->unique_code,
                'titlu' =>  $property->name,
                'descriere' => $property->description,
                'tara' => 1048,
                'judet' => 2,
                'localitate' => 3319,
                'zona' => 7777777,
                'devanzare' => strtolower(trim($property->tranzaction)) === 'sale' ? 1 : 0,
                'deinchiriat' => strtolower(trim($property->tranzaction)) === 'rent' ? 1 : 0,
                'siteagentie' => 1,
                'portal' => 1,
                'anuntbonus' => 0,
                'categorie' => 0,
                'suprafatautila' => (int) $property->usable_area,
                'latitudine' => $latitude,
                'longitudine' => $longitude,
                'caroiaj' => $this->generateCaroiaj($longitude, $latitude),
                'suprafatateren' => (int) $property->land_area,
                'inaltimespatiu' => 7,
                'tipimobil' => 0,
                'imagini' => $images,

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
                'etaj' => $property->floor,
                'suprafatacurte' => $property->land_area,
                'imoradar24' => 1,
            ];

            Log::channel('imobiliare_apis')->debug('Payload prepared for Imobiliare.ro API.', [
                'property_id' => $property->id,
                'payload' => $payload,
            ]);

            $response = $this->imobiliareApiService->createOrUpdateArticle($payload);

            dd($response, $payload);

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

    public function updateAgent(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (!$user->imobiliare_id) {
            return redirect()->back()->with('error', 'User is not linked to Imobiliare.ro');
        }

        try {
            $this->imobiliareApiService->updateAgent($user);
            return redirect()->back()->with('success', 'Agent updated on Imobiliare.ro');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update agent');
        }
    }

    public function generateCaroiaj(float $lon, float $lat): string
    {
        $projected = $this->lonlatToXY($lon, $lat);

        $iStartLon = 2252106;
        $iStartLat = 5404285;

        $sLon = floor(($projected['lon'] - $iStartLon) / 1000);
        $sLat = floor(($projected['lat'] - $iStartLat) / 1000);

        $caroiaj = substr(($iStartLon + $sLon * 1000), 0, 4) . substr(($iStartLat + $sLat * 1000), 0, 4);
        return $caroiaj;
    }

    private function lonlatToXY(float $lon, float $lat): array
    {
        $x = ($lon * 20037508.34) / 180;
        $y = log(tan((90 + $lat) * pi() / 360)) / (pi() / 180);
        $y = $y * 20037508.34 / 180;

        return ['lon' => $x, 'lat' => $y];
    }
}
