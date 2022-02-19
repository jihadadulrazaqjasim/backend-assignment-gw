<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'salary',
        'age',
        'gender',
        'hired_date',
        'job_title',
        'manager_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public static function isFounder($id)
    {
        return User::find($id)->user_type == 'founder';
    }

    public static function isNormalEmployee($id)
    {
        return User::find($id)->user_type == NULL;
    }

    public function managerId($id)
    {
        return  User::find($id)->manager_id;
    }
    public static function getChain($id): string
    {
        $chain = '';

        if (self::isFounder($id)) {
            return User::find($id)->name;
        } else {
            $id = User::find($id)->id;

            // $id = $user->managerId($id);

            while ($id != 0) {
                Log::info($id);
                $user = User::find($id);
                $user_name = $user->name;
                $chain = $chain . $user_name . '-';

                if ($id == 0) {
                    return $chain;
                }

                $id = $user->managerId($id);
            }

            return $chain;
        }
    }

    public static function getManagersSalary($id): array
    {
        if (self::isFounder($id)) {
            Log::info($id);
            $manager = User::find($id);

            return [
                'name' => $manager->name,
                'salary' => $manager->salary
            ];
        }

        $managers = [
            'name' => [],
            'salary' => []
        ];

        $id = $user = User::find($id)->id;

        while ($id != 0) {


            $user = User::find($id);
            $user_name = $user->name;


            $user_salary = $user->salary;
            Log::info(json_encode($managers['name']));

            array_push($managers['name'], $user_name);
            array_push($managers['salary'], $user_salary);


            if ($id == 0) {
                return [
                    'name' => $managers['name'],
                    'salary' => $managers['salary']
                ];
            }
            
            // $managers['name'] = $user_name;
            // $managers['salary'] = $user_salary;


            $id = $user->managerId($id);
        }
        return $managers;
    }
}
