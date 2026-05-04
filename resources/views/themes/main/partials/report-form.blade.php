<div class="row align-items-center justify-content-center">
    <div class="card rounded-4 border-0 p-3">
    <form action="{{ route('contact.submit') }}" method="POST">
        @csrf
            <div class="col-12">
                <div class="input-group mb-4">
                    <div class="input-group-prepend">
                    <span class="input-group-text" id="reportFormFirstName"><i class="bi bi-person me-1"></i></span>
                    </div>
                     <input type="text" name="name" class="form-control" placeholder="{{ translate('Your name') }}"
                    value="{{ auth()->user() ? auth()->user()->full_name : '' }}" aria-describedby="reportFormFirstName" required>
                </div>
            </div>
            <div class="col-12">
                <div class="input-group mb-4">
                    <div class="input-group-prepend">
                    <span class="input-group-text" id="reportFormEmail"><i class="bi bi-envelope me-1"></i></span>
                    </div>
                     <input type="email" name="email" class="form-control" placeholder="{{ translate('Your email') }}"
                    value="{{ auth()->user() ? auth()->user()->email : '' }}" aria-describedby="reportFormEmail" required>
                </div>
            </div>
            <div class="col-12">
                <div class="input-group mb-4">
                    <div class="input-group-prepend">
                        <label class="input-group-text" for="reportFormReason"><i class="bi bi-flag me-1"></i></label>
                    </div>
                    <select class="form-select" name="subject" id="reportFormReason" required>
                        <option value="" disabled selected>{{ translate('Select reason') }}</option>
                         <option value="intellectual-property">{{ translate('Bugs Report') }}</option>
                        <option value="intellectual-property">{{ translate('Intellectual Property') }}</option>
                        <option value="fake-item">{{ translate('Fake or Unoriginal product') }}</option>
                         <option value="policy-violation">{{ translate('Spam or Misleading') }}</option>
                        <option value="policy-violation">{{ translate('Policy Violation') }}</option>
                          <option value="policy-violation">{{ translate('Sexual Content') }}</option>
                        <option value="others">{{ translate('Others') }}
                            </option>
                    </select>
                </div>
            </div>
            @isset($product)
            <div class="col-12">
                <div class="input-group mb-4">
                    <div class="input-group-prepend">
                    <span class="input-group-text" id="reportFormproductLink"><i class="bi bi-link me-1"></i></span>
                    </div>
                     <input type="url" name="url" class="form-control" placeholder="{{ translate('product link') }}" value="{{ $product->view_link ?? '' }}" aria-describedby="reportFormproductLink">
                </div>
            </div>
            @endisset
            <div class="col-12">
                <div class="input-group mb-4">
                    <span class="input-group-text" id="productReportMessage"><i class="bi bi-pencil-square me-1"></i></span>
                    <textarea  type="text" name="message" class="form-control" placeholder="{{ translate('Write Details') }}" aria-label="text area" aria-describedby="productReportMessage" value="{{ old('message') }}" required></textarea>
                </div>
            </div>
        <x-captcha />
        <button class="btn btn-primary w-100">
            {{ translate('Submit') }}
        </button>
    </form>
    </div>
</div>
