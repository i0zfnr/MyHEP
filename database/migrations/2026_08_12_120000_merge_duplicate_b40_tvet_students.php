<?php

use Illuminate\Database\Migrations\Migration;
use App\Support\MalaysianIdentityNormalizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PROVIDER = 'SCHOLARSHIP B40 TVET';

    public function up(): void
    {
        if (! Schema::hasTable('students') || ! Schema::hasTable('scholarships')) {
            return;
        }

        $groups = DB::table('students')
            ->whereNotNull('ic_no')
            ->where('ic_no', '!=', '')
            ->get()
            ->groupBy(fn (object $student): string => MalaysianIdentityNormalizer::ic((string) $student->ic_no))
            ->filter(fn ($students, string $identity): bool => $identity !== '' && $students->count() > 1);

        foreach ($groups as $identity => $group) {
            DB::transaction(function () use ($identity, $group): void {
                $students = $group->sortBy(fn (object $student): string => sprintf(
                    '%d-%d-%020d',
                    empty($student->matric_no) ? 1 : 0,
                    MalaysianIdentityNormalizer::ic((string) $student->ic_no) === (string) $student->ic_no ? 0 : 1,
                    $student->id
                ))->values();

                $canonical = $students->first();
                if (! $canonical) {
                    return;
                }

                DB::table('students')->where('id', $canonical->id)->update([
                    'ic_no' => $identity,
                    'updated_at' => now(),
                ]);

                foreach ($students->skip(1) as $duplicate) {
                    $b40Records = DB::table('scholarships')
                        ->where('student_id', $duplicate->id)
                        ->where('provider_name', self::PROVIDER)
                        ->orderByDesc('updated_at')
                        ->get();

                    if ($b40Records->isEmpty()) {
                        continue;
                    }

                    $canonicalScholarship = DB::table('scholarships')
                        ->where('student_id', $canonical->id)
                        ->where('provider_name', self::PROVIDER)
                        ->first();

                    if ($canonicalScholarship) {
                        DB::table('scholarships')->whereIn('id', $b40Records->pluck('id'))->delete();
                    } else {
                        $keep = $b40Records->first();
                        DB::table('scholarships')->where('id', $keep->id)->update([
                            'student_id' => $canonical->id,
                            'updated_at' => now(),
                        ]);
                        DB::table('scholarships')->whereIn('id', $b40Records->skip(1)->pluck('id'))->delete();
                    }

                    if ($this->hasNoRemainingStudentLinks((int) $duplicate->id)) {
                        DB::table('students')->where('id', $duplicate->id)->delete();
                    }
                }
            });
        }
    }

    public function down(): void
    {
        // Merged duplicate identities cannot be recreated reliably.
    }

    private function hasNoRemainingStudentLinks(int $studentId): bool
    {
        foreach (Schema::getTables() as $tableInfo) {
            $table = $tableInfo['name'] ?? null;
            if (! is_string($table) || $table === 'students' || ! Schema::hasColumn($table, 'student_id')) {
                continue;
            }

            if (DB::table($table)->where('student_id', $studentId)->exists()) {
                return false;
            }
        }

        return true;
    }

};
