<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Classes\Traits\ApiRespondable;

class DeviceStartRequest extends Request
{

    use ApiRespondable;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "software"   =>  "required|string|min:1",
            "input"  =>  "required|array",
            "requested_by" => "required"
        ];
    }

    public function response(array $errors) {
        return $this->errorWrongArgs($errors);
    }
}
