<?php
/**
 * Created by PhpStorm.
 * User: runner
 * Date: 16-10-31
 * Time: 上午9:42
 */

namespace Runner\Revisionable;

use Illuminate\Database\Eloquent\Model;

class Revisions extends Model
{

    protected $fillable = [
        'type',
        'field',
        'old_value',
        'new_value',
        'revisionable_type',
        'revisionable_id',
        'user_id',
    ];


    public function revisionable()
    {
        return $this->morphTo();
    }


    public function user()
    {
        return $this->belongsTo(Auth::getProvider()->getModel());
    }


    public function getNewValueAttribute($value)
    {
        return $this->getValue($value);
    }


    public function getOldValueAttribute($value)
    {
        return $this->getValue($value);
    }


    protected function getValue($value)
    {
        $rules = $this->revisionable->getRevisionFormattedFieldValues();

        if (isset($rules[$this->getOriginal('field')])) {
            return Formatter::format($rules[$this->getOriginal('field')], $value);
        }

        return $value;
    }


    public function getFieldAttribute($value)
    {
        $aliases = $this->revisionable->getRevisionAliasedFieldNames();

        if (isset($aliases[$value])) {
            return $aliases[$value];
        }

        return $value;
    }

}
