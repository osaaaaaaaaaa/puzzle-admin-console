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
        $this->call(UserItemTableSeeder::class);
        $this->call(MailTableSeeder::class);
        $this->call(AttachedItemTableSeeder::class);
        $this->call(UserMailTableSeeder::class);
        $this->call(FollowingUserTableSeeder::class);
        $this->call(FollowLogsTableSeeder::class);
        $this->call(ItemLogsTableSeeder::class);
        $this->call(MailLogsTableSeeder::class);
        $this->call(LevelTableSeeder::class);
        $this->call(AchievementTableSeeder::class);
        $this->call(UserAchievementTableSeeder::class);
        $this->call(DistressSignalTableSeeder::class);
        $this->call(GuestTableSeeder::class);
        $this->call(ReplayTableSeeder::class);
    }
}
