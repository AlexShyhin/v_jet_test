<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Collection;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'password', 'api_token'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'api_token',
    ];

    public static function createUser(array $data) : User {

        return User::create([
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'email'         => $data['email'],
            'phone'         => $data['phone'],
//            'password'      => password_hash($data['password'], PASSWORD_BCRYPT)
            'password'      => Hash::make($data['password'])
        ]);
    }

    public static function autorize(array $credentials) : string|bool {
        $user = User::where('email', $credentials['email'])->first();
        $token = false;

        if (Hash::check($credentials['password'], $user->password)) {
            $token = self::generateAuthToken();
            $user->api_token = $token;
            $user->save();
        }

        return $token;

    }

    private static function generateAuthToken() : string {
        $token      = md5(uniqid(rand(), true));
        $collision  = User::where('api_token', $token)->first();
        if ($collision) {
            return self::generateAuthToken();
        }
        return $token;
    }

    public function changePassword(string $password) {
        $this->password = Hash::make($password);
        $this->api_token = null;
        return $this->save();
    }

    public function companies() : HasMany {
        return $this->hasMany(Company::class, 'user_id');
    }

    public function getCompaniesAttribute() : Collection {
        return $this->companies()->get();
    }

    public function getFullNameAttribute() : string {
        return $this->first_name . ' ' . $this->last_name;
    }
}
