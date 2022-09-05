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

namespace App\Http\Requests;

class AuthRequest extends Request
{
	/**
	 * Prepare the data for validation.
	 *
	 * @return void
	 */
	protected function prepareForValidation()
	{
		// Try to fill the 'login' field if it's not filled
		if ($this->filled('email')) {
			if (!$this->filled('login')) {
				$this->request->add(['login' => $this->input('email')]);
			}
		}
		
		// Don't apply this to the Admin Panel
		if (isAdminPanel()) {
			return;
		}
		
		$input = $this->all();
		
		// login (phone)
		if ($this->filled('login')) {
			$loginField = getLoginField($this->input('login'));
			if ($loginField == 'phone') {
				$input['login'] = phoneFormatInt($this->input('login'), $this->input('country_code', session('countryCode')));
			}
		}
		
		request()->merge($input); // Required!
		$this->merge($input);
	}
	
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		$rules = [];
		
		if ($this->has('email')) {
			$rules['email'] = ['required'];
		}
		if ($this->has('login')) {
			$rules['login'] = ['required'];
		}
		
		return $this->captchaRules($rules);
	}
}
