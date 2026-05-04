<x-datatable
    id="sellerTaxesTable"
    :items="$sellerTaxes"
    tableClass="datatable2"
    emptyMessage="{{ translate('No seller taxes found!') }}"
    emptyDescription="{{ translate('Create your first seller tax to get started') }}"
    emptyIcon="bi-bag-check"
>
    <thead>
        <tr>
            <th class="no-sort">
                <input type="checkbox" class="form-check-input bulk-select-checkbox">
            </th>
            <th>{{ translate('Name') }}</th>
            <th>{{ translate('Effective Countries') }}</th>
            <th class="text-center">{{ translate('Tax Rate') }}</th>
            <th class="text-center">{{ translate('Created Date') }}</th>
            <th class="text-end no-sort">{{ translate('Actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($sellerTaxes as $tax)
            <tr>
                <td>
                    <input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="{{ $tax->id }}">
                </td>
                <td>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#editSellerTaxModal-{{ $tax->id }}" class="text-dark">
                        {{ $tax->name }}
                    </a>
                </td>
                <td>
                    @if (count($tax->countries) > 3)
                        {{ translate(':count Countries', ['count' => count($tax->countries)]) }}
                    @else
                        {{ implode(
                            ', ',
                            array_map(function ($country) {
                                return countries($country);
                            }, $tax->countries),
                        ) }}
                    @endif
                </td>
                <td class="text-center text-primary">{{ $tax->percentage }}%</td>
                <td class="text-center">{{ dateFormat($tax->created_at) }}</td>
                <td>
                    <div class="text-end">
                        <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                            <x-dropdown.item
                                href="#"
                                data-bs-toggle="modal"
                                data-bs-target="#editSellerTaxModal-{{ $tax->id }}"
                                icon="bi bi-pencil-square"
                                iconClass="text-primary me-2">
                                {{ translate('Edit') }}
                            </x-dropdown.item>
                            <x-dropdown.item type="divider" />
                            <x-dropdown.item
                                href="{{ route('admin.financial.seller-taxes.destroy', $tax->id) }}"
                                icon="bi bi-trash"
                                color="danger"
                                class="action-confirm"
                                data-method="DELETE"
                                data-confirm="{{ translate('Are you sure to delete this tax? This action cannot be undone.') }}">
                                {{ translate('Delete') }}
                            </x-dropdown.item>
                        </x-dropdown>
                    </div>
                </td>
            </tr>

            {{-- Include Edit Modal for each tax --}}
            @include('admin.financial.seller-taxes.partials.edit-modal', ['tax' => $tax])
        @endforeach
    </tbody>
</x-datatable>




















