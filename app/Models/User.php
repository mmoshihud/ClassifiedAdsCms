<?php
/**
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 *
 * LICENSE
 * -------
 * This software is furnished under a license and may be used and copied
 * only in accordance with the terms of such license and with the inclusion
 * of the above copyright notice. If you Purchased from CodeCanyon,
 * Please read the full License from here - http://codecanyon.net/licenses/standard
 */

namespace App\Models;

use App\Helpers\Date;
use App\Helpers\Files\Storage\StorageDisk;
use App\Models\Scopes\LocalizedScope;
use App\Models\Traits\CountryTrait;
use App\Notifications\ResetPasswordNotification;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Panel\Library\Traits\Models\Crud;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends BaseUser
{
	use Crud, HasRoles, CountryTrait, HasApiTokens, Notifiable, HasFactory;
	
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'users';
	
	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	// protected $primaryKey = 'id';
	protected $appends = ['created_at_formatted', 'photo_url', 'original_updated_at', 'original_last_activity', 'p_is_online'];
	
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var boolean
	 */
	public $timestamps = true;
	
	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = ['id'];
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'country_code',
		'language_code',
		'user_type_id',
		'gender_id',
		'name',
		'photo',
		'about',
		'phone',
		'phone_hidden',
		'email',
		'username',
		'password',
		'remember_token',
		'can_be_impersonate',
		'disable_comments',
		'ip_addr',
		'provider',
		'provider_id',
		'email_token',
		'phone_token',
		'verified_email',
		'verified_phone',
		'accept_terms',
		'accept_marketing_offers',
		'time_zone',
		'blocked',
		'closed',
		'last_activity',
	];
	
	/**
	 * The attributes that should be hidden for arrays
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];
	
	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['created_at', 'updated_at', 'last_login_at', 'deleted_at'];
	
	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'email_verified_at' => 'datetime',
	];
	
	/**
	 * User constructor.
	 *
	 * @param array $attributes
	 */
	public function __construct(array $attributes = [])
	{
		if (
			isAdminPanel()
			|| str_contains(Route::currentRouteAction(), 'InstallController')
			|| str_contains(Route::currentRouteAction(), 'UpgradeController')
		) {
			$this->fillable[] = 'is_admin';
		}
		
		parent::__construct($attributes);
	}
	
	/*
	|--------------------------------------------------------------------------
	| FUNCTIONS
	|--------------------------------------------------------------------------
	*/
	protected static function boot()
	{
		parent::boot();
		
		User::observe(UserObserver::class);
		
		static::addGlobalScope(new LocalizedScope());
	}
	
	public function routeNotificationForMail()
	{
		return $this->email;
	}
	
	public function routeNotificationForVonage()
	{
		$phone = phoneFormatInt($this->phone, $this->country_code);
		
		return setPhoneSign($phone, 'vonage');
	}
	
	public function routeNotificationForTwilio()
	{
		$phone = phoneFormatInt($this->phone, $this->country_code);
		
		return setPhoneSign($phone, 'twilio');
	}
	
	public function sendPasswordResetNotification($token)
	{
		if (request()->filled('email') || request()->filled('phone')) {
			if (request()->filled('email')) {
				$field = 'email';
			} else {
				$field = 'phone';
			}
		} else {
			if (!empty($this->email)) {
				$field = 'email';
			} else {
				$field = 'phone';
			}
		}
		
		try {
			$this->notify(new ResetPasswordNotification($this, $token, $field));
		} catch (\Throwable $e) {
			$msg = $e->getMessage();
			if (!isFromApi()) {
				flash($msg)->error();
			} else {
				abort(500, $msg);
			}
		}
	}
	
	/**
	 * Get the user's preferred locale.
	 *
	 * @return string
	 */
	public function preferredLocale()
	{
		return $this->language_code;
	}
	
	public function canImpersonate(): bool
	{
		// Cannot impersonate from Demo website,
		// Non admin users cannot impersonate
		if (isDemoDomain() || !$this->can(Permission::getStaffPermissions())) {
			return false;
		}
		
		return true;
	}
	
	public function canBeImpersonated(): bool
	{
		// Cannot be impersonated from Demo website,
		// Admin users cannot be impersonated,
		// Users with the 'can_be_impersonated' attribute != 1 cannot be impersonated
		if (isDemoDomain() || $this->can(Permission::getStaffPermissions()) || $this->can_be_impersonated != 1) {
			return false;
		}
		
		return true;
	}
	
	public function impersonateBtn($xPanel = false): string
	{
		$out = '';
		
		// Get all the User's attributes
		$user = self::findOrFail($this->getKey());
		
		// Get impersonate URL
		$impersonateUrl = dmUrl($this->country_code, 'impersonate/take/' . $this->getKey(), false, false);
		
		// If the Domain Mapping plugin is installed,
		// Then, the impersonate feature need to be disabled
		if (config('plugins.domainmapping.installed')) {
			return $out;
		}
		
		// Generate the impersonate link
		if ($user->getKey() == auth()->user()->getAuthIdentifier()) {
			$tooltip = '" data-bs-toggle="tooltip" title="' . t('Cannot impersonate yourself') . '"';
			$out .= '<a class="btn btn-xs btn-warning" ' . $tooltip . '><i class="fa fa-lock"></i></a>';
		} else if ($user->can(Permission::getStaffPermissions())) {
			$tooltip = '" data-bs-toggle="tooltip" title="' . t('Cannot impersonate admin users') . '"';
			$out .= '<a class="btn btn-xs btn-warning" ' . $tooltip . '><i class="fa fa-lock"></i></a>';
		} else if (!isVerifiedUser($user)) {
			$tooltip = '" data-bs-toggle="tooltip" title="' . t('Cannot impersonate unactivated users') . '"';
			$out .= '<a class="btn btn-xs btn-warning" ' . $tooltip . '><i class="fa fa-lock"></i></a>';
		} else {
			$tooltip = '" data-bs-toggle="tooltip" title="' . t('Impersonate this user') . '"';
			$out .= '<a class="btn btn-xs btn-light" href="' . $impersonateUrl . '" ' . $tooltip . '><i class="fas fa-sign-in-alt"></i></a>';
		}
		
		return $out;
	}
	
	public function deleteBtn($xPanel = false): string
	{
		$out = '';
		
		if (auth()->check()) {
			if ($this->id == auth()->user()->id) {
				return $out;
			}
			if (isDemoDomain() && $this->id == 1) {
				return $out;
			}
		}
		
		$url = admin_url('users/' . $this->id);
		
		$out .= '<a href="' . $url . '" class="btn btn-xs btn-danger" data-button-type="delete">';
		$out .= '<i class="far fa-trash-alt"></i> ';
		$out .= trans('admin.delete');
		$out .= '</a>';
		
		return $out;
	}
	
	public function isOnline(): bool
	{
		$isOnline = ($this->last_activity > Carbon::now(Date::getAppTimeZone())->subMinutes(5));
		
		// Allow only logged users to get the other users status
		return auth()->check() ? $isOnline : false;
	}
	
	/*
	|--------------------------------------------------------------------------
	| RELATIONS
	|--------------------------------------------------------------------------
	*/
	public function posts()
	{
		return $this->hasMany(Post::class, 'user_id')->orderByDesc('created_at');
	}
	
	public function gender()
	{
		return $this->belongsTo(Gender::class, 'gender_id', 'id');
	}
	
	public function receivedThreads()
	{
		return $this->hasManyThrough(
			Thread::class,
			Post::class,
			'user_id', // Foreign key on the Listing table...
			'post_id', // Foreign key on the Thread table...
			'id',      // Local key on the User table...
			'id'       // Local key on the Listing table...
		);
	}
	
	public function threads()
	{
		return $this->hasManyThrough(
			Thread::class,
			ThreadMessage::class,
			'user_id', // Foreign key on the ThreadMessage table...
			'post_id', // Foreign key on the Thread table...
			'id',      // Local key on the User table...
			'id'       // Local key on the ThreadMessage table...
		);
	}
	
	public function savedPosts()
	{
		return $this->belongsToMany(Post::class, 'saved_posts', 'user_id', 'post_id');
	}
	
	public function savedSearch()
	{
		return $this->hasMany(SavedSearch::class, 'user_id');
	}
	
	public function userType()
	{
		return $this->belongsTo(UserType::class, 'user_type_id');
	}
	
	/*
	|--------------------------------------------------------------------------
	| SCOPES
	|--------------------------------------------------------------------------
	*/
	public function scopeVerified($builder)
	{
		$builder->where(function ($query) {
			$query->where('verified_email', 1)->where('verified_phone', 1);
		});
		
		return $builder;
	}
	
	public function scopeUnverified($builder)
	{
		$builder->where(function ($query) {
			$query->where('verified_email', 0)->orWhere('verified_phone', 0);
		});
		
		return $builder;
	}
	
	/*
	|--------------------------------------------------------------------------
	| ACCESSORS | MUTATORS
	|--------------------------------------------------------------------------
	*/
	protected function createdAt(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				$value = new Carbon($value);
				$value->timezone(Date::getAppTimeZone());
				
				return $value;
			},
		);
	}
	
	protected function updatedAt(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				$value = new Carbon($value);
				$value->timezone(Date::getAppTimeZone());
				
				return $value;
			},
		);
	}
	
	protected function originalUpdatedAt(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				return $this->getRawOriginal('updated_at');
			},
		);
	}
	
	protected function lastActivity(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				$value = new Carbon($value);
				$value->timezone(Date::getAppTimeZone());
				
				return $value;
			},
		);
	}
	
	protected function originalLastActivity(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				return $this->getRawOriginal('last_activity');
			},
		);
	}
	
	protected function lastLoginAt(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				$value = new Carbon($value);
				$value->timezone(Date::getAppTimeZone());
				
				return $value;
			},
		);
	}
	
	protected function deletedAt(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				$value = new Carbon($value);
				$value->timezone(Date::getAppTimeZone());
				
				return $value;
			},
		);
	}
	
	protected function createdAtFormatted(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				if (!isset($this->attributes['created_at']) and is_null($this->attributes['created_at'])) {
					return null;
				}
				
				$value = new Carbon($this->attributes['created_at']);
				$value->timezone(Date::getAppTimeZone());
				
				return Date::formatFormNow($value);
			},
		);
	}
	
	protected function photoUrl(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				// Default Photo
				$defaultPhotoUrl = url('images/user.jpg');
				
				// Photo from User's account
				$userPhotoUrl = null;
				if (isset($this->photo) && !empty($this->photo)) {
					$disk = StorageDisk::getDisk();
					if ($disk->exists($this->photo)) {
						$userPhotoUrl = imgUrl($this->photo, 'user');
					}
				}
				
				return !empty($userPhotoUrl) ? $userPhotoUrl : $defaultPhotoUrl;
			},
		);
	}
	
	protected function email(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				if (isAdminPanel()) {
					if (
						isDemoDomain()
						&& request()->segment(2) != 'password'
					) {
						if (auth()->check()) {
							if (auth()->user()->getAuthIdentifier() != 1) {
								if (isset($this->phone_token)) {
									if ($this->phone_token == 'demoFaker') {
										return $value;
									}
								}
								$value = hidePartOfEmail($value);
							}
						}
					}
				}
				
				return $value;
			},
		);
	}
	
	protected function phone(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				$countryCode = config('country.code');
				if (isset($this->country_code) && !empty($this->country_code)) {
					$countryCode = $this->country_code;
				}
				
				return phoneFormatInt($value, $countryCode);
			},
		);
	}
	
	protected function name(): Attribute
	{
		return Attribute::make(
			get: fn ($value) => mb_ucwords($value),
		);
	}
	
	protected function photo(): Attribute
	{
		return Attribute::make(
			set: function ($value, $attributes) {
				if (!is_string($value)) {
					return $value;
				}
				
				if ($value == url('/')) {
					return null;
				}
				
				// Retrieve current value without upload a new file
				if (str_starts_with($value, config('larapen.core.picture.default'))) {
					return null;
				}
				
				if (!str_starts_with($value, 'avatars/')) {
					if (empty($attributes['id']) || empty($attributes['country_code'])) {
						return null;
					}
					$destPath = 'avatars/' . strtolower($attributes['country_code']) . '/' . $attributes['id'];
					$value = $destPath . last(explode($destPath, $value));
				}
				
				return $value;
			},
		);
	}
	
	protected function pIsOnline(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				$timeAgoFromNow = Carbon::now(Date::getAppTimeZone())->subMinutes(5);
				$isOnline = (
					!empty($this->getRawOriginal('last_activity'))
					&& $this->last_activity->gt($timeAgoFromNow)
				);
				
				// Allow only logged users to get the other users status
				$guard = isFromApi() ? 'sanctum' : null;
				return auth($guard)->check() ? $isOnline : false;
			},
		);
	}
	
	/*
	|--------------------------------------------------------------------------
	| OTHER PRIVATE METHODS
	|--------------------------------------------------------------------------
	*/
}
