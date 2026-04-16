@extends('layouts.frontend')

@push('styles')
<link rel="stylesheet" href="{{ asset('subscription/css/credit-history.css') }}">
@endpush

@section('content')
<!-- Credits History Page -->
<div style="background: #ffffff; min-height: 100vh;">
    <div style="max-width: 800px; margin: 0 auto; padding: 40px 20px;">

        <!-- Back link and title -->
        <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; margin-bottom: 20px;">
            <h1 style="font-size: 2.2rem; font-weight: 700; color: #222; margin: 0;">Credits history</h1>
            <a href="{{ url('/profile') }}" style="color: #e04ecb; text-decoration: none; font-size: 1rem;">&larr; back to dashboard</a>
        </div>

        <!-- Month section -->
        <div style="margin-bottom: 25px;">
            <h2 style="font-size: 1.8rem; font-weight: 600; color: #222; margin: 0 0 8px 0;">February 2026</h2>
            <div style="font-size: 1.1rem; color: #333; margin-bottom: 5px;">
                Balance forwarded from previous month: <strong>0</strong>
            </div>
        </div>

        <!-- Credits table -->
        <div style="overflow-x: auto; margin-bottom: 40px; border-radius: 12px; border: 1px solid #eaeaea; background: #fff;">
            <table class="credits-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Credits used</th>
                        <th>Credits received</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>1 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>2 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>3 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>4 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>5 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>6 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>7 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>8 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>9 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>10 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>11 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>12 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>13 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>14 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>15 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>16 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>17 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>18 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>19 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>20 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>21 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>22 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>23 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>24 Feb</td><td>0</td><td>0</td><td>0</td></tr>
                    <tr><td>25 Feb</td><td>0</td><td>21</td><td>21</td></tr>
                    <tr><td>26 Feb</td><td>0</td><td>0</td><td>21</td></tr>
                    <tr><td>27 Feb</td><td>0</td><td>0</td><td>21</td></tr>
                    <tr><td>28 Feb</td><td>0</td><td>0</td><td>21</td></tr>
                </tbody>
            </table>
        </div>


    </div>
</div>

@endsection
