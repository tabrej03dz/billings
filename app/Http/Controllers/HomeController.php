<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class HomeController extends Controller
{
    public function index(){
        return view('frontend.index');
    }










    public function store(Request $request)
    {
        $request->validate([
            // user step
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(6)],

            // business step
            'business_name' => ['required', 'string', 'max:255'],
            'business_email' => ['required', 'email', 'max:255', 'unique:businesses,email'],
            'mobile' => ['required', 'string', 'max:20', 'unique:businesses,mobile'],
            'gstin' => ['nullable', 'string', 'max:255', 'unique:businesses,gstin'],
            'address' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'state_code' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],

            // billing step
            'gst_enabled' => ['nullable', 'in:0,1'],
            'invoice_base_prefix' => ['nullable', 'string', 'max:255'],
            'rounding_mode' => ['nullable', 'in:none,nearest,up,down'],
            'rounding_step' => ['nullable', 'numeric', 'min:0'],

            'terms' => ['required', 'accepted'],
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $slug = $this->generateUniqueBusinessSlug($request->business_name);

            $business = Business::create([
                'name' => $request->business_name,
                'slug' => $slug,
                'email' => $request->business_email,
                'mobile' => $request->mobile,
                'gstin' => $request->gstin,
                'gst_enabled' => $request->gst_enabled ?? 1,
                'address' => $request->address,
                'state' => $request->state,
                'state_code' => $request->state_code,
                'type' => $request->type,
                'invoice_base_prefix' => $request->invoice_base_prefix ?: 'RV/SL',
                'rounding_mode' => $request->rounding_mode ?: 'nearest',
                'rounding_step' => $request->rounding_step ?: 1.00,
            ]);

            DB::table('business_user')->insert([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $user->update([
                'current_business_id' => $business->id,
            ]);


            // 6) Important permissions
            $importantPermissions = [
                'show users',
                'create user',
                'edit user',
                'delete user',

                'show businesses',
                'create business',
                'edit business',
                'delete business',

                'show clients',
                'create client',
                'edit client',
                'delete client',

                'show invoices',
                'create invoice',
                'edit invoice',
                'delete invoice',
                'download invoice',

                'show invoices menu',
                'show proformas',
                'create proforma',
                'edit proforma',
                'delete proforma',

                'show quotations',
                'create quotation',
                'edit quotation',
                'delete quotation',

                'show categories',
                'create category',
                'edit category',
                'delete category',

                'show items',
                'create item',
                'edit item',
                'delete item',

                'show additional charges',
                'create additional charge',
                'edit additional charge',
                'delete additional charge',

                'show purchases',
                'show inventory',
                'show invoice sends',
                'show bank balance',
                'show installment reminders',
            ];

            // sirf wahi permissions assign hongi jo DB me available hain
            $existingPermissions = Permission::whereIn('name', $importantPermissions)
                ->pluck('name')
                ->toArray();

            // direct permissions
            if (!empty($existingPermissions)) {
                $user->givePermissionTo($existingPermissions);
            }

            // optional: owner role assign karo agar role table me hai
            $ownerRole = Role::where('name', 'owner')->first();
            if ($ownerRole) {
                $user->assignRole($ownerRole);
            }

            DB::commit();

            event(new Registered($user));
            Auth::login($user);

            return redirect()->route('plan.choose')
                ->with('success', 'Registration successful. Your business has been created.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors([
                    'register_error' => 'Registration failed: ' . $e->getMessage()
                ]);
        }
    }

    private function generateUniqueBusinessSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base ?: 'business';
        $count = 1;

        while (Business::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $count;
            $count++;
        }

        return $slug;
    }

}
