<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Passport\HasApiTokens;

/**
 * Class User
 * @package App\Models
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $phone
 * @property string $phone2
 * @property boolean $verified
 * @property Carbon $born_at
 * @property string $language
 * @property string $identification
 * @property IdentificationType $identificationType
 * @property User $referrer_id
 * @property Locker $locker
 * @property Country $country
 * @property Collection $cards
 * @property Timezone $timezone
 * @property Platform $platform
 * @property string $created_at
 * @property Package $packages
 * @property Purchase $purchases
 * @property Collection $coupons
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable, SoftDeletes;

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
        'phone',
        'phone2',
        'verified',
        'born_at',
        'language',
        'identification',
        'identification_type_id',
        'referrer_id',
        'country_id',
        'timezone_id',
        'platform_id',
        'external_id',
        'created_at'
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function locker()
    {
        return $this->hasOne(Locker::class);
    }

    /**
     * @return string|null
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function coupons()
    {
        return $this->belongsToMany(Coupon::class)->withPivot('charged_at');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function timezone()
    {
        return $this->belongsTo(Timezone::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function identificationType()
    {
        return $this->belongsTo(IdentificationType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function referrer()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function referrals()
    {
        return $this->hasMany(User::class);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCreatedAtBeforeThan($query, $date)
    {
        $date = Carbon::parse($date)->format('Y-m-d');
        return !$date ? $query : $query->where('users.created_at', '<=', $date . '  23:59:59');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCreatedAtAfterThan($query, $date)
    {
        $date = Carbon::parse($date)->format('Y-m-d');
        return !$date ? $query : $query->where('users.created_at', '>=', $date . ' 00:00:00');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfId($query, $id)
    {
        if (is_array($id) && !empty($id)) {
            return $query->whereIn('users.id', $id);
        } else {
            return !$id ? $query : $query->where('users.id', $id);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $verified
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfEmailVerifiedAt($query, $verified)
    {
        return !$verified ? $query->whereNotNull('email_verified_at') : $query->whereNull('email_verified_at');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $platform_id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfPlatformId($query, $platform_id)
    {
        if (is_array($platform_id) && !empty($platform_id)) {
            return $query->whereIn('users.platform_id', $platform_id);
        } else {
            return !$platform_id ? $query : $query->where('users.platform_id', $platform_id);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $identification
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfIdentification($query, $identification)
    {
        return !$identification ? $query : $query->where('users.identification', $identification);
    }

    /**
     * @param $query
     * @param $full_name
     * @return mixed
     */
    public function scopeOfFullName($query, $full_name)
    {
        return !$full_name ? $query : $query->where(function ($q2) use ($full_name) {
            $q2->where('users.first_name', 'ilike', "%{$full_name}%");
            $q2->orWhere('users.last_name', 'ilike', "%{$full_name}%");
        });
    }

    /**
     * @param $query
     * @param $email
     * @return mixed
     */
    public function scopeOfEmail($query, $email)
    {
        return !$email ? $query : $query->where('users.email', 'ilike', "%{$email}%");
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $referrer_id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfReferrerId($query, $referrer_id)
    {
        return !$referrer_id ? $query : $query->where('users.referrer_id', $referrer_id);
    }

    /**
     * @return string
     */
    public function getFullNameAttribute()
    {
        $full_name = $this->first_name;

        if (!empty($this->last_name)) {
            $full_name .= ' ' . $this->last_name;
        }

        return $full_name;
    }

    /**
     * @return string|null
     */
    public function getLockerCode()
    {
        return $this->locker ? $this->locker->code : null;
    }

    /**
     * @return bool
     */
    public function getHasCardsAttribute()
    {
        return $this->cards->isNotEmpty();
    }

    /**
     * @return string
     */
    public function getUserPassword()
    {
        return $this->getAuthPassword();
    }

    /**
     * @return Card|null
     */
    public function getDefaultCard()
    {
        return $this->cards->filter(function ($card) {
            return $card->default;
        })->first();
    }

    /**
     * @return string|null
     */
    public function getCountryCode()
    {
        return $this->country ? $this->country->code : null;
    }

    /**
     * @return string|null
     */
    public function getCountryName()
    {
        return $this->country ? $this->country->name : null;
    }

    public function getFirst1Name()
    {
        $firstNames = collect(explode(' ', preg_replace('/\s+/', ' ', $this->first_name)));

        return $firstNames->get(0);
    }

    public function getFirst2Name()
    {
        $firstNames = collect(explode(' ', preg_replace('/\s+/', ' ', $this->first_name)));

        return $firstNames->get(1);
    }

    public function getLast1Name()
    {
        $lastNames = collect(explode(' ', preg_replace('/\s+/', ' ', $this->last_name)));

        return $lastNames->get(0);
    }

    public function getLast2Name()
    {
        $lastNames = collect(explode(' ', preg_replace('/\s+/', ' ', $this->last_name)));

        return $lastNames->get(1);
    }

    public function getIdentificationTypeKey()
    {
        return $this->identificationType ? $this->identificationType->key : null;
    }

    public function getPurchasesCount()
    {
        return $this->purchases ? $this->purchases->count() : null;
    }

    public function getPackagesCount()
    {
        return $this->packages ? $this->packages->count() : null;
    }

    /**
     * @return int
     */
    public function getCardsCount()
    {
        return $this->cards->count();
    }

    /**
     * @param string $token
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * @return bool
     */
    public function isCorrectPlatform()
    {
        /** @var Platform $platform */
        $platform = $this->platform;

        return $platform->key === current_platform()->key;
    }

    public function isPlatformMailamericas()
    {
        return $this->platform ? $this->platform->isMailamericas() : false;
    }

    public function isPlatformCorreosEcuador()
    {
        return $this->platform ? $this->platform->isCorreosEcuador() : false;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * @return bool
     */
    public function isFirstPackage()
    {
        return $this->packages->count() == 1 ? true : false;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->email_verified_at ? 'Verificado' : 'Pendiente verificación';
    }

    /**
     * @return string
     */
    public function getCouponStatus()
    {
       // Verificar esto
       if ($this->purchases) {
           return $this->purchases->count() ? true : false;
       } else {
           return false;
       }
    }

    /**
     * @return string
     */
    public function getReferralLinkAttribute()
    {
        return $url = route('home') . '/account/register?ref=' . $this->email;
    }

}
