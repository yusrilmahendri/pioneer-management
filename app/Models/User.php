<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    
    protected $table = 'users'; // This tells Laravel which table to use
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'phone',
        'birth_of_date',
        'birth_of_place',
        'gender',
        'start_date',
        'end_date',
        'placement',
        'job_role',
        'account_role',
        'salary',
        'uuid',
    ];

    /**
     * Get the businesses that the user belongs to (many-to-many)
     */
    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_account', 'id_user', 'id_business');
    }

    /**
     * Get the user's primary business (first business they're assigned to)
     */
    public function primaryBusiness()
    {
        return $this->belongsToMany(Business::class, 'business_account', 'id_user', 'id_business')->first();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            // Generate ID using MySQL's UUID_SHORT() function
            if (empty($model->id)) {
                $model->id = \DB::selectOne('SELECT UUID_SHORT() as id')->id;
            }
            
            // Generate UUID for the uuid field if needed
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}
