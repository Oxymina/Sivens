<?php
// database/seeders/RoleSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Use DB Facade
use App\Models\Role; // If you use the Role model for seeding

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Using DB facade for simplicity, or you can use the Role model
        if (DB::table('roles')->count() == 0) { // Check if table is empty to avoid duplicates
            DB::table('roles')->insert([
                ['name' => 'reader', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'writer', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }
}