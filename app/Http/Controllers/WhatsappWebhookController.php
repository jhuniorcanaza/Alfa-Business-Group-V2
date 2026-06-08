<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\User;
use App\Models\WhatsappMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappWebhookController extends Controller
{
    /**
     * Verificación del webhook de Meta (GET).
     */
    public function verify(Request $request)
    {
        $verifyToken = config('services.whatsapp.verify_token');

        if ($request->get('hub_mode') === 'subscribe' &&
            $request->get('hub_verify_token') === $verifyToken) {
            return response($request->get('hub_challenge'), 200);
        }

        return response('Forbidden', 403);
    }

    /**
     * Recibir mensajes de WhatsApp (POST).
     */
    public function receive(Request $request)
    {
        $data = $request->all();

        Log::info('WhatsApp Webhook:', $data);

        // Extraer el mensaje del payload de Meta
        $entry = $data['entry'][0] ?? null;
        if (!$entry) return response('OK', 200);

        $changes = $entry['changes'][0] ?? null;
        if (!$changes) return response('OK', 200);

        $value = $changes['value'] ?? null;
        if (!$value || !isset($value['messages'])) return response('OK', 200);

        $message = $value['messages'][0];
        $from = $message['from']; // Número del remitente
        $messageType = $message['type'];
        $messageId = $message['id'];

        // Buscar el asesor por su número de teléfono
        $cleanPhone = $this->cleanPhoneNumber($from);
        $user = User::where('role', 'asesor')
            ->where('is_active', true)
            ->where(function ($q) use ($cleanPhone, $from) {
                $q->where('phone', 'LIKE', "%{$cleanPhone}%")
                  ->orWhere('phone', $from);
            })
            ->first();

        if (!$user) {
            $this->sendWhatsAppMessage($from, "❌ No estás registrado en el sistema Alfa Business Group. Contacta a tu Director para ser agregado.");
            return response('OK', 200);
        }

        // Verificar si ya envió reporte hoy
        $today = Carbon::today();
        $existingReport = DailyReport::where('user_id', $user->id)
            ->where('report_date', $today)
            ->first();

        if ($existingReport) {
            $this->sendWhatsAppMessage($from, "⚠️ Hola {$user->name}, ya enviaste tu reporte de hoy.\n\n📊 Tu reporte:\n🏠 Visitas: {$existingReport->visits}\n📋 Letrero: {$existingReport->sign_captures}\n📝 Exclusiva: {$existingReport->exclusive_captures}\n🤝 Cierres: {$existingReport->closings}\n📞 Llamadas: {$existingReport->calls_made}\n💻 AlphaX: {$existingReport->properties_in_system}\n\nSolo puedes enviar un reporte por día.");
            return response('OK', 200);
        }

        // Extraer texto del mensaje
        $messageText = '';
        if ($messageType === 'text') {
            $messageText = $message['text']['body'] ?? '';
        } else {
            $this->sendWhatsAppMessage($from, "📝 Hola {$user->name}, por ahora solo acepto mensajes de texto.\n\nEnvía tu reporte con los 6 indicadores del día. Ejemplo:\n\n_Visitas 5, Letrero 2, Exclusiva 1, Cierres 0, Llamadas 8, Propiedades 3_");
            return response('OK', 200);
        }

        // Guardar mensaje recibido
        WhatsappMessage::create([
            'user_id' => $user->id,
            'phone_number' => $from,
            'message_type' => $messageType,
            'content' => $messageText,
            'direction' => 'incoming',
            'whatsapp_message_id' => $messageId,
        ]);

        // Procesar el mensaje con Grok AI
        $extractedData = $this->extractKPIsWithGrok($messageText, $user->name);

        if (!$extractedData) {
            $this->sendWhatsAppMessage($from, "🤔 Hola {$user->name}, no pude entender tu mensaje.\n\nEnvía tu reporte indicando los 6 datos del día. Ejemplo:\n\n_Hoy hice 5 visitas, 2 con letrero, 1 exclusiva, 0 cierres, 8 llamadas y subí 3 propiedades_\n\nO de forma corta:\n_5, 2, 1, 0, 8, 3_");
            return response('OK', 200);
        }

        // Guardar el reporte
        $report = DailyReport::create([
            'user_id' => $user->id,
            'report_date' => $today,
            'visits' => $extractedData['visits'],
            'sign_captures' => $extractedData['sign_captures'],
            'exclusive_captures' => $extractedData['exclusive_captures'],
            'closings' => $extractedData['closings'],
            'calls_made' => $extractedData['calls_made'],
            'properties_in_system' => $extractedData['properties_in_system'],
            'source' => 'whatsapp',
        ]);

        // Confirmar al asesor
        $confirmMsg = "✅ *¡Reporte registrado exitosamente!*\n\n"
            . "📅 Fecha: {$today->format('d/m/Y')}\n"
            . "👤 Asesor: {$user->name}\n\n"
            . "📊 *Tus datos de hoy:*\n"
            . "🏠 Visitas: {$report->visits}\n"
            . "📋 Captaciones con Letrero: {$report->sign_captures}\n"
            . "📝 Captaciones con Exclusiva: {$report->exclusive_captures}\n"
            . "🤝 Cierres: {$report->closings}\n"
            . "📞 Llamadas: {$report->calls_made}\n"
            . "💻 Propiedades en AlphaX: {$report->properties_in_system}\n\n"
            . "¡Gracias por tu reporte! 💪";

        $this->sendWhatsAppMessage($from, $confirmMsg);

        // Guardar mensaje de confirmación
        WhatsappMessage::create([
            'user_id' => $user->id,
            'phone_number' => $from,
            'message_type' => 'text',
            'content' => $confirmMsg,
            'direction' => 'outgoing',
        ]);

        return response('OK', 200);
    }

    /**
     * Usar Grok AI para extraer los 6 KPIs del mensaje del asesor.
     */
    private function extractKPIsWithGrok(string $message, string $userName): ?array
    {
        $apiKey = config('services.gemini.api_key');

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(15)->post("https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => "Eres un asistente de Alfa Business Group que extrae datos de reportes diarios de asesores inmobiliarios. "
                            . "Debes extraer exactamente 6 números enteros del mensaje del asesor. "
                            . "Los 6 indicadores son (en este orden): "
                            . "1. Visitas Realizadas, 2. Captaciones con Letrero, 3. Captaciones con Exclusiva, "
                            . "4. Cierres (venta/anticrético/alquiler), 5. Llamadas Realizadas, 6. Propiedades subidas al sistema AlphaX. "
                            . "Responde ÚNICAMENTE con un JSON válido con estas claves exactas: "
                            . "{\"visits\": N, \"sign_captures\": N, \"exclusive_captures\": N, \"closings\": N, \"calls_made\": N, \"properties_in_system\": N}\n"
                            . "Si algún dato no se menciona, pon 0.\n\n"
                            . "Mensaje a procesar del asesor {$userName}: \"{$message}\""]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'responseMimeType' => 'application/json'
                ]
            ]);

            if (!$response->successful()) {
                Log::error('Gemini WhatsApp Extraction API Error: ' . $response->body());
                return null;
            }

            $content = $response->json('candidates.0.content.parts.0.text', '');
            $content = trim($content);

            $data = json_decode($content, true);

            if (!$data || isset($data['error'])) {
                return null;
            }

            // Validar que todos los campos existen y son enteros >= 0
            $fields = ['visits', 'sign_captures', 'exclusive_captures', 'closings', 'calls_made', 'properties_in_system'];
            foreach ($fields as $field) {
                if (!isset($data[$field]) || !is_numeric($data[$field]) || $data[$field] < 0) {
                    $data[$field] = 0;
                }
                $data[$field] = (int) $data[$field];
            }

            return $data;

        } catch (\Exception $e) {
            Log::error('Gemini WhatsApp Extraction Exception: ' . $e->getMessage());
            return null;
        }
    }


    /**
     * Enviar mensaje de WhatsApp vía Meta Cloud API.
     */
    private function sendWhatsAppMessage(string $to, string $text): void
    {
        $token = config('services.whatsapp.token');
        $phoneNumberId = config('services.whatsapp.phone_number_id');

        try {
            Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
            ])->post("https://graph.facebook.com/v21.0/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => ['body' => $text],
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp Send Error: ' . $e->getMessage());
        }
    }

    /**
     * Limpiar número de teléfono.
     */
    private function cleanPhoneNumber(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }
}
