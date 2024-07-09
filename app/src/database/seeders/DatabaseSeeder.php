<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // マスターデータ(初期データ)挿入
        $this->call(AccountsTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(ItemTableSeeder::class);
        $this->call(InventoryItemTableSeeder::class);
        $this->call(MailTableSeeder::class);
        $this->call(AttachedItemTableSeeder::class);
        $this->call(ReceivedMailTableSeeder::class);
        $this->call(FollowingUserTableSeeder::class);
    }
}
