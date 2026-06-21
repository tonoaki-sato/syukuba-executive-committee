@extends('layouts.app')

@section('title', $user->name . ' さんの詳細プロフィール')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="mb-4">
            <h3 class="fw-bold text-dark mb-1">{{ $user->name }} さんの詳細プロフィール</h3>
            <p class="text-muted small">登録されている会員情報、出自、LINE情報、および年度別の活動履歴を表示します。</p>
        </div>

        <!-- 基本プロファイルカード -->
        <div class="card p-4 shadow-sm border-0 mb-4">
            <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
                <div>
                    <h4 class="fw-bold text-secondary-color mb-1">
                        {{ $user->name }}
                        <span class="fs-6 text-muted fw-normal ms-2">({{ $user->name_kana }})</span>
                    </h4>
                    <p class="text-muted mb-0 small">会員ID: #{{ $user->id }} | 登録日: {{ $user->created_at->format('Y年m月d日') }}</p>
                </div>
                <div>
                    @if($user->hasRole('admin'))
                        <span class="badge badge-admin px-3 py-2">システム管理</span>
                    @endif
                    @if($user->hasRole('kanji'))
                        <span class="badge badge-kanji px-3 py-2 text-dark">幹事</span>
                    @endif
                    @if($user->hasRole('general'))
                        <span class="badge badge-general px-3 py-2">一般会員</span>
                    @endif
                </div>
            </div>

            <div class="row g-3">
                <!-- 基本情報 -->
                <div class="col-md-6 border-end-md pe-md-4">
                    <h5 class="fw-bold text-secondary-color mb-3">📋 基本連絡先</h5>
                    
                    <div class="mb-3">
                        <span class="text-muted d-block small">メールアドレス (ログインID)</span>
                        <span class="fw-bold text-dark">{{ $user->email }}</span>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted d-block small">LINEアカウント名</span>
                        <span class="fw-bold text-primary-color" style="font-size: 1.1rem;">💬 {{ $user->line_display_name }}</span>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted d-block small">紹介者</span>
                        <span class="fw-bold text-dark">
                            @if($user->referrer)
                                <a href="{{ route('users.show', $user->referrer) }}" class="text-decoration-none">{{ $user->referrer->name }}</a>
                            @elseif($user->referrer_text)
                                <span class="text-warning-emphasis">{{ $user->referrer_text }}</span>
                            @else
                                自己応募 / 紹介なし
                            @endif
                        </span>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted d-block small">アカウント承認者</span>
                        <span class="fw-bold text-dark">
                            @if($user->approver)
                                <a href="{{ route('users.show', $user->approver) }}" class="text-decoration-none">{{ $user->approver->name }}</a>
                            @else
                                システム自動承認
                            @endif
                        </span>
                        @if($user->approved_at)
                            <span class="text-muted small d-block">({{ $user->approved_at->format('Y-m-d H:i') }})</span>
                        @endif
                    </div>
                </div>

                <!-- 出自・スキル -->
                <div class="col-md-6 ps-md-4">
                    <h5 class="fw-bold text-secondary-color mb-3">🔨 出自 ＆ 専門スキル</h5>

                    <div class="mb-3">
                        <span class="text-muted d-block small">本業・職業</span>
                        <span class="fw-bold text-dark">{{ $user->profession }}</span>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted d-block small">地域所属・団体</span>
                        <span class="fw-bold text-dark">{{ $user->affiliation ?: '所属なし' }}</span>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted d-block small">得意分野・保有スキル</span>
                        @if(is_array($user->skills) && count($user->skills) > 0)
                            <div class="mt-1 d-flex flex-wrap gap-1">
                                @foreach($user->skills as $skill)
                                    <span class="badge bg-light text-dark border px-2 py-1.5">{{ $skill }}</span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted small">登録されているスキルはありません</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- 年度別の活動履歴 -->
            <div class="col-md-6 mb-4">
                <div class="card p-4 shadow-sm border-0 h-100">
                    <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">🔄 年度別所属履歴</h5>
                    @if($userYears->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($userYears as $uy)
                                <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark">{{ $uy->fiscal_year }}年</span>
                                        <span class="text-muted small">第{{ $uy->fiscal_year - 1993 }}回宿場まつり</span>
                                    </div>
                                    <div>
                                        @foreach($uy->roles as $role)
                                            @if($role === 'admin')
                                                <span class="badge badge-admin badge-sm">システム管理</span>
                                            @elseif($role === 'kanji')
                                                <span class="badge badge-kanji badge-sm text-dark">幹事</span>
                                            @else
                                                <span class="badge badge-general badge-sm">一般会員</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small mb-0 py-3 text-center bg-light rounded">年度別所属履歴はありません。</p>
                    @endif
                </div>
            </div>

            <!-- 登録パスキー一覧 -->
            <div class="col-md-6 mb-4">
                <div class="card p-4 shadow-sm border-0 h-100">
                    <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">🔑 登録済みパスキーデバイス</h5>
                    @if($user->webAuthnKeys->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($user->webAuthnKeys as $key)
                                <div class="list-group-item px-0 py-2.5">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="fw-bold text-dark d-block">📱 {{ $key->device_name }}</span>
                                            <span class="text-muted small d-block" style="font-size: 0.8em;">登録日: {{ $key->created_at->format('Y-m-d') }}</span>
                                            @if($key->last_used_at)
                                                <span class="text-muted small d-block" style="font-size: 0.8em;">最終使用: {{ $key->last_used_at->format('Y-m-d H:i') }}</span>
                                            @endif
                                        </div>
                                        
                                        <!-- 管理者のみパスキー無効化ボタンを表示 -->
                                        @if(Auth::user()->isSystemAdmin())
                                            <form action="{{ route('admin.users.passkey-delete', ['user' => $user, 'key' => $key]) }}" method="POST" onsubmit="return confirm('本当にこのパスキーを無効化しますか？');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-xs py-1 px-2" style="font-size: 0.75rem;">
                                                    無効化
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small mb-0 py-3 text-center bg-light rounded">パスキーは登録されていません。</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- 管理アクションエリア (閲覧者が管理者の場合のみ表示) -->
        @if(Auth::user()->isSystemAdmin() && $user->id !== Auth::id())
            <div class="card border-danger border-1 shadow-sm mb-4">
                <div class="card-header bg-danger-subtle text-danger-emphasis py-2.5 fw-bold">
                    🛡️ 管理者アクション（アカウント操作）
                </div>
                <div class="card-body d-flex gap-3 flex-wrap">
                    <form action="{{ route('admin.users.passkey-session', $user) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary">
                            🔑 パスキー登録URLを再発行
                        </button>
                    </form>
                    
                    <form action="{{ route('admin.users.delete', $user) }}" method="POST" onsubmit="return confirm('本当にこのユーザー（{{ $user->name }}）を完全に削除しますか？');" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            🗑️ 会員を完全に削除する
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <div class="d-flex justify-content-between mt-2">
            @if(Auth::user()->isSystemAdmin() || Auth::user()->isKanji())
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary px-4">
                    ⬅ 会員一覧へ戻る
                </a>
            @else
                <a href="{{ route('mypage') }}" class="btn btn-outline-secondary px-4">
                    ⬅ マイページへ戻る
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
