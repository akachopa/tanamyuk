<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'garden_id',
        'planting_cycle_id',
        'expense_category_id',
        'expense_date',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'total',
        'receipt_image_path',
        'client_mutation_id',
        'server_version',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
            'server_version' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function garden(): BelongsTo
    {
        return $this->belongsTo(Garden::class);
    }

    public function plantingCycle(): BelongsTo
    {
        return $this->belongsTo(PlantingCycle::class);
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }
}