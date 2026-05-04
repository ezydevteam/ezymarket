@extends('themes.main.layouts.single')
@section('title', translate('Feedback'))

@section('main')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>{{ translate('Submit Feedback') }}</h4>
                </div>
                <div class="card-body">
                   	<form id="feedbackForm" method="POST" action="{{ route('feedback.store') }}" enctype="multipart/form-data">
						@csrf
						<div class="form-body">
							<!-- Field -->
							<div class="mb-3">
								<label for="feedbackField" class="form-label fw-500">
									{{ translate('Feedback Category') }} <span class="text-danger">*</span>
								</label>
								<select class="form-select fw-light" name="field" >
									<option value="">{{ translate('Select a category') }}</option>
									@foreach(\App\Models\Feedback::getFeedbackFields() as $key => $field)
										<option value="{{ $key }}" {{ old('field') == $key ? 'selected' : '' }}>
											{{ $field }}
										</option>
									@endforeach
								</select>
							</div>

							<!-- Subject -->
							<div class="mb-3">
								<label for="feedbackSubject" class="form-label fw-500">
									{{ translate('Subject') }} <span class="text-danger">*</span>
								</label>
								<input type="text" 
									   class="form-control small fw-light" 
									   name="subject" 
									   value="{{ old('subject') }}"
									   placeholder="{{ translate('e.g. Website loading issue') }}"
									   maxlength="255" 
									   >
							</div>

							<!-- Description -->
							<div class="mb-3">
								<label for="feedbackDescription" class="form-label fw-500">
									{{ translate('Describe your feedback') }} <span class="text-danger">*</span>
								</label>
								<textarea class="form-control small fw-light" 
										  name="description" 
										  rows="4" 
										  placeholder="{{ translate('Please provide detailed feedback...') }}"
										  maxlength="2000"
										  required>{{ old('description') }}</textarea>
							</div>

							<!-- Screenshots -->
							<div class="mb-3">
								<label for="screenshots" class="form-label fw-500">{{ translate('Screenshots') }}</label>
								<input type="file" 
									   id="screenshotInput" 
									   name="screenshots[]"
									   class="form-control" 
									   accept="image/*" 
									   multiple>
								<div id="previewContainer" class="mt-2 d-flex gap-2 flex-wrap"></div>
								<small class="form-text text-muted">
									{{ translate('Max 4 images, 5MB each') }}
								</small>
							</div>
						</div>

						<div class="form-footer d-flex align-items-center justify-content-center py-2">
							<button type="submit" class="btn btn-primary btn-padding px-5">
								{{ translate('Submit Feedback') }}
							</button>
						</div>
					</form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const screenshotInput = document.getElementById('screenshotInput');
    const previewContainer = document.getElementById('previewContainer');
    let selectedFiles = [];

    screenshotInput.addEventListener('change', function(e) {
        selectedFiles = Array.from(e.target.files);
        updatePreview();
    });

    function updatePreview() {
        previewContainer.innerHTML = '';
        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'preview-image';
                previewDiv.innerHTML = `
                    <img src="${e.target.result}" style="width:80px;height:80px;object-fit:cover;border-radius:8px;" alt="Preview">
                    <button type="button" onclick="removeImage(${index})" style="position:absolute;top:-8px;right:-8px;background:#dc3545;color:white;border:none;border-radius:50%;width:20px;height:20px;cursor:pointer;">&times;</button>
                `;
                previewDiv.style.position = 'relative';
                previewDiv.style.display = 'inline-block';
                previewContainer.appendChild(previewDiv);
            };
            reader.readAsDataURL(file);
        });
    }

    window.removeImage = function(index) {
        selectedFiles.splice(index, 1);
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.products.add(file));
        screenshotInput.files = dt.files;
        updatePreview();
    };
});
</script>
