<form id="reportProductForm" method="POST" action="" enctype="multipart/form-data">
	@csrf
	<div class="form-body">
		<!-- Report Reason -->
		<div class="mb-3">
			<label for="reportReason" class="form-label fw-500">
				{{ translate('Report Reason') }} <span class="text-danger">*</span>
			</label>
			<select class="form-select fw-light" id="reportReason" name="reason" required>
				<option value="">{{ translate('Select a reason') }}</option>
				@foreach(\App\Models\Product\ProductReport::getReasonOptions() as $key => $reason)
					<option value="{{ $key }}">{{ $reason }}</option>
				@endforeach
			</select>
		</div>
		<!-- Detailed Description -->
		<div class="mb-3">
			<label for="reportDescription" class="form-label fw-500">
				{{ translate('Describe the reason') }} <span class="text-danger">*</span>
			</label>
			<textarea class="form-control small fw-light"
					  id="reportDescription"
					  name="description"
					  rows="4"
					  placeholder="{{ translate('Please provide more details about why you are reporting this product...') }}"
					  maxlength="1000"></textarea>
		</div>
		<!-- Screenshots -->
		<div class="mb-3">
			<label for="screenshots" class="form-label fw-500">{{ translate('Screenshots') }}</label>
			<input type="file" name="screenshots[]" id="screenshotInput" class="form-control" accept="image/jpg, image/png, image/jpeg" multiple>
			<div id="previewContainer" class="mt-2 d-flex gap-2 flex-wrap"></div>
			<small class="form-text text-muted">
				{{ translate('Accepted: jpg, jpeg, png. Max 3 images') }}
			</small>
		</div>
	</div>
	<div class="form-footer d-flex align-items-center justify-content-center py-2">
		<button type="submit" class="btn btn-danger btn-padding px-5" id="submitReportBtn">
			{{ translate('Submit Report') }}
		</button>
	</div>
</form>
