@extends('twill::layouts.free')

@section('content')

<div class="wrapper">

    <div style="padding:20px">

        <h1>Customers</h1>

        <form method="GET">

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search..."
            >

            <button type="submit">
                Search
            </button>

        </form>

        <br>

        <table class="table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th width="250"></th>
                </tr>
            </thead>

            <tbody>

                @foreach($customers as $customer)

                    <tr>

                        <td>
                            {{ $customer->id }}
                        </td>

                        <td>
                            {{ $customer->name }}
                        </td>

                        <td>
                            {{ $customer->email }}
                        </td>

                        <td>
                            {{ $customer->is_active ? 'Active' : 'Locked' }}
                        </td>

                        <td>

                            <a
                                href="{{ route('admin.customers.show', $customer) }}"
                            >
                                View
                            </a>

                            <form
                                method="POST"
                                action="{{ route('admin.customers.toggle', $customer) }}"
                                style="display:inline"
                            >
                                @csrf

                                <button type="submit">

                                    {{ $customer->is_active
                                        ? 'Lock'
                                        : 'Unlock'
                                    }}

                                </button>

                            </form>

                            

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

        {{ $customers->links() }}

    </div>

</div>

@endsection