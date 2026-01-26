<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;

class ScmLocationLink extends Model
{
    protected $table = 'fm_scm_location_link';
    public $timestamps = false;

    protected $fillable = [
        'wh_seq',
        'location_position',
        'location_code',
        'goods_seq',
        'goods_code',
        'goods_name',
        'option_type',
        'option_seq',
        'option_name',
        'ea',
        'bad_ea',
        'supply_price',
    ];

    // No primary key defined in schema check, assuming composite or none?
    // Often these link tables don't have a single PK. 
    // We should be careful with save(). Maybe use query builder for updates if no PK.
    // Let's assume no PK for now or composite. Eloquent doesn't support composite PKs well natively without traits.
    // I'll set incrementing to false.
    public $incrementing = false;
    protected $primaryKey = null; 
}
