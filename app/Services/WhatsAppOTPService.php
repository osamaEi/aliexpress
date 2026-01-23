<?php

namespace App\Services;

class WhatsAppOTPService
{
    private $appId = "oFafUriVLBEhLkZoydSQL9vsbKbQM68G5zejBBab";
    private $appSecret = "1wIyZ8dwiSXDzwZ3sAavjyuD0XtoKTzs3E1MtZgy8yJkTtcAfXS5CbUCkv4K7oxAG5oWgDSqCpnet8Fj2Z1EoY3dzoioLT4Pfim5";
    private $projectId = 669;
    private $baseUrl = "https://api-users.4jawaly.com/api/v1/whatsapp/";
    private $namespace = "d62f7444_aa0b_40b8_8f46_0bb55ef2862e";

    public function sendOTP($phoneNumber, $code, $isEnglish = false)
    {
        try {
            $template = $isEnglish ? 'general_notices_en' : 'general_notices_ar';
            $language = $isEnglish ? 'en' : 'ar';

            $data = [
                "path" => "message/template",
                "params" => [
                    "phone" => $phoneNumber,
                    "template" => $template,
                    "language" => [
                        "policy" => "deterministic",
                        "code" => $language
                    ],
                    "namespace" => $this->namespace,
                    "params" => [
                        [
                            "type" => "body",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => $code
                                ]
                            ]
                        ],
                        [
                            "index" => "0",
                            "sub_type" => "URL",
                            "type" => "button",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => $code
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            return $this->makeRequest($data);

        } catch (\Exception $e) {
            \Log::error('WhatsApp OTP Error:', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function makeRequest($data)
    {
        $url = $this->baseUrl . $this->projectId;

        $headers = [
            "accept: application/json",
            "Authorization: Basic " . base64_encode("$this->appId:$this->appSecret"),
            "Content-Type: application/json"
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $result = [
                'success' => false,
                'error' => curl_error($ch)
            ];
        } else {
            $result = [
                'success' => true,
                'response' => json_decode($response, true)
            ];
        }

        curl_close($ch);
        return $result;
    }
}
