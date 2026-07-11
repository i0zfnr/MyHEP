<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (session()->has('auth_user')) {
            $role = session('auth_user.role');

            return $role === 'admin'
                ? redirect()->route('admin.dashboard')
                : redirect()->route('student.dashboard');
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:student,admin'],
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = 'login:' . $validated['role'] . '|' . strtolower(trim($validated['username'])) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->route('login')->withErrors([
                'username' => __('login.error_throttle', ['seconds' => $seconds]),
            ])->withInput();
        }

        if ($validated['role'] === 'student') {
            $student = DB::table('students')
                ->where('matric_no', $validated['username'])
                ->first();

            $studentLoginValid = false;
            if ($student) {
                if (!empty($student->password)) {
                    $studentLoginValid = Hash::check($validated['password'], $student->password);
                } else {
                    $studentLoginValid = $student->ic_no === $validated['password'];
                }
            }

            if (!$studentLoginValid) {
                RateLimiter::hit($throttleKey, 900);
                auditLog('auth.login_failed', 'student', $student->id ?? null, 'Percubaan login pelajar gagal');
                return redirect()->route('login')->withErrors([
                    'username' => __('login.error_invalid_student'),
                ])->withInput();
            }

            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            $request->session()->put('auth_user', [
                'id' => $student->id,
                'role' => 'student',
                'name' => $student->full_name,
            ]);
            auditLog('auth.login_success', 'student', (int) $student->id, 'Login pelajar berjaya');

            return redirect()->route('student.dashboard');
        }

        $adminUsername = trim($validated['username']);
        $admin = DB::table('admins')
            ->where('ic_no', $adminUsername)
            ->orWhereRaw('LOWER(full_name) = ?', [strtolower($adminUsername)])
            ->first();

        if (!$admin || !Hash::check($validated['password'], $admin->password)) {
            RateLimiter::hit($throttleKey, 900);
            auditLog('auth.login_failed', 'admin', $admin->id ?? null, 'Percubaan login admin gagal');
            return redirect()->route('login')->withErrors([
                'username' => __('login.error_invalid_admin'),
            ])->withInput();
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();
        $request->session()->put('auth_user', [
            'id' => $admin->id,
            'role' => 'admin',
            'name' => $admin->full_name,
            'admin_role' => $admin->role,
        ]);
        auditLog('auth.login_success', 'admin', (int) $admin->id, 'Login admin berjaya');

        return redirect()->route('admin.dashboard');
    }

    public function forgotForm(): View
    {
        return view('auth.password.forgot');
    }

    public function sendResetCode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:student,admin'],
            'identifier' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
        ]);

        $account = $this->findAccount($validated['role'], trim($validated['identifier']));
        $email = strtolower(trim($validated['email']));

        if (!$account || empty($account->email) || strtolower((string) $account->email) !== $email) {
            return redirect()->route('password.forgot')
                ->withErrors(['identifier' => 'Maklumat pemulihan akaun tidak sepadan.'])
                ->withInput();
        }

        DB::table('password_reset_codes')
            ->where('role', $validated['role'])
            ->where('target_id', $account->id)
            ->whereNull('used_at')
            ->update(['used_at' => now(), 'updated_at' => now()]);

        $code = (string) random_int(100000, 999999);
        $ref = (string) Str::uuid();

        DB::table('password_reset_codes')->insert([
            'ref' => $ref,
            'role' => $validated['role'],
            'target_id' => $account->id,
            'email' => $email,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(15),
            'verified_at' => null,
            'used_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $deliveryMessage = null;
        try {
            Mail::raw(
                "Kod verifikasi reset kata laluan StudentEdge anda: {$code}. Kod ini sah selama 15 minit.",
                function ($message) use ($email) {
                    $message->to($email)->subject('Kod Verifikasi Reset Kata Laluan StudentEdge');
                }
            );
        } catch (\Throwable $e) {
            $deliveryMessage = config('app.debug')
                ? "Penghantaran email gagal pada persekitaran ini. Guna kod debug: {$code}"
                : 'Penghantaran email gagal. Sila hubungi pentadbir sistem.';
        }

        $masked = $this->maskEmail($email);

        return redirect()->route('password.verify', ['ref' => $ref])
            ->with('success', "Kod verifikasi telah dihantar ke {$masked}.")
            ->with('delivery_info', $deliveryMessage);
    }

    public function verifyForm(Request $request): View|RedirectResponse
    {
        $ref = (string) $request->query('ref');
        $reset = $this->getActiveResetByRef($ref);

        if (!$reset) {
            return redirect()->route('password.forgot')
                ->withErrors(['identifier' => 'Sesi verifikasi tidak sah atau telah tamat.']);
        }

        return view('auth.password.verify', [
            'ref' => $ref,
            'maskedEmail' => $this->maskEmail($reset->email),
        ]);
    }

    public function verifyCode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ref' => ['required', 'uuid'],
            'code' => ['required', 'digits:6'],
        ]);

        $reset = $this->getActiveResetByRef($validated['ref']);
        if (!$reset) {
            return redirect()->route('password.forgot')
                ->withErrors(['identifier' => 'Sesi verifikasi tidak sah atau telah tamat.']);
        }

        if (!Hash::check($validated['code'], $reset->code_hash)) {
            return redirect()->route('password.verify', ['ref' => $validated['ref']])
                ->withErrors(['code' => 'Kod verifikasi tidak sah.'])
                ->withInput();
        }

        DB::table('password_reset_codes')
            ->where('id', $reset->id)
            ->update([
                'verified_at' => now(),
                'updated_at' => now(),
            ]);

        return redirect()->route('password.reset', ['ref' => $validated['ref']])
            ->with('success', 'Kod verifikasi berjaya disahkan. Sila tetapkan kata laluan baharu.');
    }

    public function resetForm(Request $request): View|RedirectResponse
    {
        $ref = (string) $request->query('ref');

        $reset = DB::table('password_reset_codes')
            ->where('ref', $ref)
            ->whereNull('used_at')
            ->whereNotNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$reset) {
            return redirect()->route('password.forgot')
                ->withErrors(['identifier' => 'Sesi reset kata laluan tidak sah atau telah tamat.']);
        }

        return view('auth.password.reset', ['ref' => $ref]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ref' => ['required', 'uuid'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $reset = DB::table('password_reset_codes')
            ->where('ref', $validated['ref'])
            ->whereNull('used_at')
            ->whereNotNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$reset) {
            return redirect()->route('password.forgot')
                ->withErrors(['identifier' => 'Sesi reset kata laluan tidak sah atau telah tamat.']);
        }

        $table = $reset->role === 'admin' ? 'admins' : 'students';

        DB::table($table)
            ->where('id', $reset->target_id)
            ->update([
                'password' => Hash::make($validated['password']),
                'updated_at' => now(),
            ]);

        DB::table('password_reset_codes')
            ->where('id', $reset->id)
            ->update([
                'used_at' => now(),
                'updated_at' => now(),
            ]);

        auditLog('auth.password_reset', $reset->role, (int) $reset->target_id, 'Reset kata laluan berjaya');

        return redirect()->route('login')
            ->with('success', 'Kata laluan berjaya ditetapkan semula. Sila log masuk.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $locale = $request->session()->get('locale');
        $theme = $request->session()->get('theme');

        auditLog('auth.logout', session('auth_user.role'), session('auth_user.id'), 'Pengguna log keluar');
        $request->session()->forget('auth_user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($locale) {
            $request->session()->put('locale', $locale);
        }

        if ($theme) {
            $request->session()->put('theme', $theme);
        }

        return redirect()->route('login');
    }

    private function findAccount(string $role, string $identifier): ?object
    {
        if ($role === 'admin') {
            return DB::table('admins')
                ->select('id', 'email')
                ->where('ic_no', $identifier)
                ->first();
        }

        return DB::table('students')
            ->select('id', 'email')
            ->where('matric_no', $identifier)
            ->first();
    }

    private function getActiveResetByRef(string $ref): ?object
    {
        if ($ref === '') {
            return null;
        }

        return DB::table('password_reset_codes')
            ->where('ref', $ref)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email) + ['', ''];
        if ($local === '' || $domain === '') {
            return $email;
        }

        $localMasked = substr($local, 0, 2) . str_repeat('*', max(1, strlen($local) - 2));

        return $localMasked . '@' . $domain;
    }
}
