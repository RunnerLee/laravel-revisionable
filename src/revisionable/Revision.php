<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 16-10-31 上午 10:52
 */

namespace Runner\Revisionable;

use Illuminate\Database\Eloquent\Model;
use Auth;
use Symfony\Component\Debug\Exception\FatalErrorException;

class Revision extends Model
{

    protected $fillable = [
        'type',
        'field',
        'old_value',
        'new_value',
        'revisionable_type',
        'revisionable_id',
        'user_id',
        'ip',
    ];


    public function revisionable()
    {
        return $this->morphTo();
    }


    public function user()
    {
        if (method_exists(Auth::getProvider(), 'getModel')) {
            return $this->belongsTo(Auth::getProvider()->getModel());
        }

        return $this->belongsTo(Auth::getProvider()->createModel());
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
        if(!class_exists($this->revisionable_type)) {
            return $value;
        }
        $model = new $this->revisionable_type;

        $rules = $model->getRevisionFormattedFieldValues();

        if (isset($rules[$this->getOriginal('field')])) {
            return Formatter::format($rules[$this->getOriginal('field')], $value);
        }

        return $value;
    }


    public function getFieldAttribute($value)
    {
        if(!class_exists($this->revisionable_type)) {
            return $value;
        }
        $model = new $this->revisionable_type;

        if (isset($model->getRevisionAliasedFieldNames()[$value])) {
            return $model->getRevisionAliasedFieldNames()[$value];
        }

        return $value;
    }

}
