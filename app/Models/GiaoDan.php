<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiaoDan extends Model
{
    use CrudTrait;
    use HasFactory;
    
    protected $table = 'parishioners';
    
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'pid',
        'deid',
        'did',
        'paid',
        'assid',
        'origin',
        'ward',
        'province',
        'residence',
        'resi_ward',
        'resi_province',
        'professional_level',
        'study',
        'new_convert',
        'married',
        'statistical',
        'note',
        'baptism_date',
        'baptism_number',
        'baptism_giver',
        'baptism_sponsor',
        'baptism_dioceses',
        'baptism_deanerys',
        'baptism_parish',
        'more_power_date',
        'more_power_number',
        'more_power_giver',
        'more_power_sponsor',
        'more_power_dioceses',
        'more_power_deanerys',
        'more_power_parish',
        'communion_date',
        'communion_number',
        'communion_giver',
        'communion_dioceses',
        'communion_deanerys',
        'communion_parish',
        'anoint_date',
        'anoint_status',
        'anoint_giver',
        'anoint_note',
        'die_status',
        'die_time',
        'die_lottery',
        'die_death',
        'die_burial',
        'phone',
        'email',
        'image',
        'father',
        'mother',
        'sex',
        'birthday',
        'cccd',
        'holy',
        'ethnic',
        'career',
        'level',
        'position',
        'language',
        'status',
        'created_at',
        'updated_at'
    ];
    
    protected $dates = ['created_at', 'updated_at'];
    
    //protected $with = ['user'];
    
    /**
     * User this order Belongs To
     * @return BelongsTo
     */
    /*
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id',
            'user'
        );
    }
    
    /**
     * Order Items
     * @return HasMany
     */
    /*
    public function items(): HasMany
    {
        return $this->hasMany(
            OrderItem::class,
            'order_id',
            'id'
        );
    }*/
    
}
