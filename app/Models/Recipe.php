<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $guarded = array('id');

    // リレーション設定
    public function materials()
    {
        return $this->hasMany('App\Models\Material');
    }
    public function processes()
    {
        return $this->hasMany('App\Models\Process');
    }
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    // レコード取得時のcreated_atのフォーマット指定
    public function getCreatedAtAttribute($date)
    {
        return date('Y-m-d H:i:s', strtotime($date));
    }
}
