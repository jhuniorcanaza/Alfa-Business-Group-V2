<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_id',
        'indicator',
        'weekly_goal',
        'yellow_threshold_pct',
        'red_threshold_pct',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'office_id' => 'integer',
            'weekly_goal' => 'integer',
            'yellow_threshold_pct' => 'integer',
            'red_threshold_pct' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    // ─── Relaciones ─────────────────────────────────────────

    /**
     * Oficina a la que pertenece esta configuración de KPI.
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    /**
     * Director que configuró este KPI.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Helpers ────────────────────────────────────────────

    /**
     * Calcula el color del semáforo según el porcentaje de cumplimiento.
     */
    public function getTrafficLightColor(int $currentValue): string
    {
        if ($this->weekly_goal <= 0) {
            return 'green';
        }

        $percentage = ($currentValue / $this->weekly_goal) * 100;

        if ($percentage >= 100) {
            return 'green';
        }

        if ($percentage <= $this->red_threshold_pct) {
            return 'red';
        }

        return 'yellow';
    }
}
