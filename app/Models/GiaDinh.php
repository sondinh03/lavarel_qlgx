<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiaDinh extends Model
{
    use CrudTrait;
    use HasFactory;
    
    protected $table = 'family';
    
    protected $guarded = ['id'];
    protected $fillable = [
        'mother',
        'father',
        'household',
        'name',
        'did',
        'deid',
        'pid',
        'paid',
        'idhouse',        
        'dien',
        'songuoi',
        'phone',
        'origin',
        'ward',
        'province',
        'noio',
        'thongke',
        'note',
        'image',
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
