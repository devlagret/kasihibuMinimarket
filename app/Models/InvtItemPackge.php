<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvtItemPackge extends Model
{
    protected $table        = 'invt_item_packge';
    protected $primaryKey   = 'item_packge_id';
    protected $guarded = [
        'updated_at',
        'created_at'
    ];
    public function item() {
        return $this->belongsTo(InvtItem::class,'item_id','item_id');
    }
    public function unit() {
        return $this->belongsTo(InvtItemUnit::class,'item_unit_id','item_unit_id');
    }
}
