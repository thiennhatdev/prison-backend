@extends('twill::layouts.free')

@section('content')

<div style="padding:20px">

    <h1>
        {{ $customer->name }}
    </h1>

    <p>Email: {{ $customer->email }}</p>

    <p>Phone: {{ $customer->phone }}</p>

    <p>Status:
        {{ $customer->is_active ? 'Active' : 'Locked' }}
    </p>

</div>

@endsection