<?php

namespace App\Services\Imobiliare\Apis;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use SoapClient;
use Exception;

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

    public function showMap()
    {
        $sid = $this->getSessionId();
        return view('partials.imobiliare-map', ['sessionId' => $sid]);
    }

    public function verifyAgent(User $user)
    {
        $sid = $this->getSessionId();

        if ($user->imobiliare_id) {
            return;
        }

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
        $agents = $this->getAgentsList();
        $sid = $this->getSessionId();

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
}
