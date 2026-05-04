@extends('admin.layouts.full')
@section('section', translate('System'))
@section('title', translate('Rich Text Images'))
@section('content')
    <x-datatable
        id="richTextImagesTable"
        :items="$richTextImages"
        tableClass="datatable2"
        emptyMessage="{{ translate('No images found!') }}"
        emptyDescription="{{ translate('All uploaded rich text editor images will appear here') }}"
        emptyIcon="bi-image">
        <thead>
            <tr>
                <th class="text-nowrap">{{ translate('ID') }}</th>
                <th class="text-nowrap no-sort">{{ translate('Details') }}</th>
                <th class="text-center text-nowrap">{{ translate('Uploaded Date') }}</th>
                <th class="text-end no-sort text-nowrap">{{ translate('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($richTextImages as $richTextImage)
                <tr>
                    <td>{{ $richTextImage->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <span class="image-fluid rounded">
                                <img src="{{ $richTextImage->view_link }}" alt="{{ $richTextImage->name }}">
                            </span>
                            <div>
                                <span class="text-reset d-block fw-medium">{{ truncateText($richTextImage->name, 40) }}</span>
                                <small class="text-muted">{{ $richTextImage->view_link }}</small>
                            </div>
                        </div>
                    </td>
                    <td class="text-center text-muted">{{ dateFormat($richTextImage->created_at) }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.system.rich-text-images.destroy', $richTextImage->id) }}"
                            class="btn btn-danger btn-sm action-confirm"
                            data-method="DELETE"
                            data-confirm="{{ translate('Are you sure you want to delete this image?') }}">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-datatable>
@endsection


















