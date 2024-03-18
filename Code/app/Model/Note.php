<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = ['title', 'content'];

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'note_tag');
    }
    public function scopeWhereTitle($query, $title)
    {
        return $query->where('title', $title);
    }

}
