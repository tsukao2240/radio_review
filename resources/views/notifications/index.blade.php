@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-bell me-2"></i>通知一覧</h4>
                    @if($notifications->where('is_read', false)->count() > 0)
                        <form action="{{ route('api.notifications.markAllRead') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-light">
                                <i class="fas fa-check-double me-1"></i>全て既読にする
                            </button>
                        </form>
                    @endif
                </div>

                <div class="card-body p-0">
                    @if($notifications->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">通知はありません</h5>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($notifications as $notification)
                                <div class="list-group-item {{ $notification->is_read ? 'bg-light' : 'bg-white' }} border-start border-4
                                    @if($notification->type === 'recording_complete') border-success
                                    @elseif($notification->type === 'recording_start') border-info
                                    @elseif($notification->type === 'recording_failed') border-danger
                                    @elseif($notification->type === 'favorite_broadcast') border-warning
                                    @else border-secondary
                                    @endif">

                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="me-2" style="font-size: 1.5em;">
                                                    @if($notification->type === 'recording_start')
                                                        🔴
                                                    @elseif($notification->type === 'recording_complete')
                                                        ✅
                                                    @elseif($notification->type === 'recording_failed')
                                                        ❌
                                                    @elseif($notification->type === 'favorite_broadcast')
                                                        ⭐
                                                    @else
                                                        🔔
                                                    @endif
                                                </span>
                                                <h5 class="mb-0 {{ $notification->is_read ? 'text-muted' : 'fw-bold' }}">
                                                    {{ $notification->title }}
                                                </h5>
                                            </div>

                                            <p class="mb-2">{{ $notification->message }}</p>

                                            <div class="d-flex align-items-center text-muted small">
                                                <i class="fas fa-clock me-1"></i>
                                                <span>{{ $notification->created_at->diffForHumans() }}</span>
                                                @if($notification->is_read)
                                                    <span class="ms-3">
                                                        <i class="fas fa-check-circle me-1"></i>既読
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        @if(!$notification->is_read)
                                            <form action="{{ route('api.notifications.markRead') }}" method="POST" class="ms-3">
                                                @csrf
                                                <input type="hidden" name="notification_id" value="{{ $notification->id }}">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-check"></i> 既読
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if($notifications->isNotEmpty())
                    <div class="card-footer text-muted text-center">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            通知は30日間保存されます
                        </small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.list-group-item {
    transition: all 0.3s ease;
}

.list-group-item:hover {
    transform: translateX(5px);
}
</style>
@endsection
