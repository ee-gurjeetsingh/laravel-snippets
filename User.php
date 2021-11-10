<?php

namespace App\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\ActivityLogger;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Services\User\Notifications\SetPasswordNotification;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Set the auto incrementing to false as we store id as uuid and it not
     * auto incrementing so when after create it return id = 0
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * To track only these fields
     *
     * @var array
     */
    protected static array $logAttributes = [
        'first_name',
        'last_name',
        'email',
        'role',
    ];

    /**
     * Log only updated fields
     *
     * @var bool
     */
    protected static bool $logOnlyDirty = true;

    /**
     * Do not log empty attributes
     *
     * @var bool
     */
    protected static bool $submitEmptyLogs = false;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }

    /**
     * Send the set password notification.
     *
     * @param string $token
     * @return void
     */
    public function setPasswordNotification(string $token)
    {
        $this->notify(new SetPasswordNotification($token));
    }

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected string $keyType = 'string';

    /**
     * Spatie log activity modifications while logging activity
     */
    protected static function bootLogsActivity()
    {
        static::eventsToBeRecorded()->each(function ($eventName) {
            return static::$eventName(function (Model $model) use ($eventName) {
                if (! $model->shouldLogEvent($eventName)) {
                    return;
                }

                $description = $model->getDescriptionForEvent($eventName);

                $logName = $model->getLogNameToUse($eventName);

                if ($description == '') {
                    return;
                }

                $attrs = $model->attributeValuesToBeLogged($eventName);

                if ($model->isLogEmpty($attrs) && ! $model->shouldSubmitEmptyLogs()) {
                    return;
                }

                $logger = app(ActivityLogger::class)
                    ->useLog($logName)
                    ->performedOn($model)
                    ->withProperties($attrs);

                if (method_exists($model, 'tapActivity')) {
                    $logger->tap([$model, 'tapActivity'], $eventName);
                }

                // Check while logout it was tracking some updated user data so to ignore it
                // added this condition
                if (
                    !Str::contains(url()->current(), 'logout') ||
                    ($description !== 'updated' && get_class($model) !== 'App\Models\User')
                ) {
                    $logger->log($description);
                }
            });
        });
    }
}
