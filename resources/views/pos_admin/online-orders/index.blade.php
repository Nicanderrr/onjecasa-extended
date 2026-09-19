@extends('layouts.pos-admin')

@section('title', 'Online Orders - POS Admin')
@section('page-eyebrow', 'Website Fulfillment')
@section('page-title', 'Online Orders')
@section('page-description', 'Monitor website orders, staff assignment, delivery status, and branch fulfillment.')

@section('content')
  @include('shared.online-orders-workflow', [
      'title' => 'Online Orders',
      'description' => 'Branch admins can supervise, accept, and update website fulfillment.',
      'acceptRoute' => 'pos.admin.online-orders.accept',
      'statusRoute' => 'pos.admin.online-orders.status',
      'orders' => $orders,
      'statusLabels' => $statusLabels,
      'canOverride' => $canOverride,
  ])
@endsection
