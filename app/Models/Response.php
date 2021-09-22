<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Response extends Model
{
    use HasFactory;

    protected $fillable = ['thread_id', 'origin_d_post_id', 'dest_d_post_id'];
    protected $hidden = ['created_at', 'updated_at'];
    //リレーション定義
    public function thread()
    {
        return $this->belongsTo((Thread::class));
    }

    //呼び出しメソッド
    public function returnRespondedCountTable($thread_id)
    {
        return $this->select('dest_d_post_id', DB::raw('count(*) as responded_count'))
            ->where('thread_id', $thread_id)->groupBy('dest_d_post_id');
    }
}
