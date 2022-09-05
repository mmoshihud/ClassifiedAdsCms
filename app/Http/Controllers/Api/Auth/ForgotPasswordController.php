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

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\ForgotPasswordRequest;
use App\Helpers\Auth\Traits\SendsPasswordResetEmails;
use App\Helpers\Auth\Traits\SendsPasswordResetSms;

/**
 * @group Authentication
 */
class ForgotPasswordController extends BaseController
{
    use SendsPasswordResetEmails, SendsPasswordResetSms;
    
    /**
     * Forgot password
	 *
	 * @bodyParam login string required The user's login (Can be email address or phone number). Example: user@demosite.com
	 * @bodyParam captcha_key string Key generated by the CAPTCHA endpoint calling (Required if the CAPTCHA verification is enabled from the Admin panel).
     *
	 * @param \App\Http\Requests\ForgotPasswordRequest $request
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
	 */
    public function sendResetLink(ForgotPasswordRequest $request)
    {
        // Get the right login field
        $field = getLoginField($request->input('login'));
        $request->merge([$field => $request->input('login')]);
        if ($field != 'email') {
            $request->merge(['email' => $request->input('login')]);
        }
        
        // Send the Token by SMS
        if ($field == 'phone') {
            return $this->sendResetTokenSms($request);
        }
        
        // Go to the core process
        return $this->sendResetLinkEmail($request);
    }
}
