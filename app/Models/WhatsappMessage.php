<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone_number',
        'direction',
        'message_type',
        'content',
        'original_content',
        'transcription',
        'extracted_data',
        'status',
        'daily_report_id',
        'whatsapp_message_id',
    ];

    protected function casts(): array
    {
        return [
            'extracted_data' => 'array',
        ];
    }

    // ─── Relaciones ─────────────────────────────────────────

    /**
     * Usuario asociado al mensaje.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Reporte diario generado a partir de este mensaje.
     */
    public function dailyReport(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class);
    }

    // ─── Helpers ────────────────────────────────────────────

    public function isIncoming(): bool
    {
        return $this->direction === 'incoming';
    }

    public function isAudio(): bool
    {
        return $this->message_type === 'audio';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }
}
