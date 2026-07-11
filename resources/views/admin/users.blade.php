@extends('layouts.app')

@section('title', '会員・パスキー管理')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="fw-bold text-dark mb-0">会員名簿 ＆ パスキー（生体認証）管理</h3>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary py-2 px-3 fw-semibold">
                    ➕ 新規会員を直接追加
                </a>
                <a href="{{ route('admin.users.transition') }}" class="btn btn-outline-primary py-2 px-3 fw-semibold">
                    🔄 新年度への移行・引き継ぎ
                </a>
            </div>
        </div>
        <p class="text-muted small mb-4">実行委員会に登録されている正式会員の一覧、紹介関係、およびログイン用パスキーの管理を行います。パスキーの紛失対策や追加デバイス登録はここから制御します。</p>

        <!-- パスキー再発行・新規直接追加時のワンタイム登録URLおよび初期情報表示エリア -->
        @if(session('register_url'))
            <div class="card border-primary border-2 shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3 fw-bold">
                    ➕ 会員「{{ session('session_user_name') }}」のパスキー登録セッションを開始しました
                </div>
                <div class="card-body">
                    <p class="small text-dark fw-semibold mb-2">ログインおよびパスキー設定用のワンタイムURLです：</p>
                    
                    <div class="input-group mb-3">
                        <input type="text" id="admin-copy-url" class="form-control text-primary-emphasis border-primary bg-light" 
                               value="{{ session('register_url') }}" readonly>
                        <button class="btn btn-success" type="button" id="btn-admin-copy-url">
                            📋 URLをコピー
                        </button>
                    </div>
                    
                    <div class="alert alert-warning py-2 mb-0 small" role="alert">
                        <strong>⚠️ 注意:</strong> この登録用URLは<strong>有効期限は24時間</strong>です。ログインおよび追加登録を行いたいユーザーへ直接共有し、新しいデバイスで指紋・顔認証を設定するよう案内してください。
                    </div>
                </div>
            </div>
        @endif

        <div class="card p-4 shadow-sm border-0">
            @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>氏名 (かな) / ロール</th>
                                <th>LINEアカウント名</th>
                                <th>出自 (本業 / 所属)</th>
                                <th>紹介者 ＆ 承認者</th>
                                <th>登録済みパスキーデバイス (操作は管理者のみ)</th>
                                <th class="text-center">アクション</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <!-- 氏名・ロール -->
                                    <td>
                                        <div class="fw-bold"><a href="{{ route('users.show', $user) }}" class="text-decoration-none text-dark">{{ $user->name }}</a></div>
                                        <div class="text-muted small mb-1">{{ $user->name_kana }}</div>
                                        <div>
                                            @if($user->hasRole('admin'))
                                                <span class="badge badge-admin badge-sm">システム管理</span>
                                            @endif
                                            @if($user->hasRole('kanji'))
                                                <span class="badge badge-kanji badge-sm">幹事</span>
                                            @endif
                                            @if($user->hasRole('equipment_manager'))
                                                <span class="badge bg-primary badge-sm">備品管理</span>
                                            @endif
                                            @if($user->hasRole('general'))
                                                <span class="badge badge-general badge-sm">一般会員</span>
                                            @endif
                                        </div>

                                    </td>
                                    
                                    <!-- LINEアカウント -->
                                    <td class="fw-semibold text-primary-color">
                                        {{ $user->line_display_name }}
                                    </td>
                                    
                                    <!-- 出自 -->
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $user->profession }}</div>
                                        <div class="text-muted small">{{ $user->affiliation ?: '所属なし' }}</div>
                                        @if(is_array($user->skills) && count($user->skills) > 0)
                                            <div class="mt-1">
                                                @foreach($user->skills as $skill)
                                                    <span class="badge bg-light text-dark border me-1" style="font-size: 0.7em;">{{ $skill }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    
                                    <!-- 紹介・承認者 -->
                                    <td class="small">
                                        <div class="mb-1">
                                            <span class="text-muted">紹介:</span>
                                            @if($user->referrer)
                                                <span class="fw-semibold">{{ $user->referrer->name }}</span>
                                            @elseif($user->referrer_text)
                                                <span class="text-warning-emphasis fw-semibold">{{ $user->referrer_text }}</span>
                                            @else
                                                <span class="text-muted">自己応募</span>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="text-muted">承認:</span>
                                            @if($user->approver)
                                                <span class="fw-semibold">{{ $user->approver->name }}</span>
                                            @else
                                                <span class="text-muted">システム</span>
                                            @endif
                                            @if($user->approved_at)
                                                <span class="text-muted d-block" style="font-size: 0.85em;">({{ $user->approved_at->format('Y-m-d') }})</span>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <!-- パスキーリスト -->
                                    <td>
                                        @if($user->webAuthnKeys->count() > 0)
                                            <ul class="list-unstyled mb-0 small">
                                                @foreach($user->webAuthnKeys as $key)
                                                    <li class="py-1 d-flex justify-content-between align-items-center border-bottom">
                                                        <div>
                                                            <span class="d-block fw-bold text-dark">📱 {{ $key->device_name }}</span>
                                                            <span class="text-muted" style="font-size: 0.85em;">登録: {{ $key->created_at->format('y-m-d') }}</span>
                                                            @if($key->last_used_at)
                                                                <span class="text-muted d-block" style="font-size: 0.85em;">最終: {{ $key->last_used_at->format('y-m-d H:i') }}</span>
                                                            @endif
                                                        </div>
                                                        <!-- パスキーの無効化（削除） -->
                                                        <form action="{{ route('admin.users.passkey-delete', ['user' => $user, 'key' => $key]) }}" method="POST" onsubmit="return confirm('本当にこのパスキーデバイスを無効化しますか？ユーザーは本端末での指紋・顔認証ログインができなくなります。');" class="ms-2">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger btn-xs py-0 px-1" style="font-size: 0.75rem;">
                                                                無効化
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-muted small">パスキー未設定 (要登録)</span>
                                        @endif
                                    </td>
                                    
                                    <!-- アクション（追加登録用セッション ＆ ユーザー削除） -->
                                    <td class="text-center">
                                        <div class="d-flex flex-column gap-2 justify-content-center align-items-center">
                                            <form action="{{ route('admin.users.passkey-session', $user) }}" method="POST" class="w-100">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-primary btn-sm py-1.5 px-3 fw-semibold w-100">
                                                    🔑 パスキー追加URL発行
                                                </button>
                                            </form>
                                            @if($user->id !== Auth::id())
                                                <form action="{{ route('admin.users.delete', $user) }}" method="POST" onsubmit="return confirm('本当にこのユーザー（{{ $user->name }}）をシステムから完全に削除しますか？\n登録済みのパスキーや会議の出欠など、関連するすべてのデータが完全に削除されます。');" class="w-100">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm py-1.5 px-3 fw-semibold w-100">
                                                        🗑️ ユーザーを削除
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-outline-secondary btn-sm py-1.5 px-3 fw-semibold w-100" disabled title="自分自身は削除できません">
                                                    🗑️ 自分は削除不可
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <p class="mb-0">登録されているアクティブな会員はいません。</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
