<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('admins')
            ->select('id', 'ic_no')
            ->orderBy('id')
            ->chunkById(100, function ($admins): void {
                foreach ($admins as $admin) {
                    DB::table('admins')->where('id', $admin->id)->update([
                        'password' => Hash::make((string) $admin->ic_no),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Previous password hashes cannot be restored safely.
    }
};
