@extends('admin.layouts.full')
@section('section', translate('Mail'))
@section('title', translate('Mail Templates'))
@section('content')
    <x-datatable
        id="mailTemplatesTable"
        :items="$mailTemplates"
        emptyMessage="{{ translate('No mail templates found!') }}"
        emptyDescription="{{ translate('Create your first email template to get started') }}"
        emptyIcon="bi-envelope"
    >
        <thead>
            <tr>
                <th class="no-sort">
                    <input type="checkbox" class="form-check-input bulk-select-checkbox">
                </th>
                <th>{{ translate('Template Name') }}</th>
                <th>{{ translate('Subject') }}</th>
                <th class="text-center">{{ translate('Status') }}</th>
                <th class="text-end no-sort">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($mailTemplates as $mailTemplate)
                <tr>
                    <td>
                         <input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="{{ $mailTemplate->id }}">
                    </td>
                    <td>
                        <a class="text-dark hover-primary" href="{{ route('admin.mail.templates.edit', $mailTemplate->id) }}">
                            {{ translate($mailTemplate->name) }}
                        </a>
                    </td>
                    <td>
                        <a class="text-dark hover-primary" href="{{ route('admin.mail.templates.edit', $mailTemplate->id) }}">
                            {{ $mailTemplate->subject }}
                        </a>
                    </td>
                    <td class="text-center">
                        @php $activeMail = $mailTemplate->isActive(); @endphp
                        <span class="badge bg-text-{{ $activeMail ? 'green' : 'red' }}">
                            <i class="bi bi-{{ $activeMail ? 'check-circle' : 'x-circle' }} me-1"></i>
                            {{ translate($activeMail ? 'Active' : 'Inactive') }}
                        </span>
                    </td>
                    <td>
                        <div class="text-end">
                            <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                                <x-dropdown.item
                                    href="{{ route('admin.mail.templates.edit', $mailTemplate->id) }}"
                                    icon="bi-pencil-square"
                                    iconClass="text-primary">
                                    {{ translate('Edit Details') }}
                                </x-dropdown.item>
                                @if (!$mailTemplate->isDefault())
                                    <x-dropdown.item type="divider" />
                                    <x-dropdown.item
                                        href="{{ route('admin.mail.templates.destroy', $mailTemplate->id) }}"
                                        icon="bi-trash"
                                        color="danger"
                                        class="action-confirm"
                                        data-method="DELETE"
                                        data-confirm="{{ translate('Are you sure you want to delete this template?') }}">
                                        {{ translate('Delete') }}
                                    </x-dropdown.item>
                                @endif
                            </x-dropdown>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-datatable>
@endsection

@push('top_scripts')
<script>
    "use strict";
    config.translates.searchPlaceholder = "{{ translate('Search Mail Templates') }}";
</script>
@endpush

@push('scripts_libs')
    <script>
        "use strict";
        const tableElement = document.getElementById('mailTemplatesTable');
            if (tableElement) {
                const bulkActions = [
                    {
                        text: '<i class="bi bi-trash text-danger me-2"></i>{{ translate("Delete Selected") }}',
                        className: 'dropdown-item text-danger',
                        action: function(e, dt, node, config) {
                            bulkAction({
                                url: "{{ route('admin.mail.templates.bulk-delete') }}",
                                method: 'DELETE',
                                confirmMessage: "{{ translate('Are you sure you want to delete the selected mail templates?') }}"
                            });
                        }
                    }
                ];

                $(tableElement).data('bulk-actions', bulkActions);
            }
    </script>
@endpush















