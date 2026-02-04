<?php

namespace Database\Factories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition()
    {
        return [
            'userid' => $this->faker->unique()->userName,
            'password' => 'password_hash_placeholder',
            'user_name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'status' => 'done',
            'group_seq' => 1, 
            'cellphone' => $this->faker->phoneNumber,
            'regist_date' => now(),
            'provider_YN' => 'N',
            'cash' => 0,
            'emoney' => 0
        ];
    }
}
