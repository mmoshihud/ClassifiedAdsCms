<?php
$post ??= [];
?>
<div class="modal fade" id="contactUser" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			
			<div class="modal-header px-3">
				<h4 class="modal-title">
					<i class="fas fa-envelope"></i> {{ t('contact_advertiser') }}
				</h4>
				
				<button type="button" class="close" data-bs-dismiss="modal">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">{{ t('Close') }}</span>
				</button>
			</div>
			
			<form role="form" method="POST" action="{{ url('account/messages/posts/' . data_get($post, 'id')) }}" enctype="multipart/form-data">
				{!! csrf_field() !!}
				<div class="modal-body">

					@if (isset($errors) && $errors->any() && old('messageForm')=='1')
						<div class="alert alert-danger alert-dismissible">
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ t('Close') }}"></button>
							<ul class="list list-check">
								@foreach($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif
					
					@if (auth()->check())
						<input type="hidden" name="from_name" value="{{ auth()->user()->name }}">
						@if (!empty(auth()->user()->email))
							<input type="hidden" name="from_email" value="{{ auth()->user()->email }}">
						@else
							{{-- from_email --}}
							<?php $fromEmailError = (isset($errors) && $errors->has('from_email')) ? ' is-invalid' : ''; ?>
							<div class="mb-3 required">
								<label for="from_email" class="control-label">{{ t('E-mail') }}
									@if (!isEnabledField('phone'))
										<sup>*</sup>
									@endif
								</label>
								<div class="input-group">
									<span class="input-group-text"><i class="far fa-envelope"></i></span>
									<input id="from_email"
										name="from_email"
										type="text"
										class="form-control{{ $fromEmailError }}"
										placeholder="{{ t('eg_email') }}"
										value="{{ old('from_email', auth()->user()->email) }}"
									>
								</div>
							</div>
						@endif
					@else
						{{-- from_name --}}
						<?php $fromNameError = (isset($errors) && $errors->has('from_name')) ? ' is-invalid' : ''; ?>
						<div class="mb-3 required">
							<label for="from_name" class="control-label">{{ t('Name') }} <sup>*</sup></label>
							<div class="input-group">
								<input id="from_name"
									name="from_name"
									type="text"
									class="form-control{{ $fromNameError }}"
									placeholder="{{ t('your_name') }}"
									value="{{ old('from_name') }}"
								>
							</div>
						</div>
							
						{{-- from_email --}}
						<?php $fromEmailError = (isset($errors) && $errors->has('from_email')) ? ' is-invalid' : ''; ?>
						<div class="mb-3 required">
							<label for="from_email" class="control-label">{{ t('E-mail') }}
								@if (!isEnabledField('phone'))
									<sup>*</sup>
								@endif
							</label>
							<div class="input-group">
								<span class="input-group-text"><i class="far fa-envelope"></i></span>
								<input id="from_email"
									name="from_email"
									type="text"
									class="form-control{{ $fromEmailError }}"
									placeholder="{{ t('eg_email') }}"
									value="{{ old('from_email') }}"
								>
							</div>
						</div>
					@endif
					
					{{-- from_phone --}}
					<?php $fromPhoneError = (isset($errors) && $errors->has('from_phone')) ? ' is-invalid' : ''; ?>
					<div class="mb-3 required">
						<label for="phone" class="control-label">{{ t('phone_number') }}
							@if (!isEnabledField('email'))
								<sup>*</sup>
							@endif
						</label>
						<div class="input-group">
							<span id="phoneCountry" class="input-group-text"><i class="fas fa-mobile-alt"></i></span>
							<input id="from_phone"
								name="from_phone"
								type="text"
								maxlength="60"
								class="form-control{{ $fromPhoneError }}"
								placeholder="{{ t('phone_number') }}"
								value="{{ old('from_phone', (auth()->check()) ? auth()->user()->phone : '') }}"
							>
						</div>
					</div>
					
					{{-- body --}}
					<?php $bodyError = (isset($errors) && $errors->has('body')) ? ' is-invalid' : ''; ?>
					<div class="mb-3 required">
						<label for="body" class="control-label">
							{{ t('Message') }} <span class="text-count">(500 max)</span> <sup>*</sup>
						</label>
						<textarea id="body"
							name="body"
							rows="5"
							class="form-control required{{ $bodyError }}"
							style="height: 150px;"
							placeholder="{{ t('your_message_here') }}"
						>{{ old('body', t('is_still_available', ['name' => data_get($post, 'contact_name', t('sir_miss'))])) }}</textarea>
					</div>

					<?php
						$catType = data_get($post, 'category.parent.type', data_get($post, 'category.type'));
					?>
					@if (in_array($catType, ['job-offer']))
						{{-- filename --}}
						<?php $filenameError = (isset($errors) && $errors->has('filename')) ? ' is-invalid' : ''; ?>
						<div class="mb-3 required" {!! (config('lang.direction')=='rtl') ? 'dir="rtl"' : '' !!}>
							<label for="filename" class="control-label{{ $filenameError }}">{{ t('Resume') }} </label>
							<input id="filename" name="filename" type="file" class="file{{ $filenameError }}">
							<div class="form-text text-muted">
								{{ t('file_types', ['file_types' => showValidFileTypes('file')]) }}
							</div>
						</div>
						<input type="hidden" name="catType" value="{{ $catType }}">
					@endif
					
					@include('layouts.inc.tools.captcha', ['label' => true])
					
					<input type="hidden" name="country_code" value="{{ config('country.code') }}">
					<input type="hidden" name="post_id" value="{{ data_get($post, 'id') }}">
					<input type="hidden" name="messageForm" value="1">
				</div>
				
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary float-end">{{ t('send_message') }}</button>
					<button type="button" class="btn btn-default" data-bs-dismiss="modal">{{ t('Cancel') }}</button>
				</div>
			</form>
			
		</div>
	</div>
</div>
@section('after_styles')
	@parent
	<link href="{{ url('assets/plugins/bootstrap-fileinput/css/fileinput.min.css') }}" rel="stylesheet">
	@if (config('lang.direction') == 'rtl')
		<link href="{{ url('assets/plugins/bootstrap-fileinput/css/fileinput-rtl.min.css') }}" rel="stylesheet">
	@endif
	<style>
		.krajee-default.file-preview-frame:hover:not(.file-preview-error) {
			box-shadow: 0 0 5px 0 #666666;
		}
	</style>
@endsection

@section('after_scripts')
    @parent
	
	<script src="{{ url('assets/plugins/bootstrap-fileinput/js/plugins/sortable.min.js') }}" type="text/javascript"></script>
	<script src="{{ url('assets/plugins/bootstrap-fileinput/js/fileinput.min.js') }}" type="text/javascript"></script>
	<script src="{{ url('assets/plugins/bootstrap-fileinput/themes/fas/theme.js') }}" type="text/javascript"></script>
	<script src="{{ url('common/js/fileinput/locales/' . config('app.locale') . '.js') }}" type="text/javascript"></script>

	<script>
		/* Initialize with defaults (Resume) */
		$('#filename').fileinput(
		{
			theme: 'fas',
            language: '{{ config('app.locale') }}',
			@if (config('lang.direction') == 'rtl')
				rtl: true,
			@endif
			showPreview: false,
			allowedFileExtensions: {!! getUploadFileTypes('file', true) !!},
			showUpload: false,
			showRemove: false,
			maxFileSize: {{ (int)config('settings.upload.max_file_size', 1000) }}
		});
	</script>
	<script>
		$(document).ready(function () {
			@if ($errors->any())
				@if ($errors->any() && old('messageForm')=='1')
					{{-- Re-open the modal if error occured --}}
					let quickLogin = new bootstrap.Modal(document.getElementById('contactUser'), {});
					quickLogin.show();
				@endif
			@endif
		});
	</script>
@endsection
