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

use App\Helpers\Arr;
use App\Helpers\UrlGen;

/**
 * @param string|null $path
 * @return string
 */
function admin_uri(?string $path = ''): string
{
	$path = str_replace(url(config('larapen.admin.route', 'admin')), '', $path);
	$path = ltrim($path, '/');
	
	if (!empty($path)) {
		$path = config('larapen.admin.route', 'admin') . '/' . $path;
	} else {
		$path = config('larapen.admin.route', 'admin');
	}
	
	return $path;
}

/**
 * @param string|null $path
 * @return string
 */
function admin_url(?string $path = ''): string
{
	return url(admin_uri($path));
}

/**
 * Default Admin translator (e.g. admin.messages.php)
 *
 * @param $string
 * @param array $params
 * @param string $file
 * @param $locale
 * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Translation\Translator|string|null
 */
function __t($string, array $params = [], string $file = 'admin.messages', $locale = null)
{
    if (is_null($locale)) {
        $locale = config('app.locale');
    }

    return trans($file . '.' . $string, $params, $locale);
}

/**
 * Checkbox Display
 *
 * @param $fieldValue
 * @return string
 */
function checkboxDisplay($fieldValue): string
{
	// fa-square-o | fa-check-square-o
	// fa-toggle-off | fa-toggle-on
    if ($fieldValue == 1) {
        return '<i class="admin-single-icon fa fa-toggle-on" aria-hidden="true"></i>';
    } else {
        return '<i class="admin-single-icon fa fa-toggle-off" aria-hidden="true"></i>';
    }
}

/**
 * Ajax Checkbox Display
 *
 * @param $id
 * @param $table
 * @param $field
 * @param null $fieldValue
 * @return string
 */
function ajaxCheckboxDisplay($id, $table, $field, $fieldValue = null): string
{
    $lineId = $field.$id;
    $lineId = str_replace('.', '', $lineId); // fix JS bug (in admin layout)
    $data = 'data-table="' . $table . '" 
			data-field="'.$field.'" 
			data-line-id="' . $lineId . '" 
			data-id="' . $id . '" 
			data-value="' . ($fieldValue ?? 0) . '"';

    // Decoration
    if (isset($fieldValue) && $fieldValue == 1) {
        $html = '<i id="' . $lineId . '" class="admin-single-icon fa fa-toggle-on" aria-hidden="true"></i>';
    } else {
        $html = '<i id="' . $lineId . '" class="admin-single-icon fa fa-toggle-off" aria-hidden="true"></i>';
    }
	
	return '<a href="" class="ajax-request" ' . $data . '>' . $html . '</a>';
}

/**
 * Advanced Ajax Checkbox Display
 *
 * @param $id
 * @param $table
 * @param $field
 * @param null $fieldValue
 * @return string
 */
function installAjaxCheckboxDisplay($id, $table, $field, $fieldValue = null): string
{
    $lineId = $field.$id;
    $lineId = str_replace('.', '', $lineId); // fix JS bug (in admin layout)
    $data = 'data-table="' . $table . '" 
			data-field="'.$field.'" 
			data-line-id="' . $lineId . '" 
			data-id="' . $id . '" 
			data-value="' . $fieldValue . '"';

    // Decoration
    if ($fieldValue == 1) {
        $html = '<i id="' . $lineId . '" class="admin-single-icon fa fa-toggle-on" aria-hidden="true"></i>';
    } else {
        $html = '<i id="' . $lineId . '" class="admin-single-icon fa fa-toggle-off" aria-hidden="true"></i>';
    }
    $html = '<a href="" class="ajax-request" ' . $data . '>' . $html . '</a>';

    // Install country's decoration
    $html .= ' &nbsp;';
    if ($fieldValue == 1) {
        $html .= '<a href="" id="install' . $id . '" class="ajax-request btn btn-xs btn-success text-white" ' . $data . '>';
		$html .= '<i class="fas fa-download"></i> ' . trans('admin.Installed');
		$html .= '</a>';
    } else {
        $html .= '<a href="" id="install' . $id . '" class="ajax-request btn btn-xs btn-light" ' . $data . '>';
		$html .= '<i class="fas fa-download"></i> ' . trans('admin.Install');
		$html .= '</a>';
    }

    return $html;
}

