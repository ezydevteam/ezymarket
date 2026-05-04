<div class="col-12">
    <div class="card card-body border-0 shadow-sm rounded-4 p-4 p-xl-5 bg-white position-relative overflow-hidden">
        <div class="row align-items-center position-relative z-1">
            <div class="col-lg-7">
                <div class="pe-xl-5">
                    <span class="badge bg-primary-light text-primary rounded-pill px-3 py-2 mb-3 fw-bold">
                        <i class="bi bi-gift-fill me-1"></i>{{ translate('Limited Time Reward') }}
                    </span>
                    <h2 class="display-6 fw-bold text-dark mb-3">
                        {{ translate('Invite Friends & Earn Commission') }}
                    </h2>
                    <p class="text-gray-700 fs-5 mb-4 mb-lg-5">
                        {{ translate('Our referral program offers a great way to earn passive income. Share your
                        link and get :percentage% of every purchase your referrals make — for life.', ['percentage'
                        => @$settings->referral->percentage]) }}
                    </p>

                    <div class="referral-link-box p-3 bg-light rounded-4 border border-dashed border-primary mb-4">
                        <label class="form-label small fw-bold text-gray-700 mb-2 text-uppercase letter-spacing-1">
                            {{ translate('Your Referral Link') }}
                        </label>
                        <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden border-0">
                            <input id="refLink" type="text" class="form-control border-0 bg-white"
                                value="{{ authUser()->referral_link }}" readonly>
                            <button type="button" class="btn btn-primary px-4 btn-copy"
                                data-clipboard-target="#refLink">
                                <i class="bi bi-copy me-2"></i>{{ translate('Copy Link') }}
                            </button>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted text-uppercase small fw-medium d-none d-lg-block">
                            {{ translate('Share on social:') }}
                        </span>
                        @themeInclude('partials.share-buttons', [
                        'socials_class' => 'social-btn-sm gap-2',
                        'link' => authUser()->referral_link,
                        'show_label' => false,
                        ])
                    </div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <div class="text-center" style="font-size: 15rem">
                    <i class="bi bi-people-fill text-primary opacity-10"></i>
                </div>
            </div>
        </div>
        {{-- Decorative background element --}}
        <div
            class="position-absolute bottom-0 end-0 opacity-10 pointer-events-none translate-middle-y me-n5 pe-5 d-none d-xl-block">
            <i class="bi bi-cash-coin display-1 text-primary"></i>
        </div>
    </div>
</div>
