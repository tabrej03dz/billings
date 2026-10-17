<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Doctor;
use App\Models\HospitalBed;
use App\Models\HospitalDepartment;
use App\Models\HospitalRoom;
use App\Models\HospitalWard;
use App\Models\PatientProfile;
use App\Models\PatientVisit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HospitalManagementController extends Controller
{
    /**
     * Get current/active business ID for API.
     *
     * Priority:
     * 1. X-Business-Id Header
     * 2. business_id request parameter
     * 3. user's current_business_id
     * 4. user's first business
     */
    private function businessId(Request $request): int
    {
        $user = $request->user();

        abort_unless($user, 401, 'Unauthenticated.');

        $businessId =
            $request->header('X-Business-Id')
            ?? $request->input('business_id')
            ?? $user->current_business_id
            ?? $user->businesses()->pluck('businesses.id')->first();

        abort_unless(
            $businessId && is_numeric($businessId),
            403,
            'Active business select nahi hai.'
        );

        $businessId = (int) $businessId;

        $allowed = $user->businesses()
            ->where('businesses.id', $businessId)
            ->exists();

        /*
         * Same access logic as your existing web controller.
         */
        abort_unless(
            $allowed || $user->hasAnyRole(['super_admin', 'admin', 'owner']),
            403,
            'Business access denied.'
        );

        return $businessId;
    }

    /**
     * Business scoped query helper.
     */
    private function scoped(string $model, int $businessId): Builder
    {
        return $model::query()
            ->where('business_id', $businessId);
    }

    /**
     * Standard success response.
     */
    private function success(
        mixed $data = null,
        string $message = 'Success',
        int $status = 200
    ) {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Standard error response.
     */
    private function error(
        string $message,
        mixed $errors = null,
        int $status = 400
    ) {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    /**
     * Convert Laravel paginator to cleaner API response.
     */
    private function pagination($paginator): array
    {
        return [
            'items' => $paginator->items(),

            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),

                'has_more_pages' => $paginator->hasMorePages(),

                'next_page_url' => $paginator->nextPageUrl(),
                'previous_page_url' => $paginator->previousPageUrl(),
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    public function dashboard(Request $request)
    {
        $businessId = $this->businessId($request);

        $stats = [
            'patients' => PatientProfile::where(
                'business_id',
                $businessId
            )->count(),

            'doctors' => Doctor::where('business_id', $businessId)
                ->where('is_active', true)
                ->count(),

            'departments' => HospitalDepartment::where(
                'business_id',
                $businessId
            )
                ->where('is_active', true)
                ->count(),

            'wards' => HospitalWard::where(
                'business_id',
                $businessId
            )
                ->where('is_active', true)
                ->count(),

            'rooms' => HospitalRoom::where(
                'business_id',
                $businessId
            )
                ->where('is_active', true)
                ->count(),

            'beds' => HospitalBed::where(
                'business_id',
                $businessId
            )
                ->where('is_active', true)
                ->count(),

            'available_beds' => HospitalBed::where(
                'business_id',
                $businessId
            )
                ->where('is_active', true)
                ->where('status', 'available')
                ->count(),

            'occupied_beds' => HospitalBed::where(
                'business_id',
                $businessId
            )
                ->where('is_active', true)
                ->where('status', 'occupied')
                ->count(),

            'active_visits' => PatientVisit::where(
                'business_id',
                $businessId
            )
                ->whereIn(
                    'status',
                    ['registered', 'in_consultation', 'admitted']
                )
                ->count(),

            'today_visits' => PatientVisit::where(
                'business_id',
                $businessId
            )
                ->whereDate('visit_at', today())
                ->count(),
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

            'beds as available_beds_count' => function ($query) {
                $query->where('status', 'available');
            },

            'beds as occupied_beds_count' => function ($query) {
                $query->where('status', 'occupied');
            },
        ])
            ->where('business_id', $businessId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return $this->success([
            'stats' => $stats,
            'recent_visits' => $recentVisits,
            'wards' => $wards,
        ], 'Hospital dashboard fetched successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Doctors
    |--------------------------------------------------------------------------
    */

    public function doctors(Request $request)
    {
        $businessId = $this->businessId($request);

        $doctors = Doctor::where('business_id', $businessId)
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = trim((string) $request->search);

                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%")
                            ->orWhere(
                                'specialization',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'registration_number',
                                'like',
                                "%{$search}%"
                            );
                    });
                }
            )
            ->latest('id')
            ->paginate(
                min((int) $request->input('per_page', 20), 100)
            );

        return $this->success(
            $this->pagination($doctors),
            'Doctors fetched successfully.'
        );
    }

    public function storeDoctor(Request $request)
    {
        $businessId = $this->businessId($request);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'doctor_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('doctors', 'doctor_code')
                    ->where('business_id', $businessId),
            ],

            'mobile' => [
                'nullable',
                'string',
                'max:20',
            ],

            'email' => [
                'nullable',
                'email',
                'max:150',
            ],

            'qualification' => [
                'nullable',
                'string',
                'max:150',
            ],

            'specialization' => [
                'nullable',
                'string',
                'max:150',
            ],

            'registration_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'consultation_fee' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $doctor = Doctor::create([
            ...$data,

            'business_id' => $businessId,

            'is_active' => $request->boolean(
                'is_active',
                true
            ),
        ]);

        return $this->success(
            $doctor,
            'Doctor successfully add ho gaya.',
            201
        );
    }

    public function updateDoctor(
        Request $request,
        Doctor $doctor
    ) {
        $businessId = $this->businessId($request);

        abort_unless(
            (int) $doctor->business_id === $businessId,
            404,
            'Doctor not found.'
        );

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'doctor_code' => [
                'nullable',
                'string',
                'max:50',

                Rule::unique('doctors', 'doctor_code')
                    ->where('business_id', $businessId)
                    ->ignore($doctor->id),
            ],

            'mobile' => [
                'nullable',
                'string',
                'max:20',
            ],

            'email' => [
                'nullable',
                'email',
                'max:150',
            ],

            'qualification' => [
                'nullable',
                'string',
                'max:150',
            ],

            'specialization' => [
                'nullable',
                'string',
                'max:150',
            ],

            'registration_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'consultation_fee' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $doctor->update([
            ...$data,

            'is_active' => $request->boolean(
                'is_active'
            ),
        ]);

        return $this->success(
            $doctor->fresh(),
            'Doctor update ho gaya.'
        );
    }

    public function deleteDoctor(
        Request $request,
        Doctor $doctor
    ) {
        $businessId = $this->businessId($request);

        abort_unless(
            (int) $doctor->business_id === $businessId,
            404,
            'Doctor not found.'
        );

        if ($doctor->visits()->exists()) {
            return $this->error(
                'Doctor visit records se linked hai. Delete ke badle inactive karein.',
                [
                    'doctor' => [
                        'Doctor visit records se linked hai.',
                    ],
                ],
                409
            );
        }

        $doctor->delete();

        return $this->success(
            null,
            'Doctor delete ho gaya.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Patients
    |--------------------------------------------------------------------------
    */

    public function patients(Request $request)
    {
        $businessId = $this->businessId($request);

        $patients = PatientProfile::with('client')
            ->where('business_id', $businessId)
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = trim((string) $request->search);

                    $query->where(function ($q) use ($search) {
                        $q->where(
                            'patient_code',
                            'like',
                            "%{$search}%"
                        )
                            ->orWhereHas(
                                'client',
                                function ($clientQuery) use ($search) {
                                    $clientQuery
                                        ->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'mobile',
                                            'like',
                                            "%{$search}%"
                                        );
                                }
                            );
                    });
                }
            )
            ->latest('id')
            ->paginate(
                min((int) $request->input('per_page', 20), 100)
            );

        return $this->success(
            $this->pagination($patients),
            'Patients fetched successfully.'
        );
    }

    public function storePatient(Request $request)
    {
        $businessId = $this->businessId($request);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'mobile' => [
                'nullable',
                'string',
                'max:20',
            ],

            'email' => [
                'nullable',
                'email',
                'max:150',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'state' => [
                'nullable',
                'string',
                'max:100',
            ],

            'state_code' => [
                'nullable',
                'string',
                'max:10',
            ],

            'pincode' => [
                'nullable',
                'string',
                'max:20',
            ],

            'patient_code' => [
                'nullable',
                'string',
                'max:50',

                Rule::unique(
                    'patient_profiles',
                    'patient_code'
                )->where(
                    'business_id',
                    $businessId
                ),
            ],

            'date_of_birth' => [
                'nullable',
                'date',
            ],

            'age' => [
                'nullable',
                'integer',
                'min:0',
                'max:150',
            ],

            'gender' => [
                'nullable',
                Rule::in([
                    'male',
                    'female',
                    'other',
                ]),
            ],

            'blood_group' => [
                'nullable',
                'string',
                'max:10',
            ],

            'guardian_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'guardian_relation' => [
                'nullable',
                'string',
                'max:100',
            ],

            'emergency_contact' => [
                'nullable',
                'string',
                'max:20',
            ],

            'allergies' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'medical_history' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'insurance_provider' => [
                'nullable',
                'string',
                'max:150',
            ],

            'insurance_policy_number' => [
                'nullable',
                'string',
                'max:150',
            ],

            'abha_number' => [
                'nullable',
                'string',
                'max:50',
            ],
        ]);

        $patient = DB::transaction(
            function () use ($data, $businessId) {

                $client = Client::create([
                    'business_id' => $businessId,

                    'name' => $data['name'],

                    'mobile' => $data['mobile'] ?? null,

                    'email' => $data['email'] ?? null,

                    'address' => $data['address'] ?? null,

                    'state' => $data['state'] ?? null,

                    'state_code' =>
                        $data['state_code'] ?? null,

                    'pincode' =>
                        $data['pincode'] ?? null,

                    'is_save' => true,
                ]);

                return PatientProfile::create([
                    'business_id' => $businessId,

                    'client_id' => $client->id,

                    'patient_code' =>
                        !empty($data['patient_code'])
                            ? $data['patient_code']
                            : 'PAT-' .
                                str_pad(
                                    (string) $client->id,
                                    6,
                                    '0',
                                    STR_PAD_LEFT
                                ),

                    'date_of_birth' =>
                        $data['date_of_birth'] ?? null,

                    'age' =>
                        $data['age'] ?? null,

                    'gender' =>
                        $data['gender'] ?? null,

                    'blood_group' =>
                        $data['blood_group'] ?? null,

                    'guardian_name' =>
                        $data['guardian_name'] ?? null,

                    'guardian_relation' =>
                        $data['guardian_relation'] ?? null,

                    'emergency_contact' =>
                        $data['emergency_contact'] ?? null,

                    'allergies' =>
                        $data['allergies'] ?? null,

                    'medical_history' =>
                        $data['medical_history'] ?? null,

                    'insurance_provider' =>
                        $data['insurance_provider'] ?? null,

                    'insurance_policy_number' =>
                        $data['insurance_policy_number'] ?? null,

                    'abha_number' =>
                        $data['abha_number'] ?? null,
                ]);
            }
        );

        $patient->load('client');

        return $this->success(
            $patient,
            'Patient successfully register ho gaya.',
            201
        );
    }

    public function updatePatient(
        Request $request,
        PatientProfile $patient
    ) {
        $businessId = $this->businessId($request);

        abort_unless(
            (int) $patient->business_id === $businessId,
            404,
            'Patient not found.'
        );

        $patient->load('client');

        abort_unless(
            $patient->client,
            404,
            'Patient client record not found.'
        );

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'mobile' => [
                'nullable',
                'string',
                'max:20',
            ],

            'email' => [
                'nullable',
                'email',
                'max:150',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'state' => [
                'nullable',
                'string',
                'max:100',
            ],

            'pincode' => [
                'nullable',
                'string',
                'max:20',
            ],

            'patient_code' => [
                'required',
                'string',
                'max:50',

                Rule::unique(
                    'patient_profiles',
                    'patient_code'
                )
                    ->where(
                        'business_id',
                        $businessId
                    )
                    ->ignore($patient->id),
            ],

            'date_of_birth' => [
                'nullable',
                'date',
            ],

            'age' => [
                'nullable',
                'integer',
                'min:0',
                'max:150',
            ],

            'gender' => [
                'nullable',
                Rule::in([
                    'male',
                    'female',
                    'other',
                ]),
            ],

            'blood_group' => [
                'nullable',
                'string',
                'max:10',
            ],

            'guardian_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'emergency_contact' => [
                'nullable',
                'string',
                'max:20',
            ],

            'allergies' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'medical_history' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'insurance_provider' => [
                'nullable',
                'string',
                'max:150',
            ],

            'insurance_policy_number' => [
                'nullable',
                'string',
                'max:150',
            ],

            'abha_number' => [
                'nullable',
                'string',
                'max:50',
            ],
        ]);

        DB::transaction(
            function () use ($patient, $data) {

                $patient->client->update([
                    'name' => $data['name'],

                    'mobile' =>
                        $data['mobile'] ?? null,

                    'email' =>
                        $data['email'] ?? null,

                    'address' =>
                        $data['address'] ?? null,

                    'state' =>
                        $data['state'] ?? null,

                    'pincode' =>
                        $data['pincode'] ?? null,
                ]);

                $patient->update(
                    collect($data)
                        ->except([
                            'name',
                            'mobile',
                            'email',
                            'address',
                            'state',
                            'pincode',
                        ])
                        ->all()
                );
            }
        );

        $patient->refresh()->load('client');

        return $this->success(
            $patient,
            'Patient information update ho gayi.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Departments
    |--------------------------------------------------------------------------
    */

    public function departments(Request $request)
    {
        $businessId = $this->businessId($request);

        $departments = HospitalDepartment::where(
            'business_id',
            $businessId
        )
            ->withCount('doctors')
            ->latest('id')
            ->paginate(
                min((int) $request->input('per_page', 20), 100)
            );

        return $this->success(
            $this->pagination($departments),
            'Departments fetched successfully.'
        );
    }

    public function storeDepartment(Request $request)
    {
        $businessId = $this->businessId($request);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',

                Rule::unique(
                    'hospital_departments',
                    'name'
                )->where(
                    'business_id',
                    $businessId
                ),
            ],

            'code' => [
                'nullable',
                'string',
                'max:30',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $department = HospitalDepartment::create([
            ...$data,

            'business_id' => $businessId,

            'is_active' =>
                $request->boolean(
                    'is_active',
                    true
                ),
        ]);

        return $this->success(
            $department,
            'Department add ho gaya.',
            201
        );
    }

    public function updateDepartment(
        Request $request,
        HospitalDepartment $department
    ) {
        $businessId = $this->businessId($request);

        abort_unless(
            (int) $department->business_id === $businessId,
            404,
            'Department not found.'
        );

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',

                Rule::unique(
                    'hospital_departments',
                    'name'
                )
                    ->where(
                        'business_id',
                        $businessId
                    )
                    ->ignore($department->id),
            ],

            'code' => [
                'nullable',
                'string',
                'max:30',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $department->update([
            ...$data,

            'is_active' =>
                $request->boolean('is_active'),
        ]);

        return $this->success(
            $department->fresh(),
            'Department update ho gaya.'
        );
    }

    public function deleteDepartment(
        Request $request,
        HospitalDepartment $department
    ) {
        $businessId = $this->businessId($request);

        abort_unless(
            (int) $department->business_id === $businessId,
            404,
            'Department not found.'
        );

        if ($department->visits()->exists()) {
            return $this->error(
                'Department visits se linked hai. Inactive karein.',
                [
                    'department' => [
                        'Department visits se linked hai.',
                    ],
                ],
                409
            );
        }

        $department->delete();

        return $this->success(
            null,
            'Department delete ho gaya.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Wards
    |--------------------------------------------------------------------------
    */

    public function wards(Request $request)
    {
        $businessId = $this->businessId($request);

        $wards = HospitalWard::where(
            'business_id',
            $businessId
        )
            ->withCount([
                'rooms',
                'beds',
            ])
            ->latest('id')
            ->paginate(
                min((int) $request->input('per_page', 20), 100)
            );

        return $this->success(
            $this->pagination($wards),
            'Wards fetched successfully.'
        );
    }

    public function storeWard(Request $request)
    {
        $businessId = $this->businessId($request);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',

                Rule::unique(
                    'hospital_wards',
                    'name'
                )->where(
                    'business_id',
                    $businessId
                ),
            ],

            'code' => [
                'nullable',
                'string',
                'max:30',
            ],

            'ward_type' => [
                'nullable',
                'string',
                'max:100',
            ],

            'daily_charge' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $ward = HospitalWard::create([
            ...$data,

            'business_id' => $businessId,

            'is_active' =>
                $request->boolean(
                    'is_active',
                    true
                ),
        ]);

        return $this->success(
            $ward,
            'Ward add ho gaya.',
            201
        );
    }

    public function updateWard(
        Request $request,
        HospitalWard $ward
    ) {
        $businessId = $this->businessId($request);

        abort_unless(
            (int) $ward->business_id === $businessId,
            404,
            'Ward not found.'
        );

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'code' => [
                'nullable',
                'string',
                'max:30',
            ],

            'ward_type' => [
                'nullable',
                'string',
                'max:100',
            ],

            'daily_charge' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $ward->update([
            ...$data,

            'is_active' =>
                $request->boolean('is_active'),
        ]);

        return $this->success(
            $ward->fresh(),
            'Ward update ho gaya.'
        );
    }

    public function deleteWard(
        Request $request,
        HospitalWard $ward
    ) {
        $businessId = $this->businessId($request);

        abort_unless(
            (int) $ward->business_id === $businessId,
            404,
            'Ward not found.'
        );

        if (
            $ward->rooms()->exists()
            || $ward->visits()->exists()
        ) {
            return $this->error(
                'Ward rooms/visits se linked hai.',
                [
                    'ward' => [
                        'Ward rooms/visits se linked hai.',
                    ],
                ],
                409
            );
        }

        $ward->delete();

        return $this->success(
            null,
            'Ward delete ho gaya.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Rooms
    |--------------------------------------------------------------------------
    */

    public function rooms(Request $request)
    {
        $businessId = $this->businessId($request);

        $wards = HospitalWard::where(
            'business_id',
            $businessId
        )
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $rooms = HospitalRoom::with('ward')
            ->where('business_id', $businessId)
            ->withCount('beds')
            ->latest('id')
            ->paginate(
                min((int) $request->input('per_page', 20), 100)
            );

        return $this->success([
            'rooms' => $this->pagination($rooms),

            'wards' => $wards,
        ], 'Rooms fetched successfully.');
    }

    public function storeRoom(Request $request)
    {
        $businessId = $this->businessId($request);

        $data = $request->validate([
            'ward_id' => [
                'required',

                Rule::exists(
                    'hospital_wards',
                    'id'
                )->where(
                    'business_id',
                    $businessId
                ),
            ],

            'room_number' => [
                'required',
                'string',
                'max:50',

                Rule::unique(
                    'hospital_rooms',
                    'room_number'
                )->where(
                    'business_id',
                    $businessId
                ),
            ],

            'room_type' => [
                'nullable',
                'string',
                'max:100',
            ],

            'daily_charge' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $room = HospitalRoom::create([
            ...$data,

            'business_id' => $businessId,

            'is_active' =>
                $request->boolean(
                    'is_active',
                    true
                ),
        ]);

        $room->load('ward');

        return $this->success(
            $room,
            'Room add ho gaya.',
            201
        );
    }

    public function updateRoom(
        Request $request,
        HospitalRoom $room
    ) {
        $businessId = $this->businessId($request);

        abort_unless(
            (int) $room->business_id === $businessId,
            404,
            'Room not found.'
        );

        $data = $request->validate([
            'ward_id' => [
                'required',

                Rule::exists(
                    'hospital_wards',
                    'id'
                )->where(
                    'business_id',
                    $businessId
                ),
            ],

            'room_number' => [
                'required',
                'string',
                'max:50',

                Rule::unique(
                    'hospital_rooms',
                    'room_number'
                )
                    ->where(
                        'business_id',
                        $businessId
                    )
                    ->ignore($room->id),
            ],

            'room_type' => [
                'nullable',
                'string',
                'max:100',
            ],

            'daily_charge' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $room->update([
            ...$data,

            'is_active' =>
                $request->boolean('is_active'),
        ]);

        $room->refresh()->load('ward');

        return $this->success(
            $room,
            'Room update ho gaya.'
        );
    }

    public function deleteRoom(
        Request $request,
        HospitalRoom $room
    ) {
        $businessId = $this->businessId($request);

        abort_unless(
            (int) $room->business_id === $businessId,
            404,
            'Room not found.'
        );

        if (
            $room->beds()->exists()
            || $room->visits()->exists()
        ) {
            return $this->error(
                'Room beds/visits se linked hai.',
                [
                    'room' => [
                        'Room beds/visits se linked hai.',
                    ],
                ],
                409
            );
        }

        $room->delete();

        return $this->success(
            null,
            'Room delete ho gaya.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Beds
    |--------------------------------------------------------------------------
    */

    public function beds(Request $request)
    {
        $businessId = $this->businessId($request);

        $rooms = HospitalRoom::with('ward')
            ->where('business_id', $businessId)
            ->where('is_active', true)
            ->orderBy('room_number')
            ->get();

        $beds = HospitalBed::with([
            'room.ward',
        ])
            ->where('business_id', $businessId)

            ->when(
                $request->filled('status'),
                function ($query) use ($request) {
                    $query->where(
                        'status',
                        $request->status
                    );
                }
            )

            ->latest('id')

            ->paginate(
                min((int) $request->input('per_page', 25), 100)
            );

        return $this->success([
            'beds' => $this->pagination($beds),

            'rooms' => $rooms,
        ], 'Beds fetched successfully.');
    }

    public function storeBed(Request $request)
    {
        $businessId = $this->businessId($request);

        $data = $request->validate([
            'room_id' => [
                'required',

                Rule::exists(
                    'hospital_rooms',
                    'id'
                )->where(
                    'business_id',
                    $businessId
                ),
            ],

            'bed_number' => [
                'required',
                'string',
                'max:50',
            ],

            'daily_charge' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'status' => [
                'required',

                Rule::in([
                    'available',
                    'occupied',
                    'reserved',
                    'maintenance',
                ]),
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $exists = HospitalBed::where(
            'business_id',
            $businessId
        )
            ->where(
                'room_id',
                $data['room_id']
            )
            ->where(
                'bed_number',
                $data['bed_number']
            )
            ->exists();

        if ($exists) {
            return $this->error(
                'Is room me ye bed number pehle se hai.',
                [
                    'bed_number' => [
                        'Is room me ye bed number pehle se hai.',
                    ],
                ],
                422
            );
        }

        $bed = HospitalBed::create([
            ...$data,

            'business_id' => $businessId,

            'is_active' =>
                $request->boolean(
                    'is_active',
                    true
                ),
        ]);

        $bed->load('room.ward');

        return $this->success(
            $bed,
            'Bed add ho gaya.',
            201
        );
    }

    public function updateBed(
        Request $request,
        HospitalBed $bed
    ) {
        $businessId = $this->businessId($request);

        abort_unless(
            (int) $bed->business_id === $businessId,
            404,
            'Bed not found.'
        );

        $data = $request->validate([
            'room_id' => [
                'required',

                Rule::exists(
                    'hospital_rooms',
                    'id'
                )->where(
                    'business_id',
                    $businessId
                ),
            ],

            'bed_number' => [
                'required',
                'string',
                'max:50',
            ],

            'daily_charge' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'status' => [
                'required',

                Rule::in([
                    'available',
                    'occupied',
                    'reserved',
                    'maintenance',
                ]),
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        /*
         * Prevent duplicate bed number inside same room.
         */
        $exists = HospitalBed::where(
            'business_id',
            $businessId
        )
            ->where(
                'room_id',
                $data['room_id']
            )
            ->where(
                'bed_number',
                $data['bed_number']
            )
            ->where(
                'id',
                '!=',
                $bed->id
            )
            ->exists();

        if ($exists) {
            return $this->error(
                'Is room me ye bed number pehle se hai.',
                [
                    'bed_number' => [
                        'Is room me ye bed number pehle se hai.',
                    ],
                ],
                422
            );
        }

        $bed->update([
            ...$data,

            'is_active' =>
                $request->boolean('is_active'),
        ]);

        $bed->refresh()->load('room.ward');

        return $this->success(
            $bed,
            'Bed update ho gaya.'
        );
    }

    public function deleteBed(
        Request $request,
        HospitalBed $bed
    ) {
        $businessId = $this->businessId($request);

        abort_unless(
            (int) $bed->business_id === $businessId,
            404,
            'Bed not found.'
        );

        if ($bed->visits()->exists()) {
            return $this->error(
                'Bed visit record se linked hai.',
                [
                    'bed' => [
                        'Bed visit record se linked hai.',
                    ],
                ],
                409
            );
        }

        $bed->delete();

        return $this->success(
            null,
            'Bed delete ho gaya.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Patient Visits
    |--------------------------------------------------------------------------
    */

    public function visits(Request $request)
    {
        $businessId = $this->businessId($request);

        $visits = PatientVisit::with([
            'patient',
            'doctor',
            'department',
            'ward',
            'room',
            'bed',
        ])
            ->where('business_id', $businessId)

            ->when(
                $request->filled('type'),
                function ($query) use ($request) {
                    $query->where(
                        'visit_type',
                        $request->type
                    );
                }
            )

            ->when(
                $request->filled('status'),
                function ($query) use ($request) {
                    $query->where(
                        'status',
                        $request->status
                    );
                }
            )

            ->when(
                $request->filled('search'),
                function ($query) use ($request) {

                    $search = trim(
                        (string) $request->search
                    );

                    $query->where(
                        function ($q) use ($search) {

                            $q->where(
                                'visit_number',
                                'like',
                                "%{$search}%"
                            )
                                ->orWhereHas(
                                    'patient',
                                    function ($patientQuery) use ($search) {
                                        $patientQuery->where(
                                            'patient_code',
                                            'like',
                                            "%{$search}%"
                                        );
                                    }
                                )
                                ->orWhereHas(
                                    'patient.client',
                                    function ($clientQuery) use ($search) {
                                        $clientQuery
                                            ->where(
                                                'name',
                                                'like',
                                                "%{$search}%"
                                            )
                                            ->orWhere(
                                                'mobile',
                                                'like',
                                                "%{$search}%"
                                            );
                                    }
                                );
                        }
                    );
                }
            )

            ->latest('visit_at')

            ->paginate(
                min((int) $request->input('per_page', 25), 100)
            );

        return $this->success(
            $this->pagination($visits),
            'Patient visits fetched successfully.'
        );
    }
}