/**
 * Generate the Post's link from the Admin panel
 *
 * @param $post
 * @return string
 */
function getPostUrl($post): string
{
    $out = '';
    
    if (isset($post->latestPayment) && !empty($post->latestPayment)) {
        if (isset($post->latestPayment->package) && !empty($post->latestPayment->package)) {
			$info = '';
            if ($post->featured == 1) {
                $class = 'text-success';
            } else {
                $class = 'text-danger';
				$info = ' (' . trans('admin.Expired') . ')';
            }
            $out = ' <i class="fa fa-check-circle ' . $class . '"
                    data-bs-placement="bottom" data-bs-toggle="tooltip"
                    type="button" title="' . $post->latestPayment->package->short_name . $info . '">
                </i>';
        }
    }
	
    // Get URL
	if (!is_null($post) && isset($post->country_code, $post->title)) {
		$url = dmUrl($post->country_code, UrlGen::postPath($post));
		$out = linkStrLimit($url, $post->title, 35, 'target="_blank"') . $out;
	} else {
		$out = '--';
	}
	
    return $out;
}

/**
 * @param $entry
 * @param bool $withLink
 * @return string
 */
function getCountryFlag($entry, bool $withLink = false): string
{
	$out = '';
	
	if (isset($entry->country_code)) {
		$countryName = (isset($entry->country) && isset($entry->country->name)) ? $entry->country->name : null;
		$countryName = (!empty($countryName)) ? $countryName : $entry->country_code;
		
		$iconPath = 'images/flags/16/' . strtolower($entry->country_code) . '.png';
		if (file_exists(public_path($iconPath))) {
			$out = ($withLink) ? '<a href="' . dmUrl($entry->country_code, '/', true, true) . '" target="_blank">' : '';
			$out .= '<img src="' . url($iconPath) . getPictureVersion() . '" data-bs-toggle="tooltip" title="' . $countryName . '">';
			$out .= ($withLink) ? '</a>' : '';
			$out .= ' ';
		} else {
			$out .= $entry->country_code . ' ';
		}
	}
	
	return $out;
}

/**
 * Check if the Listing is verified
 *
 * @param $post
 * @return bool
 */
function isVerifiedPost($post): bool
{
	$post = (is_array($post)) ? Arr::toObject($post) : $post;
	
    if (!isset($post->verified_email) || !isset($post->verified_phone) || !isset($post->reviewed)) {
        return false;
    }
    
    if (config('settings.single.listings_review_activation')) {
        $verified = ($post->verified_email == 1 && $post->verified_phone == 1 && $post->reviewed == 1);
    } else {
        $verified = ($post->verified_email == 1 && $post->verified_phone == 1);
    }
    
    return $verified;
}

/**
 * Check if the User is verified
 *
 * @param $user
 * @return bool
 */
function isVerifiedUser($user): bool
{
	$user = (is_array($user)) ? Arr::toObject($user) : $user;
	
    if (!isset($user->verified_email) || !isset($user->verified_phone)) {
        return false;
    }
    
    return ($user->verified_email == 1 && $user->verified_phone == 1);
}

/**
 * @return bool
 */
function userHasSuperAdminPermissions(): bool
{
	if (auth()->check()) {
		$permissions = \App\Models\Permission::getSuperAdminPermissions();
		
		// Remove the standard admin permission
		$permissions = collect($permissions)->reject(function ($value, $key) {
			return $value == 'dashboard-access';
		})->toArray();
		
		// Check if user has the super admin permissions
		if (auth()->user()->can($permissions)) {
			return true;
		}
	}
	
	return false;
}
