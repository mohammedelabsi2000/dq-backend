<?php

namespace App\Http\Requests\UserRole;

use App\Http\Requests\DQFormRequest;
use App\Models\Halaqa;
use App\Models\User;
use App\Models\UserScope;
use Illuminate\Validation\Rule;

class AssignScopeRequest extends DQFormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('users.roles.update', 'sanctum');
    }

    public function rules(): array
    {
        return [
            'scopes'        => 'required|array',
            'scopes.*.type' => [
                'required',
                'string',
                Rule::in(['branch', 'region', 'center', 'halaqa']),
            ],
            'scopes.*.id'   => 'required|integer',
        ];
    }

    /**
     * منع تنسيب أكثر من مستخدم كمحفظ فعّال لنفس الحلقة في آن واحد.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $routeUser = $this->route('user');
            $userId = $routeUser instanceof User ? $routeUser->id : (int) $routeUser;

            foreach ((array) $this->input('scopes', []) as $index => $scope) {
                if (($scope['type'] ?? null) !== 'halaqa' || empty($scope['id'])) {
                    continue;
                }

                $halaqaId = (int) $scope['id'];

                $existingTeacherScope = UserScope::where('scope_type', 'halaqa')
                    ->where('scope_id', $halaqaId)
                    ->where('user_id', '!=', $userId)
                    ->active()
                    ->first();

                if (!$existingTeacherScope) {
                    continue;
                }

                $halaqa = Halaqa::find($halaqaId);

                if ($halaqa) {
                    $location = $halaqa->locationLabel();
                    $message = "هذه الحلقة \"{$halaqa->name}\" يوجد لها محفظ بالفعل" . ($location ? " ({$location})" : '') . '.';

                    $validator->errors()->add("scopes.$index.id", $message);
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'scopes.required'        => 'النطاقات مطلوبة',
            'scopes.array'           => 'النطاقات يجب أن تكون مصفوفة',
            'scopes.*.type.required' => 'نوع النطاق مطلوب',
            'scopes.*.type.in'       => 'نوع النطاق غير صالح',
            'scopes.*.id.required'   => 'معرف النطاق مطلوب',
            'scopes.*.id.integer'    => 'معرف النطاق يجب أن يكون رقماً',
        ];
    }
}
