<?php

namespace App\Services\Imobiliare\Apis;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SoapClient;
use Exception;
use DOMDocument;

class ImobiliareApiService
{
    protected $soap;
    protected $sessionId;

    protected $soap_url;

    protected $soap_user;

    protected $soap_key;

    public function __construct()
    {
        $this->soap_url = config('services.imobiliare.api_url');
        $this->soap_user = config('services.imobiliare.api_user');
        $this->soap_key = config('services.imobiliare.api_key');
        $this->soap = new SoapClient($this->soap_url);
    }

    public function login(): string
    {
        try {
            $result = $this->soap->__soapCall('login', [
                'login' => [
                    'id'     => $this->soap_user,
                    'hid'    => $this->soap_key,
                    'server' => '',
                    'agent'  => '',
                    'parola' => '',
                ],
            ]);

            $parts = explode('#', $result->extra);
            $this->sessionId = $parts[1];
            return $this->sessionId;
        } catch (Exception $e) {
            Log::channel('imobiliare_apis')->error("SOAP Login failed error: " . $e->getMessage());
            throw new Exception('SOAP Login failed: ' . $e->getMessage());
        }
    }

    public function getSessionId(): string
    {
        return $this->sessionId ?? $this->login();
    }

    public function logout(): void
    {
        $sid = $this->getSessionId();

        $this->soap->__soapCall('logout', [
            'logout' => [
                'sid' => $sid,
                'id' => '',
                'jurnal' => '',
            ]
        ]);
    }

    //Functions for agents
    public function getAgentsList(): array
    {
        $sid = $this->getSessionId();

        try {
            $response = $this->soap->__soapCall('import_lista_agenti', [
                'import_lista_agenti' => [
                    'sid' => $sid,
                ],
            ]);

            $ids = preg_split('/\s+/', trim($response->extra));
            $agents = [];

            foreach ($ids as $id) {
                if (!is_numeric($id)) {
                    continue;
                }

                $agents[] = [
                    'id'    => (int) $id,
                ];
            }

            return $agents;
        } catch (Exception $e) {
            Log::channel('imobiliare_apis')->error("Failed to fetch agent list: " . $e->getMessage());
            throw new Exception('Failed to fetch agent list: ' . $e->getMessage());
        }
    }

    public function verifyAgent(User $user)
    {
        $sid = $this->getSessionId();

        try {
            $response = $this->soap->__soapCall('import_agent', [
                'import_agent' => [
                    'sid' => $sid,
                    'id' => $user->imobiliare_id,
                ],
            ]);

            return simplexml_load_string($response->extra);
        } catch (Exception $e) {
            Log::channel('imobiliare_apis')->error("Failed to fetch agent: " . $e->getMessage());
            throw new Exception('Failed to fetch agent: ' . $e->getMessage());
        }
    }

    public function syncAllAgents()
    {
        $sid = $this->getSessionId();

        $localUsers = User::whereNull('imobiliare_id')->get();

        foreach ($localUsers as $user) {
            $this->addAgent($user);
        }

        $agents = $this->getAgentsList();

        foreach ($agents as $agent) {
            try {
                $response = $this->soap->__soapCall('import_agent', [
                    'import_agent' => [
                        'sid' => $sid,
                        'id' => $agent['id'],
                    ],
                ]);

                $xml = simplexml_load_string($response->extra);
                if (!$xml) {
                    Log::channel('imobiliare_apis')->warning("Invalid XML for agent ID {$agent['id']}");
                    continue;
                }

                $email = (string) $xml->email;
                $imobiliareId = (int) $xml->id;

                $user = User::where('email', $email)->first();

                if ($user && !$user->imobiliare_id) {
                    $user->imobiliare_id = $imobiliareId;
                    $user->save();

                    Log::channel('imobiliare_apis')->info("Mapped user {$user->id} to Imobiliare ID $imobiliareId");
                } elseif (!$user) {
                    Log::channel('imobiliare_apis')->warning("No matching user for email: {$email}");
                }
            } catch (Exception $e) {
                Log::channel('imobiliare_apis')->error("Failed to fetch agent ID {$agent['id']}: " . $e->getMessage());
            }
        }
    }

    public function addAgent(User $user): void
    {
        $sid = $this->getSessionId();

        $password = md5('1q2w3imobiliare');

        $photoBase64 = '';
        if ($user->image && $user->image->path && file_exists(public_path($user->image->path))) {
            $photoPath = public_path($user->image->path);
            $photoMime = mime_content_type($photoPath);

            if (in_array($photoMime, ['image/jpeg', 'image/jpg'])) {
                $photoBase64 = base64_encode(file_get_contents($photoPath));
            } else {
                Log::channel('imobiliare_apis')->warning("Skipping profile image for user {$user->id} - invalid format ({$photoMime}). Only JPEG is supported.");
            }
        }

        $agentXml = <<<XML
            <agent>
              <email>{$user->email}</email>
              <functie>Agent</functie>
              <id>{$user->id}</id>
              <nume>{$user->name}</nume>
              <telefon>{$user->phone}</telefon>
              <username>{$user->email}</username>
              <password>{$password}</password>
              <poza>{$photoBase64}</poza>
              <drepturi_adminonline>oferte</drepturi_adminonline>
            </agent>
        XML;

        try {
            $this->soap->__soapCall('publica_agent', [
                'publica_agent' => [
                    'sid' => $sid,
                    'id' => $user->id,
                    'operatie' => 'ADD',
                    'agentxml' => $agentXml,
                ]
            ]);

            Log::channel('imobiliare_apis')->info("Created agent on Imobiliare.ro", [
                'email' => $user->email,
            ]);
        } catch (Exception $e) {
            Log::channel('imobiliare_apis')->error("Failed to save agent: " . $e->getMessage());
            throw new Exception('Failed to save agent: ' . $e->getMessage());
        }
    }

