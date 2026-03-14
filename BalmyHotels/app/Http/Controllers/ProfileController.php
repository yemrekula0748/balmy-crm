<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('profile.index', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'phone'  => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];

        if ($request->filled('password')) {
            $rules['current_password'] = 'required|current_password';
            $rules['password']         = ['required', 'confirmed', Password::min(8)];
        }

        $request->validate($rules, [
            'current_password.required'      => 'Mevcut şifrenizi girmelisiniz.',
            'current_password.current_password' => 'Mevcut şifre hatalı.',
            'password.required'              => 'Yeni şifre zorunludur.',
            'password.confirmed'             => 'Yeni şifreler eşleşmiyor.',
            'password.min'                   => 'Şifre en az 8 karakter olmalıdır.',
            'avatar.image'                   => 'Geçerli bir resim dosyası seçmelisiniz.',
            'avatar.mimes'                   => 'Sadece JPG, JPEG, PNG veya WEBP yükleyebilirsiniz.',
            'avatar.max'                     => 'Resim boyutu 2 MB\'ı geçemez.',
        ]);

        $data = ['phone' => $request->phone];

        if ($request->hasFile('avatar')) {
            // Eski avatarı sil (public/uploads/avatars/)
            if ($user->avatar) {
                $oldPath = public_path($user->avatar);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            // Doğrudan public/ altına kaydet — symlink gerekmez
            $dir      = public_path('uploads/avatars');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $filename = uniqid('av_', true) . '.' . $request->file('avatar')->getClientOriginalExtension();
            $request->file('avatar')->move($dir, $filename);
            @chmod($dir . '/' . $filename, 0644);
            $data['avatar'] = 'uploads/avatars/' . $filename;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'Profiliniz başarıyla güncellendi.');
    }
}
