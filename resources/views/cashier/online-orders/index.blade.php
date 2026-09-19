@extends('layouts.cashier')

@section('title', 'Online Orders - Cashier')

@section('content')
  @include('shared.online-orders-workflow', [
      'title' => 'Online Orders',
      'description' => 'Accept and process website orders assigned to this branch.',
      'acceptRoute' => 'cashier.online-orders.accept',
      'statusRoute' => 'cashier.online-orders.status',
      'orders' => $orders,
      'statusLabels' => $statusLabels,
      'canOverride' => $canOverride,
  ])
@endsection
