<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:255'],
            'id_number' => ['nullable', 'string', 'max:255'], // Adjust as needed
            'department' => ['required', 'string', 'in:Electronics Engineering,Computer Engineering'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                'ends_with:@jru.edu,@my.jru.edu' // Email domain validation
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'confirmed'
            ],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['first_name'] . ' ' . ($data['middle_name'] ? $data['middle_name'] . ' ' : '') . $data['last_name'] . ($data['suffix']),
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'id_number' => $data['id_number'],
            'department' => $data['department'],
        ]);

        // Determine role ID based on email domain
        $roleId = (strpos($data['email'], '@my.jru.edu') !== false) ? 4 : 5;  // 4 = Student, 5 = Technical Adviser

        $permissionIds = [7, 8, 9];

        // Insert role in the ModelHasRole section
        DB::table('model_has_roles')->insert([
            'role_id' => $roleId,
            'model_type' => 'App\Models\User',
            'model_id' => $user->id,
        ]);

        foreach ($permissionIds as $permissionId) {
            $exists = DB::table('role_has_permissions')
                ->where('role_id', $roleId)
                ->where('permission_id', $permissionId)
                ->exists();

            if (!$exists) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }

        return $user;
    }
}