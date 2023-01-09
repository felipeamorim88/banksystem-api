<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Enum;

class FinancialMovement extends Model
{
    use HasFactory;

    protected $table = 'financial_movement';
    protected $fillable = ['user_id', 'previous_value', 'value', 'status', 'transaction_hash','image_base64'];
    /**
 * The "booting" method of the model.
 *
 * @return void
 */
protected static function boot()
{
    parent::boot();

    // auto-sets values on creation
    static::creating(function ($query) {
        $query->transaction_hash = $query->transaction_hash ?? "";
        $query->image_base64 = $query->image_base64 ?? "";

        $query->status = $query->status ?? FinancialMovementStatus::PENDING;
    });
}

}

class FinancialMovementStatus extends Enum
{
    const PENDING = 1;
    const ACCEPTED = 2;
    const REJECTED = 3;

}