    public function updateAgent(User $user): void
    {
        $sid = $this->getSessionId();

        $photoBase64 = '';
        if ($user->image && $user->image->path && file_exists(public_path($user->image->path))) {
            $photoPath = public_path($user->image->path);
            $photoMime = mime_content_type($photoPath);

            if (in_array($photoMime, ['image/jpeg', 'image/jpg'])) {
                if (filesize($photoPath) <= 65000) {
                    $photoBase64 = base64_encode(file_get_contents($photoPath));
                } else {
                    Log::channel('imobiliare_apis')->warning("Agent photo too large for user {$user->id}. Skipping.");
                }
            } else {
                Log::channel('imobiliare_apis')->warning("Invalid image format for user {$user->id}. Only JPEG allowed.");
            }
        }

        $agentXml = <<<XML
            <agent>
              <functie>Agent</functie>
              <id>{$user->imobiliare_id}</id>
              <nume>{$user->name}</nume>
              <telefon>{$user->phone}</telefon>
              <drepturi_adminonline>oferte</drepturi_adminonline>
              <poza>{$photoBase64}</poza>
            </agent>
        XML;

        try {
            $this->soap->__soapCall('publica_agent', [
                'publica_agent' => [
                    'sid' => $sid,
                    'id' => $user->imobiliare_id,
                    'operatie' => 'MOD',
                    'agentxml' => $agentXml,
                ]
            ]);

            Log::channel('imobiliare_apis')->info("Updated agent on Imobiliare.ro (without changing login data)", [
                'user_id' => $user->id,
            ]);
        } catch (Exception $e) {
            Log::channel('imobiliare_apis')->error("Failed to update agent {$user->id}: " . $e->getMessage());
            throw new Exception("Failed to update agent: " . $e->getMessage());
        }
    }


    public function createOrUpdateArticle(array $payload): array
    {
        $sid = $this->getSessionId();

        try {
            $xml = new \DOMDocument('1.0', 'UTF-8');
            $xml->formatOutput = true;

            $oferta = $xml->createElement('oferta');
            $oferta->setAttribute('tip', $payload['tip'] ?? 'apartament');
            $oferta->setAttribute('versiune', '3');
            $xml->appendChild($oferta);

            foreach ($payload as $key => $value) {
                if (in_array($key, ['titlu', 'descriere'])) {
                    $element = $xml->createElement($key);
                    $lang = $xml->createElement('lang', base64_encode($value));
                    $lang->setAttribute('id', '1048');
                    $element->appendChild($lang);
                    $oferta->appendChild($element);
                } elseif ($key === 'imagini' && is_array($value)) {
                    $imaginiNode = $xml->createElement('imagini');
                    foreach ($value as $img) {
                        $imagineNode = $xml->createElement('imagine', base64_encode($img['blob']));
                        $imagineNode->setAttribute('latime', $img['width']);
                        $imagineNode->setAttribute('inaltime', $img['height']);
                        $imagineNode->setAttribute('pozitie', $img['position']);
                        $imagineNode->setAttribute('modificata', $img['timestamp']);
                        $imaginiNode->appendChild($imagineNode);
                    }
                    $oferta->appendChild($imaginiNode);
                } elseif ($value === null) {
                    continue;
                } elseif (is_array($value)) {
                    $oferta->appendChild($xml->createElement($key, implode(' ', $value)));
                } else {
                    $oferta->appendChild($xml->createElement($key, $value));
                }
            }

            $xmlPayload = $xml->saveXML();

            $response = $this->soap->__soapCall('publica_oferta', [
                'publica_oferta' => [
                    'id_str'        => '0:' . $payload['id2'],
                    'sid' 			=> $sid,
                    'operatie'		=> 'ADD',
                    'ofertaxml' 	=> $xmlPayload,
                ],
            ]);

            $codJudet = $this->getCodJudet('Brașov');

            dd($response, $xmlPayload);


            $result = [
                'status' => $response->status(),
                'body' => $response->json(),
            ];

            Log::channel('imobiliare_apis')->debug('Received response from Imobiliare.ro API.', [
                'response' => $result,
            ]);

            return $result;

        } catch (Exception $e) {
            Log::channel('imobiliare_apis')->error('Error sending payload to Imobiliare.ro API.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'status' => 500,
                'body' => ['error' => 'There was an error: ' . $e->getMessage()],
            ];
        }
    }

    public function getCodJudet(string $numeJudet): ?int
    {
        $sid = $this->getSessionId();
        $resp = $this->soap->__soapCall('obtine_parametrii', [['sid' => $sid]]);
        dd($resp);

        $xml = simplexml_load_string($resp->extra);
        dd($xml,$resp);

        return null;
    }
}
