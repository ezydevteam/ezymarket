@extends('admin.layouts.app')
@section('section', translate('Records'))
@section('title', translate('Premium Earning #:id', ['id' => $premiumEarning->id]))
@section('back', route('admin.records.premium-earnings.index'))
@section('container', 'container-max-md')
@section('content')
    <div class="card mb-4">
                        <strong>{{ translate('Last update') }}</strong>
                    </div>
                    <div class="col-auto">
                        <time>{{ dateFormat($premiumEarning->updated_at) }}</time>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    <form action="{{ route('admin.records.premium-earnings.destroy', $premiumEarning->id) }}" method="POST">
        @csrf
        @method('DELETE')
        <button class="btn btn-outline-danger btn-lg w-100 action-confirm">{{ translate('Delete') }}</button>
    </form>
@endsection



















