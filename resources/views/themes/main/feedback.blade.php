@extends('themes.main.layouts.single')
@section('title', translate('Feedback'))

@section('main')
<div class="container">
    <div class="row justify-content-center mt-3">
        <div class="col-md-8">
            <div class="card border">
                <div class="card-header border-bottom">
                    <h5 class="mb-0">{{ translate('Submit a Feedback') }}</h5>
                </div>
                <div class="card-body">
                   	<form id="feedbackForm" method="POST" action="{{ route('feedback.store') }}" enctype="multipart/form-data">
						@csrf
						<div class="form-body">
							<!-- Field -->
							<div class="mb-3">
								<label for="feedbackField" class="form-label fw-500">
									{{ translate('Select reason') }} <span class="text-danger">*</span>
								</label>
								<select class="form-select fw-light" name="field" required>
									<option value="">{{ translate('--Choose one--') }}</option>
									@foreach(\App\Models\Feedback::getFeedbackFields() as $key => $field)
										<option value="{{ $key }}" {{ old('field') == $key ? 'selected' : '' }}>
											{{ $field }}
										</option>
									@endforeach
								</select>
							</div>

						<!-- Description -->
						<div class="mb-3">
							<label for="feedbackDescription" class="form-label fw-500">
								{{ translate('Describe the reason') }} <span class="text-danger">*</span>
							</label>
							<textarea class="form-control small fw-light"
									  id="feedbackDescription"
									  name="description"
									  rows="6"
									  placeholder="{{ translate('Write in detail...') }}"
									  minlength="50"
									  maxlength="1000"
									  required>{{ old('description') }}</textarea>
							<small class="form-text text-muted">
								<span id="charCount">0</span>/50 {{ translate('characters minimum') }} ({{ translate('1000 max') }})
							</small>
						</div>							<!-- Screenshots -->
							<div class="mb-3">
								<label for="screenshots" class="form-label fw-500">{{ translate('Screenshots') }}</label>
								<input type="file"
									   id="screenshotInput"
									   name="screenshots[]"
									   class="form-control"
									   accept="image/jpeg, image/jpg, image/png"
									   multiple>
								<small class="form-text text-muted">
									{{ translate('Max 4 images, 2MB each') }}
								</small>
								<div id="previewContainer" class="mt-2 d-flex gap-2 flex-wrap"></div>
							</div>
						</div>

						<div class="form-footer d-flex align-items-center justify-content-center py-2">
							<button type="submit" class="btn btn-primary btn-padding px-5">
								{{ translate('Submit') }}
							</button>
						</div>
					</form>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let fileArray = [];

    document.getElementById('screenshotInput').addEventListener('change', function(e) {
        let files = Array.from(e.target.files);

        if (fileArray.length + files.length > 4) {
            alert('Maximum 4 images allowed!');
        }

        files.forEach(file => {
            if (fileArray.length < 4) {
                fileArray.push(file);
            }
        });

        displayFiles();
        updateInput();
    });

    function displayFiles() {
        const container = document.getElementById('previewContainer');
        container.innerHTML = '';

        fileArray.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.style.cssText = 'display:inline-block; margin:5px; position:relative;';
                div.innerHTML = `
                    <img src="${e.target.result}" style="width:60px;height:60px;object-fit:cover;border-radius:5px;">
                    <span onclick="removeFile(${index})" style="position:absolute;top:-5px;right:-5px;background:red;color:white;border-radius:50%;width:20px;height:20px;cursor:pointer;text-align:center;line-height:20px;font-size:12px;">×</span>
                `;
                container.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

    function updateInput() {
        const input = document.getElementById('screenshotInput');
        const dt = new DataTransfer();
        fileArray.forEach(file => dt.items.add(file));
        input.files = dt.files;
    }

    window.removeFile = function(index) {
        fileArray.splice(index, 1);
        displayFiles();
        updateInput();
    };
});
</script>
@endpush
@endsection

