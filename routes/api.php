<?php

use App\Http\Controllers\WhatsappWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Alfa Business Group
|--------------------------------------------------------------------------
| Estas rutas son accesibles sin autenticación (webhooks externos).
*/

// WhatsApp Meta Cloud API Webhook
Route::get('/webhook/whatsapp', [WhatsappWebhookController::class, 'verify']);
Route::post('/webhook/whatsapp', [WhatsappWebhookController::class, 'receive']);
