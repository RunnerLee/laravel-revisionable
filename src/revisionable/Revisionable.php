<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 16-10-31 上午 10:52
 */

namespace Runner\Revisionable;

use Auth, Request;

/**
 * Class Revisionable
 * @package Runner\Revisionable
 */
trait Revisionable
{

    /**
     * @var array
     */
    protected $dirtyData = [];


    public static function boot()
    {
        parent::boot();
    }


    public static function bootRevisionable()
    {
        static::created(function($model) {
            if(!isset($model->revisionEnabled) || $model->revisionEnabled) {
                $model->postCreate();
            }
        });

        static::updating(function($model) {
            if(!isset($model->revisionEnabled) || $model->revisionEnabled) {
                $model->preUpdate();
            }
        });

        static::updated(function($model) {
            if(!isset($model->revisionEnabled) || $model->revisionEnabled) {
                $model->postUpdate();
            }
        });

        static::deleted(function($model) {
            if(!isset($model->revisionEnabled) || $model->revisionEnabled) {
                $model->postDelete();
            }
        });
    }


    public function preUpdate()
    {
        $this->dirtyData = array_intersect_key($this->original, $this->getDirty());

        if (isset($this->revisionOnlyFields)) {
            foreach ($this->revisionOnlyFields as $key) {
                if(!isset($this->dirtyData[$key])) {
                    unset($this->dirtyData[$key]);
                }
            }
            return true;
        }

        if (isset($this->revisionExceptFields)) {
            foreach ($this->revisionExceptFields as $key) {
                if(isset($this->dirtyData[$key])) {
                    unset($this->dirtyData);
                }
            }
        }

        return true;
    }


    public function postDelete()
    {
        if(isset($this->forceDeleting) && !$this->forceDeleting) {
            $this->dirtyData = [
                'deleted_at' => null,
            ];

            return $this->postUpdate();
        }

        Revision::create([
            'user_id'           => $this->lookupUserId(),
            'revisionable_type' => $this->getMorphClass(),
            'revisionable_id'   => $this->getKey(),
            'type'              => 'delete',
            'old_value'         => null,
            'new_value'         => null,
            'field'             => null,
            'ip'                => $this->getIp(),
        ]);

        return true;
    }


    public function postUpdate()
    {
        foreach($this->dirtyData as $key => $value) {
            Revision::create([
                'user_id'           => $this->lookupUserId(),
                'revisionable_type' => $this->getMorphClass(),
                'revisionable_id'   => $this->getKey(),
                'type'              => 'update',
                'old_value'         => in_array($key, $this->getRevisionNotRecordValueFields()) ? '' : $value,
                'new_value'         => in_array($key, $this->getRevisionNotRecordValueFields()) ? '' : $this->getAttribute($key),
                'field'             => $key,
                'ip'                => $this->getIp(),
            ]);
        }

        $this->dirtyData = [];

        return true;
    }


    public function postCreate()
    {
        Revision::create([
            'user_id'           => $this->lookupUserId(),
            'revisionable_type' => $this->getMorphClass(),
            'revisionable_id'   => $this->getKey(),
            'type'              => 'create',
            'old_value'         => null,
            'new_value'         => null,
            'field'             => null,
            'ip'                => $this->getIp(),
        ]);

        return true;
    }


    /**
     * @return array
     */
    public function getRevisionFormattedFieldValues()
    {
        if (isset($this->revisionFormattedFieldValues)) {
            return $this->revisionFormattedFieldValues;
        }
        return [];
    }


    /**
     * @return array
     */
    public function getRevisionAliasedFieldNames()
    {
        if (isset($this->revisionAliasedFieldNames)) {
            return $this->revisionAliasedFieldNames;
        }
        return [];
    }


    /**
     * @return array
     */
    public function getRevisionNotRecordValueFields()
    {
        if(isset($this->revisionNotRecordValueFields)) {
            return $this->revisionNotRecordValueFields;
        }
        return [];
    }


    /**
     * @return string|integer|null
     */
    protected function lookupUserId()
    {
        if(Auth::check()) {
            return Auth::user()->getAuthIdentifier();
        }

        return null;
    }


    /**
     * @return string|null
     */
    protected function getIp()
    {
        if(isset($this->revisionRecordIp) && $this->revisionRecordIp) {
            return Request::ip();
        }
        return null;
    }
}