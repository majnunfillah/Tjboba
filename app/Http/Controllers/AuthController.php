<?php

namespace App\Http\Controllers;

use App\Http\Repository\DBFLPASSRepository;
use App\Models\DBFLPASS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    private $dbflpassRepository;

    public function __construct(DBFLPASSRepository $dbflpassRepository)
    {
        $this->dbflpassRepository = $dbflpassRepository;
    }

    public function loginView()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'exists:DBFLPASS,USERID'],
            'password' => ['required_unless:username,SA'],
        ]);
        // dd(base64_encode($request->password));
        $user = DBFLPASS::where('USERID', $request->username)->first();
        if ($user) {
            if ($request->password == base64_decode($user->UID2)) {
                $user->update(['status' => 1, 'IPAddres' => $request->getClientIp(), 'HOSTID' => substr($request->getHttpHost(), 0, 20)]);
                Auth::login($user);
                return redirect()->route('dashboard');
            }
            return back()->withErrors(['password' => 'Password salah!'])->withInput();
        } else {
            return back()->withErrors(['message' => 'Akun tidak ditemukan'])->withInput();
        }
    }

    public function logout(Request $request)
    {
        auth()->user()->update(['status' => 0]);

        auth('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function gantiPassword(Request $request)
    {
        $request->validate([
            'oldUID' => ['required', 'string', 'max:8'],
            'UID2' => ['required', 'string', 'max:8'],
            'UID2_confirmation' => ['required', 'max:8', 'same:UID2'],
        ]);
        $user = DBFLPASS::where('USERID', auth()->user()->USERID)->first();
        if($request->oldUID == base64_decode($user->UID2)) {
            $user->update(['UID2' => base64_encode($request->UID2)]);
            return $this->setResponseSuccess('Password berhasil diubah.');
        } else {
            return $this->setResponseError('Password lama salah.', 501);
        }
    }
}
