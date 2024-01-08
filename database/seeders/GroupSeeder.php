<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $group = Group::create([
                'name' => 'Group '.$i,
                'description' => fake()->sentence(),
                'thumbnail' => fake()->imageUrl(),
            ]);

            $users = User::all()->random(10);

            foreach ($users as $user) {
                $group->users()->attach($user->id);

            }
        }
    }
}
