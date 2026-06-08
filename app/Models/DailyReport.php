<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'report_date',
        'visits',
        'visits_data',
        'sign_captures',
        'exclusive_captures',
        'closings',
        'calls_made',
        'call_phone_number',
        'properties_in_system',
        'source',
        'notes',
        'sign_image_path',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'visits' => 'integer',
            'visits_data' => 'array',
            'sign_captures' => 'integer',
            'exclusive_captures' => 'integer',
            'closings' => 'integer',
            'calls_made' => 'integer',
            'properties_in_system' => 'integer',
        ];
    }

    // ─── Relaciones ─────────────────────────────────────────

    /**
     * Asesor que envió este reporte.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mensajes de WhatsApp que generaron este reporte.
     */
    public function whatsappMessages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class);
    }

    // ─── Helpers ────────────────────────────────────────────

    /**
     * Total de captaciones del día (letrero + exclusiva).
     */
    public function totalCaptures(): int
    {
        return $this->sign_captures + $this->exclusive_captures;
    }
}
