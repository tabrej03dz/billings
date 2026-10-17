<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Doctor;
use App\Models\HospitalBed;
use App\Models\HospitalDepartment;
use App\Models\HospitalRoom;
use App\Models\HospitalWard;
use App\Models\PatientProfile;
use App\Models\PatientVisit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HospitalManagementController extends Controller
{
    private function businessId(Request $request): int
    {
        $user = $request->user();

        $businessId = $user->current_business_id
            ?? session('active_business_id')
            ?? $user->businesses()->pluck('businesses.id')->first();

        abort_unless($businessId, 403, 'Active business select nahi hai.');

        $allowed = $user->businesses()
            ->where('businesses.id', $businessId)
            ->exists();

        abort_unless(
            $allowed || $user->hasAnyRole(['super_admin', 'admin', 'owner']),
            403,
            'Business access denied.'
        );

        return (int) $businessId;
    }

    private function scoped(string $model, int $businessId): Builder
    {
        return $model::query()->where('business_id', $businessId);
    }

    public function dashboard(Request $request)
    {
        $businessId = $this->businessId($request);

        $stats = [
            'patients' => PatientProfile::where('business_id', $businessId)->count(),
            'doctors' => Doctor::where('business_id', $businessId)->where('is_active', true)->count(),
            'departments' => HospitalDepartment::where('business_id', $businessId)->where('is_active', true)->count(),
            'wards' => HospitalWard::where('business_id', $businessId)->where('is_active', true)->count(),
            'rooms' => HospitalRoom::where('business_id', $businessId)->where('is_active', true)->count(),
            'beds' => HospitalBed::where('business_id', $businessId)->where('is_active', true)->count(),
            'available_beds' => HospitalBed::where('business_id', $businessId)
                ->where('is_active', true)->where('status', 'available')->count(),
            'occupied_beds' => HospitalBed::where('business_id', $businessId)
                ->where('is_active', true)->where('status', 'occupied')->count(),
            'active_visits' => PatientVisit::where('business_id', $businessId)
                ->whereIn('status', ['registered', 'in_consultation', 'admitted'])->count(),
            'today_visits' => PatientVisit::where('business_id', $businessId)
                ->whereDate('visit_at', today())->count(),
        ];

        $recentVisits = PatientVisit::with([
                'patient',
                'doctor',
                'department',
                'ward',
                'room',
                'bed',
            ])
            ->where('business_id', $businessId)
            ->latest('visit_at')
            ->limit(8)
            ->get();

        $wards = HospitalWard::withCount([
                'beds',
                'beds as available_beds_count' => fn ($q) => $q->where('status', 'available'),
                'beds as occupied_beds_count' => fn ($q) => $q->where('status', 'occupied'),
            ])
            ->where('business_id', $businessId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('hospital.dashboard', compact('stats', 'recentVisits', 'wards'));
    }

    public function doctors(Request $request)
    {
        $businessId = $this->businessId($request);

        $doctors = Doctor::where('business_id', $businessId)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('specialization', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('hospital.doctors', compact('doctors'));
    }

    public function storeDoctor(Request $request)
    {
        $businessId = $this->businessId($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'doctor_code' => ['nullable', 'string', 'max:50',
                Rule::unique('doctors', 'doctor_code')->where('business_id', $businessId)],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'qualification' => ['nullable', 'string', 'max:150'],
            'specialization' => ['nullable', 'string', 'max:150'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Doctor::create([
            ...$data,
            'business_id' => $businessId,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Doctor successfully add ho gaya.');
    }

    public function updateDoctor(Request $request, Doctor $doctor)
    {
        $businessId = $this->businessId($request);
        abort_unless((int) $doctor->business_id === $businessId, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'doctor_code' => ['nullable', 'string', 'max:50',
                Rule::unique('doctors', 'doctor_code')
                    ->where('business_id', $businessId)->ignore($doctor->id)],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'qualification' => ['nullable', 'string', 'max:150'],
            'specialization' => ['nullable', 'string', 'max:150'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $doctor->update([
            ...$data,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Doctor update ho gaya.');
    }

    public function deleteDoctor(Request $request, Doctor $doctor)
    {
        $businessId = $this->businessId($request);
        abort_unless((int) $doctor->business_id === $businessId, 404);

        if ($doctor->visits()->exists()) {
            return back()->withErrors([
                'doctor' => 'Doctor visit records se linked hai. Delete ke badle inactive karein.',
            ]);
        }

        $doctor->delete();

        return back()->with('success', 'Doctor delete ho gaya.');
    }

    public function patients(Request $request)
    {
        $businessId = $this->businessId($request);

        $patients = PatientProfile::with('client')
            ->where('business_id', $businessId)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('patient_code', 'like', "%{$search}%")
                        ->orWhereHas('client', function ($clientQuery) use ($search) {
                            $clientQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('hospital.patients', compact('patients'));
    }

    public function storePatient(Request $request)
    {
        $businessId = $this->businessId($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:1000'],
            'state' => ['nullable', 'string', 'max:100'],
            'state_code' => ['nullable', 'string', 'max:10'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'patient_code' => ['nullable', 'string', 'max:50',
                Rule::unique('patient_profiles', 'patient_code')->where('business_id', $businessId)],
            'date_of_birth' => ['nullable', 'date'],
            'age' => ['nullable', 'integer', 'min:0', 'max:150'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_relation' => ['nullable', 'string', 'max:100'],
            'emergency_contact' => ['nullable', 'string', 'max:20'],
            'allergies' => ['nullable', 'string', 'max:2000'],
            'medical_history' => ['nullable', 'string', 'max:5000'],
            'insurance_provider' => ['nullable', 'string', 'max:150'],
            'insurance_policy_number' => ['nullable', 'string', 'max:150'],
            'abha_number' => ['nullable', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($data, $businessId) {
            $client = Client::create([
                'business_id' => $businessId,
                'name' => $data['name'],
                'mobile' => $data['mobile'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'state' => $data['state'] ?? null,
                'state_code' => $data['state_code'] ?? null,
                'pincode' => $data['pincode'] ?? null,
                'is_save' => true,
            ]);

            PatientProfile::create([
                'business_id' => $businessId,
                'client_id' => $client->id,
                'patient_code' => $data['patient_code']
                    ?: 'PAT-' . str_pad((string) $client->id, 6, '0', STR_PAD_LEFT),
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'age' => $data['age'] ?? null,
                'gender' => $data['gender'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'guardian_name' => $data['guardian_name'] ?? null,
                'guardian_relation' => $data['guardian_relation'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
                'allergies' => $data['allergies'] ?? null,
                'medical_history' => $data['medical_history'] ?? null,
                'insurance_provider' => $data['insurance_provider'] ?? null,
                'insurance_policy_number' => $data['insurance_policy_number'] ?? null,
                'abha_number' => $data['abha_number'] ?? null,
            ]);
        });

        return back()->with('success', 'Patient successfully register ho gaya.');
    }

    public function updatePatient(Request $request, PatientProfile $patient)
    {
        $businessId = $this->businessId($request);
        abort_unless((int) $patient->business_id === $businessId, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:1000'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'patient_code' => ['required', 'string', 'max:50',
                Rule::unique('patient_profiles', 'patient_code')
                    ->where('business_id', $businessId)->ignore($patient->id)],
            'date_of_birth' => ['nullable', 'date'],
            'age' => ['nullable', 'integer', 'min:0', 'max:150'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact' => ['nullable', 'string', 'max:20'],
            'allergies' => ['nullable', 'string', 'max:2000'],
            'medical_history' => ['nullable', 'string', 'max:5000'],
            'insurance_provider' => ['nullable', 'string', 'max:150'],
            'insurance_policy_number' => ['nullable', 'string', 'max:150'],
            'abha_number' => ['nullable', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($patient, $data) {
            $patient->client->update([
                'name' => $data['name'],
                'mobile' => $data['mobile'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'state' => $data['state'] ?? null,
                'pincode' => $data['pincode'] ?? null,
            ]);

            $patient->update(collect($data)->except([
                'name', 'mobile', 'email', 'address', 'state', 'pincode',
            ])->all());
        });

        return back()->with('success', 'Patient information update ho gayi.');
    }

    public function departments(Request $request)
    {
        $businessId = $this->businessId($request);
        $departments = HospitalDepartment::where('business_id', $businessId)
            ->withCount('doctors')->latest('id')->paginate(20);

        return view('hospital.departments', compact('departments'));
    }

    public function storeDepartment(Request $request)
    {
        $businessId = $this->businessId($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150',
                Rule::unique('hospital_departments', 'name')->where('business_id', $businessId)],
            'code' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        HospitalDepartment::create([
            ...$data, 'business_id' => $businessId,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Department add ho gaya.');
    }

    public function updateDepartment(Request $request, HospitalDepartment $department)
    {
        $businessId = $this->businessId($request);
        abort_unless((int) $department->business_id === $businessId, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150',
                Rule::unique('hospital_departments', 'name')
                    ->where('business_id', $businessId)->ignore($department->id)],
            'code' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $department->update([...$data, 'is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'Department update ho gaya.');
    }

    public function deleteDepartment(Request $request, HospitalDepartment $department)
    {
        $businessId = $this->businessId($request);
        abort_unless((int) $department->business_id === $businessId, 404);

        if ($department->visits()->exists()) {
            return back()->withErrors(['department' => 'Department visits se linked hai. Inactive karein.']);
        }

        $department->delete();
        return back()->with('success', 'Department delete ho gaya.');
    }

    public function wards(Request $request)
    {
        $businessId = $this->businessId($request);
        $wards = HospitalWard::where('business_id', $businessId)
            ->withCount(['rooms', 'beds'])->latest('id')->paginate(20);

        return view('hospital.wards', compact('wards'));
    }

    public function storeWard(Request $request)
    {
        $businessId = $this->businessId($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150',
                Rule::unique('hospital_wards', 'name')->where('business_id', $businessId)],
            'code' => ['nullable', 'string', 'max:30'],
            'ward_type' => ['nullable', 'string', 'max:100'],
            'daily_charge' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        HospitalWard::create([
            ...$data, 'business_id' => $businessId,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Ward add ho gaya.');
    }

    public function updateWard(Request $request, HospitalWard $ward)
    {
        $businessId = $this->businessId($request);
        abort_unless((int) $ward->business_id === $businessId, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:30'],
            'ward_type' => ['nullable', 'string', 'max:100'],
            'daily_charge' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $ward->update([...$data, 'is_active' => $request->boolean('is_active')]);
        return back()->with('success', 'Ward update ho gaya.');
    }

    public function deleteWard(Request $request, HospitalWard $ward)
    {
        $businessId = $this->businessId($request);
        abort_unless((int) $ward->business_id === $businessId, 404);

        if ($ward->rooms()->exists() || $ward->visits()->exists()) {
            return back()->withErrors(['ward' => 'Ward rooms/visits se linked hai.']);
        }

        $ward->delete();
        return back()->with('success', 'Ward delete ho gaya.');
    }

    public function rooms(Request $request)
    {
        $businessId = $this->businessId($request);
        $wards = HospitalWard::where('business_id', $businessId)
            ->where('is_active', true)->orderBy('name')->get();

        $rooms = HospitalRoom::with('ward')->where('business_id', $businessId)
            ->withCount('beds')->latest('id')->paginate(20);

        return view('hospital.rooms', compact('rooms', 'wards'));
    }

    public function storeRoom(Request $request)
    {
        $businessId = $this->businessId($request);
        $data = $request->validate([
            'ward_id' => ['required', Rule::exists('hospital_wards', 'id')->where('business_id', $businessId)],
            'room_number' => ['required', 'string', 'max:50',
                Rule::unique('hospital_rooms', 'room_number')->where('business_id', $businessId)],
            'room_type' => ['nullable', 'string', 'max:100'],
            'daily_charge' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        HospitalRoom::create([
            ...$data, 'business_id' => $businessId,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Room add ho gaya.');
    }

    public function updateRoom(Request $request, HospitalRoom $room)
    {
        $businessId = $this->businessId($request);
        abort_unless((int) $room->business_id === $businessId, 404);

        $data = $request->validate([
            'ward_id' => ['required', Rule::exists('hospital_wards', 'id')->where('business_id', $businessId)],
            'room_number' => ['required', 'string', 'max:50',
                Rule::unique('hospital_rooms', 'room_number')
                    ->where('business_id', $businessId)->ignore($room->id)],
            'room_type' => ['nullable', 'string', 'max:100'],
            'daily_charge' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $room->update([...$data, 'is_active' => $request->boolean('is_active')]);
        return back()->with('success', 'Room update ho gaya.');
    }

    public function deleteRoom(Request $request, HospitalRoom $room)
    {
        $businessId = $this->businessId($request);
        abort_unless((int) $room->business_id === $businessId, 404);

        if ($room->beds()->exists() || $room->visits()->exists()) {
            return back()->withErrors(['room' => 'Room beds/visits se linked hai.']);
        }

        $room->delete();
        return back()->with('success', 'Room delete ho gaya.');
    }

    public function beds(Request $request)
    {
        $businessId = $this->businessId($request);
        $rooms = HospitalRoom::with('ward')->where('business_id', $businessId)
            ->where('is_active', true)->orderBy('room_number')->get();

        $beds = HospitalBed::with(['room.ward'])->where('business_id', $businessId)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')->paginate(25)->withQueryString();

        return view('hospital.beds', compact('beds', 'rooms'));
    }

    public function storeBed(Request $request)
    {
        $businessId = $this->businessId($request);
        $data = $request->validate([
            'room_id' => ['required', Rule::exists('hospital_rooms', 'id')->where('business_id', $businessId)],
            'bed_number' => ['required', 'string', 'max:50'],
            'daily_charge' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['available', 'occupied', 'reserved', 'maintenance'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $exists = HospitalBed::where('business_id', $businessId)
            ->where('room_id', $data['room_id'])
            ->where('bed_number', $data['bed_number'])->exists();

        if ($exists) {
            return back()->withErrors(['bed_number' => 'Is room me ye bed number pehle se hai.'])->withInput();
        }

        HospitalBed::create([
            ...$data, 'business_id' => $businessId,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Bed add ho gaya.');
    }

    public function updateBed(Request $request, HospitalBed $bed)
    {
        $businessId = $this->businessId($request);
        abort_unless((int) $bed->business_id === $businessId, 404);

        $data = $request->validate([
            'room_id' => ['required', Rule::exists('hospital_rooms', 'id')->where('business_id', $businessId)],
            'bed_number' => ['required', 'string', 'max:50'],
            'daily_charge' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['available', 'occupied', 'reserved', 'maintenance'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $bed->update([...$data, 'is_active' => $request->boolean('is_active')]);
        return back()->with('success', 'Bed update ho gaya.');
    }

    public function deleteBed(Request $request, HospitalBed $bed)
    {
        $businessId = $this->businessId($request);
        abort_unless((int) $bed->business_id === $businessId, 404);

        if ($bed->visits()->exists()) {
            return back()->withErrors(['bed' => 'Bed visit record se linked hai.']);
        }

        $bed->delete();
        return back()->with('success', 'Bed delete ho gaya.');
    }

    public function visits(Request $request)
    {
        $businessId = $this->businessId($request);

        $visits = PatientVisit::with([
                'patient', 'doctor', 'department', 'ward', 'room', 'bed',
            ])
            ->where('business_id', $businessId)
            ->when($request->filled('type'), fn ($q) => $q->where('visit_type', $request->type))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('visit_number', 'like', "%{$search}%")
                        ->orWhereHas('patient', fn ($p) => $p->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('visit_at')
            ->paginate(25)
            ->withQueryString();

        return view('hospital.visits', compact('visits'));
    }

}
