@extends('themes.main.layouts.single')
@section('title', translate('Contact Us'))
@section('container', 'container container-default')
@section('header_style', 'no_header')

@section('main')
<div class="section contact-section">
    <div class="card overflow-hidden shadow rounded-4 border-0 transition-all">
        <div class="row g-0">
            <!-- Contact Information Sidebar -->
            <div
                class="col-lg-5 bg-primary-light p-4 p-md-5 d-flex flex-column justify-content-between border-end border-light">
                <div>
                    <span
                        class="badge bg-primary rounded-pill px-3 py-2 mb-3 text-uppercase fs-12 fw-bold letter-spacing-1">
                        {{ translate('Get in touch') }}
                    </span>
                    <h2 class="fw-bold mb-4 display-6">{{ translate('Contact Us') }}</h2>
                    <p class="text-gray-700 mb-5 fs-15 lh-lg">
                        {{ translate('Have questions or need assistance? Our team is here to help you get the most out
                        of your experience.') }}
                    </p>

                    <div class="contact-info-list d-flex flex-column gap-4">
                        <div
                            class="d-flex align-items-center gap-3 p-3 rounded-4 bg-white shadow-sm transition-all hover-shadow">
                            <div
                                class="d-flex align-items-center justify-content-center icon-circle bg-primary-light text-primary shadow-sm">
                                <i class="bi bi-envelope-open-fill"></i>
                            </div>
                            <div>
                                <div class="small text-muted fw-500">{{ translate('Email us at') }}</div>
                                <a href="mailto:{{ @settings('general')->contact_email }}"
                                    class="text-dark fw-bold hover-primary">
                                    {{ @settings('general')->contact_email }}
                                </a>
                            </div>
                        </div>

                        <div
                            class="d-flex align-items-center gap-3 p-3 rounded-4 bg-white shadow-sm transition-all hover-shadow">
                            <div
                                class="d-flex align-items-center justify-content-center icon-circle bg-secondary-subtle text-secondary shadow-sm">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                            <div>
                                <div class="small text-muted fw-500">{{ translate('Support Hours') }}</div>
                                <div class="text-dark fw-bold">{{ translate('Always open') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 p-4 rounded-4 border border-primary border-dashed bg-white-50">
                    <div class="d-flex gap-2 align-items-center text-primary">
                        <span><i class="bi bi-info-circle-fill fs-5 mt-1"></i></span>
                        <span class="small fw-500">
                            {{ translate('Average response time is currently under 12 hours.') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-7 p-4 p-md-5 bg-white">
                <form action="{{ route('contact.submit') }}" method="POST" class="contact-form">
                    @csrf
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-uppercase">{{ translate('Full Name')
                                }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i
                                        class="bi bi-person text-muted"></i></span>
                                <input type="text" name="name" class="form-control bg-light border-0 py-3"
                                    placeholder="{{ translate('Enter your name') }}"
                                    value="{{ authUser() ? authUser()->full_name : old('name') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-uppercase">{{ translate('Email Address')
                                }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i
                                        class="bi bi-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control bg-light border-0 py-3"
                                    placeholder="{{ translate('Enter your email') }}"
                                    value="{{ authUser() ? authUser()->email : old('email') }}" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-uppercase">{{ translate('Subject')
                                }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i
                                        class="bi bi-chat-left-dots text-muted"></i></span>
                                <input type="text" name="subject" class="form-control bg-light border-0 py-3"
                                    placeholder="{{ translate('How can we help?') }}" value="{{ old('subject') }}"
                                    required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-uppercase">{{ translate('Detailed Message')
                                }}</label>
                            <textarea name="message" rows="5" class="form-control bg-light border-0 p-3"
                                placeholder="{{ translate('Type your message here...') }}"
                                required>{{ old('message') }}</textarea>
                        </div>
                    </div>

                    <div class="mb-4">
                        <x-captcha />
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold btn-modern shadow">
                        {{ translate('Send Message') }}<i class="bi bi-send-fill fs-14 ms-2"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
