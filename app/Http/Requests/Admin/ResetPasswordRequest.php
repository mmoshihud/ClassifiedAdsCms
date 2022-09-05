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

class ResetPasswordRequest extends Request
{
	/**
	 * @return bool
	 */
	public function authorize(): bool
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
		$rules = [
			'email'    => ['required'],
			'password' => ['required', 'confirmed'],
		];
		
		$rules = $this->validEmailRules('email', $rules);
		$rules = $this->validPasswordRules('password', $rules);
		
		return $this->captchaRules($rules);
	}
	
	/**
	 * @return array
	 */
	public function messages(): array
	{
		return [];
	}
}
