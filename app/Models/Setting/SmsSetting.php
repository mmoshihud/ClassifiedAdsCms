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

namespace App\Models\Setting;

class SmsSetting
{
	public static function getValues($value, $disk)
	{
		if (empty($value)) {
			
			$value['vonage_key'] = env('VONAGE_KEY', '');
			$value['vonage_secret'] = env('VONAGE_SECRET', '');
			$value['vonage_application_id'] = env('VONAGE_APPLICATION_ID', '');
			$value['vonage_from'] = env('VONAGE_SMS_FROM', '');
			
			$value['twilio_username'] = env('TWILIO_USERNAME', '');
			$value['twilio_password'] = env('TWILIO_PASSWORD', '');
			$value['twilio_auth_token'] = env('TWILIO_AUTH_TOKEN', '');
			$value['twilio_account_sid'] = env('TWILIO_ACCOUNT_SID', '');
			$value['twilio_from'] = env('TWILIO_FROM', '');
			$value['twilio_alpha_sender'] = env('TWILIO_ALPHA_SENDER', '');
			$value['twilio_sms_service_sid'] = env('TWILIO_SMS_SERVICE_SID', '');
			$value['twilio_debug_to'] = env('TWILIO_DEBUG_TO', '');
			
		} else {
			
			if (!isset($value['vonage_key'])) {
				$value['vonage_key'] = env('VONAGE_KEY', '');
			}
			if (!isset($value['vonage_secret'])) {
				$value['vonage_secret'] = env('VONAGE_SECRET', '');
			}
			if (!isset($value['vonage_application_id'])) {
				$value['vonage_application_id'] = env('VONAGE_APPLICATION_ID', '');
			}
			if (!isset($value['vonage_from'])) {
				$value['vonage_from'] = env('VONAGE_SMS_FROM', '');
			}
			
			if (!isset($value['twilio_username'])) {
				$value['twilio_username'] = env('TWILIO_USERNAME', '');
			}
			if (!isset($value['twilio_password'])) {
				$value['twilio_password'] = env('TWILIO_PASSWORD', '');
			}
			if (!isset($value['twilio_auth_token'])) {
				$value['twilio_auth_token'] = env('TWILIO_AUTH_TOKEN', '');
			}
			if (!isset($value['twilio_account_sid'])) {
				$value['twilio_account_sid'] = env('TWILIO_ACCOUNT_SID', '');
			}
			if (!isset($value['twilio_from'])) {
				$value['twilio_from'] = env('TWILIO_FROM', '');
			}
			if (!isset($value['twilio_alpha_sender'])) {
				$value['twilio_alpha_sender'] = env('TWILIO_ALPHA_SENDER', '');
			}
			if (!isset($value['twilio_sms_service_sid'])) {
				$value['twilio_sms_service_sid'] = env('TWILIO_SMS_SERVICE_SID', '');
			}
			if (!isset($value['twilio_debug_to'])) {
				$value['twilio_debug_to'] = env('TWILIO_DEBUG_TO', '');
			}
			
		}
		
		return $value;
	}
	
	public static function setValues($value, $setting)
	{
		return $value;
	}
	
