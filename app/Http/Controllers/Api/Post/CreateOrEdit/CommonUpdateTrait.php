<?php
/*
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 *  Website: https://laraclassifier.com
 *
 * LICENSE
 * -------
 * This software is furnished under a license and may be used and copied
 * only in accordance with the terms of such license and with the inclusion
 * of the above copyright notice. If you Purchased from CodeCanyon,
 * Please read the full License from here - http://codecanyon.net/licenses/standard
 */

namespace App\Http\Controllers\Api\Post\CreateOrEdit;

use App\Helpers\Date;
use App\Http\Resources\PostResource;
use App\Models\Package;
use App\Models\Post;
use App\Notifications\PostArchived;
use App\Notifications\PostRepublished;
use Illuminate\Support\Carbon;

trait CommonUpdateTrait
{
	/**
	 * Archive a listing
	 *
	 * Put a listing offline
	 *
	 * @authenticated
	 * @header Authorization Bearer {YOUR_AUTH_TOKEN}
	 *
	 * @urlParam id int required The post/listing's ID.
	 *
	 * @param $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function offline($id)
	{
		if (!auth('sanctum')->check()) {
			return $this->respondUnAuthorized();
		}
		
		$user = auth('sanctum')->user();
		
		$post = Post::where('user_id', $user->id)->where('id', $id)->first();
		if (empty($post)) {
			return $this->respondNotFound(t('post_not_found'));
		}
		
		if ($post->archived == 1) {
			return $this->respondError(t('The listing is already offline'));
		}
		
		$post->archived = 1;
		$post->archived_at = Carbon::now(Date::getAppTimeZone());
		$post->archived_manually = 1;
		$post->save();
		
		if ($post->archived == 1) {
			$archivedPostsExpiration = config('settings.cron.manually_archived_listings_expiration', 180);
			
			// Send Confirmation Email or SMS
			if (config('settings.mail.confirmation') == 1) {
				try {
					$post->notify(new PostArchived($post, $archivedPostsExpiration));
				} catch (\Throwable $e) {
					return $this->respondError($e->getMessage());
				}
			}
			
			$message = t('offline_putting_message', [
				'postTitle' => $post->title,
				'dateDel'   => Date::format($post->archived_at->addDays($archivedPostsExpiration)),
			]);
			
			$data = [
				'success' => true,
				'message' => $message,
				'result'  => new PostResource($post),
			];
			
			return $this->apiResponse($data);
		} else {
			return $this->respondError(t('The putting offline has failed'));
		}
	}
	
	/**
	 * Repost a listing
	 *
	 * Repost a listing by un-archiving it.
	 *
	 * @authenticated
	 * @header Authorization Bearer {YOUR_AUTH_TOKEN}
	 *
	 * @urlParam id int required The post/listing's ID.
	 *
	 * @param $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function repost($id)
	{
		if (!auth('sanctum')->check()) {
			return $this->respondUnAuthorized();
		}
		
		$user = auth('sanctum')->user();
		
		$post = Post::where('user_id', $user->id)->where('id', $id)->first();
		if (empty($post)) {
			return $this->respondNotFound(t('post_not_found'));
		}
		
		if ($post->archived == 0) {
			return $this->respondError(t('The listing is already online'));
		}
		
		$today = Carbon::now(Date::getAppTimeZone());
		
		$post->archived = 0;
		$post->archived_at = null;
		$post->deletion_mail_sent_at = null;
		$post->created_at = $today;
		if ($post->archived_manually != 1) {
			$post->archived_manually = 0;
		}
		
		// If the "Allow listings to be reviewed by Admins" option is activated,
		// and the listing is not linked to a valid payment,
		// and all activated packages have price > 0, then
		// - Un-approve (un-reviewed) the listing (using the "reviewed" column)
		// - Update the "updated_at" date column  to now
		if (config('settings.single.listings_review_activation')) {
			$paymentExists = false;
			if (
				isset($post->latestPayment)
				&& isset($post->latestPayment->package)
				&& !empty($post->latestPayment->package)
			) {
				$paymentExists = (
					$today->diffInDays($post->latestPayment->created_at) >= (int)$post->latestPayment->package->duration
				);
			}
			if (!$paymentExists) {
				$packagesForFree = Package::query()->where('price', 0);
				if ($packagesForFree->count() <= 0) {
					$post->reviewed = 0;
				}
			}
		}
		
		// Save the listing
		$post->save();
		
		if ($post->archived == 0) {
			// Send Confirmation Email or SMS
			if (config('settings.mail.confirmation') == 1) {
				try {
					$post->notify(new PostRepublished($post));
				} catch (\Throwable $e) {
					return $this->respondError($e->getMessage());
				}
			}
			
			$data = [
				'success' => true,
				'message' => t('the_repost_has_done_successfully'),
				'result'  => new PostResource($post),
			];
			
			return $this->apiResponse($data);
		} else {
			return $this->respondError(t('the_repost_has_failed'));
		}
	}
}
