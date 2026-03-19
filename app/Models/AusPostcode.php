<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AusPostcode extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'auspostcodes';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false; // The table does not have created_at/updated_at columns

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pcode',
        'locality',
        'state',
        'comments',
        'deliveryoffice',
        'presortindicator',
        'parcelzone',
        'bspnumber',
        'bspname',
        'category',
        'lat',
        'long',      // reserved keyword – but Eloquent handles it
        'dateofupdate',
        'otherid',
        'type',
    ];

    // Optional: If you want to cast any attributes, add a $casts array
    // protected $casts = [
    //     'lat' => 'decimal:8',
    //     'long' => 'decimal:8',
    // ];
}