	public static function getFields($diskName)
	{
		$fields = [
			[
				'name'              => 'driver',
				'label'             => trans('admin.SMS Driver'),
				'type'              => 'select2_from_array',
				'options'           => [
					'vonage' => 'Vonage',
					'twilio' => 'Twilio',
				],
				'attributes'        => [
					'id'       => 'driver',
					'onchange' => 'getDriverFields(this)',
				],
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'validate_driver',
				'label'             => trans('admin.validate_driver_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.validate_sms_driver_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			
			[
				'name'              => 'driver_vonage_title',
				'type'              => 'custom_html',
				'value'             => trans('admin.driver_vonage_title'),
				'wrapperAttributes' => [
					'class' => 'col-md-12 vonage',
				],
			],
			[
				'name'              => 'driver_vonage_info',
				'type'              => 'custom_html',
				'value'             => trans('admin.driver_vonage_info'),
				'wrapperAttributes' => [
					'class' => 'col-md-12 vonage',
				],
			],
			[
				'name'              => 'vonage_key',
				'label'             => trans('admin.Vonage Key'),
				'type'              => 'text',
				'wrapperAttributes' => [
					'class' => 'col-md-6 vonage',
				],
			],
			[
				'name'              => 'vonage_secret',
				'label'             => trans('admin.Vonage Secret'),
				'type'              => 'text',
				'wrapperAttributes' => [
					'class' => 'col-md-6 vonage',
				],
			],
			[
				'name'              => 'vonage_application_id',
				'label'             => trans('admin.vonage_application_id'),
				'type'              => 'text',
				'wrapperAttributes' => [
					'class' => 'col-md-6 vonage',
				],
			],
			[
				'name'              => 'vonage_from',
				'label'             => trans('admin.Vonage From'),
				'type'              => 'text',
				'wrapperAttributes' => [
					'class' => 'col-md-6 vonage',
				],
			],
			
			[
				'name'              => 'driver_twilio_title',
				'type'              => 'custom_html',
				'value'             => trans('admin.driver_twilio_title'),
				'wrapperAttributes' => [
					'class' => 'col-md-12 twilio',
				],
			],
			[
				'name'              => 'driver_twilio_info',
				'type'              => 'custom_html',
				'value'             => trans('admin.driver_twilio_info'),
				'wrapperAttributes' => [
					'class' => 'col-md-12 twilio',
				],
			],
			[
				'name'              => 'twilio_username',
				'label'             => trans('admin.twilio_username_label'),
				'type'              => 'text',
				'hint'              => trans('admin.twilio_username_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 twilio',
				],
			],
			[
				'name'              => 'twilio_password',
				'label'             => trans('admin.twilio_password_label'),
				'type'              => 'text',
				'hint'              => trans('admin.twilio_password_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 twilio',
				],
			],
			[
				'name'              => 'twilio_auth_token',
				'label'             => trans('admin.twilio_auth_token_label'),
				'type'              => 'text',
				'hint'              => trans('admin.twilio_auth_token_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 twilio',
				],
			],
			[
				'name'              => 'twilio_account_sid',
				'label'             => trans('admin.twilio_account_sid_label'),
				'type'              => 'text',
				'hint'              => trans('admin.twilio_account_sid_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 twilio',
				],
			],
			[
				'name'              => 'twilio_from',
				'label'             => trans('admin.twilio_from_label'),
				'type'              => 'text',
				'hint'              => trans('admin.twilio_from_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 twilio',
				],
			],
			[
				'name'              => 'twilio_alpha_sender',
				'label'             => trans('admin.twilio_alpha_sender_label'),
				'type'              => 'text',
				'hint'              => trans('admin.twilio_alpha_sender_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 twilio',
				],
			],
			[
				'name'              => 'twilio_sms_service_sid',
				'label'             => trans('admin.twilio_sms_service_sid_label'),
				'type'              => 'text',
				'hint'              => trans('admin.twilio_sms_service_sid_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 twilio',
				],
			],
			[
				'name'              => 'twilio_debug_to',
				'label'             => trans('admin.twilio_debug_to_label'),
				'type'              => 'text',
				'hint'              => trans('admin.twilio_debug_to_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 twilio',
				],
			],
			
			[
				'name'  => 'javascript',
				'type'  => 'custom_html',
				'value' => '<script>
docReady(function() {
	let driverEl = document.querySelector("#driver");
	getDriverFields(driverEl);
});

function getDriverFields(driverEl) {
	let driverElValue = driverEl.value;
	
	hideEl(document.querySelectorAll(".vonage, .twilio"));
	
	if (driverElValue === "vonage") {
		showEl(document.querySelectorAll(".vonage"));
	}
	if (driverElValue === "twilio") {
		showEl(document.querySelectorAll(".twilio"));
	}
}
</script>',
			],
			
			[
				'name'  => 'sms_other',
				'type'  => 'custom_html',
				'value' => trans('admin.sms_other_sep_value'),
			],
			[
				'name'  => 'phone_verification',
				'label' => trans('admin.Enable Phone Verification'),
				'type'  => 'checkbox_switch',
				'hint'  => trans('admin.disable_phone_in_env_hint'),
			],
			[
				'name'  => 'message_activation',
				'label' => trans('admin.Enable SMS Message'),
				'type'  => 'checkbox_switch',
				'hint'  => trans('admin.enable_sms_message_hint'),
			],
		];
		
		return $fields;
	}
}
