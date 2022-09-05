<?php
/**
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 *
 * LICENSE
 * -------
 * This software is furnished under a license and may be used and copied
 * only in accordance with the terms of such license and with the inclusion
 * of the above copyright notice. If you Purchased from CodeCanyon,
 * Please read the full License from here - http://codecanyon.net/licenses/standard
 */

namespace App\Http\Requests\Admin;

use App\Models\User;

class UserRequest extends Request
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		$rules = [
			'name'         => ['required', 'min:2', 'max:100'],
			'country_code' => ['sometimes', 'required', 'not_in:0'],
			'email'        => ['required', 'unique:' . config('permission.table_names.users', 'users') . ',email'],
			// 'password'   => ['required'],
		];
		
		if (is_numeric(request()->segment(3))) {
			$user = User::find(request()->segment(3));
			if (!empty($user)) {
				if ($user->email == $this->email) {
					$rules['email'] = ['required'];
				}
			}
		}
		
		$rules = $this->validEmailRules('email', $rules);
		
		return $this->validPasswordRules('password', $rules);
	}
}
