@extends('themes.main.layouts.single')
@section('header_title', $page->title)
@section('title', $page->title)
@section('description', $page->description ?? '')
@section('breadcrumbs', Breadcrumbs::render('page', $page))
@section('breadcrumbs_schema', Breadcrumbs::view('breadcrumbs::json-ld', 'page', $page))

@section('main')
    {!! $page->content !!}
@endsection
