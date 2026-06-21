@extends('layouts.app')

@section('title', 'マイページ')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <h3 class="fw-bold text-dark mb-4">マイページ</h3>
        
        <div class="row">
            <!-- 左カラム: 登録情報の確認 -->
            <div class="col-lg-6 mb-4">
                <div class="card p-4 shadow-sm border-0 h-100">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                        <h5 class="fw-bold text-secondary-color mb-0">👤 アカウント情報</h5>
                        <a href="{{ route('users.show', $user) }}" class="btn btn-outline-primary btn-sm">詳細プロフィールを表示</a>
                    </div>
                    
                    <table class="table table-borderless align-middle">
                        <tbody>
                            <tr>
                                <th class="text-muted w-35 py-2 small">氏名 (かな)</th>
                                <td class="py-2 fw-semibold">{{ $user->name }} ({{ $user->name_kana }})</td>
                            </tr>
                            <tr>
                                <th class="text-muted py-2 small">メールアドレス</th>
                                <td class="py-2">{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted py-2 small">本業・職業</th>
                                <td class="py-2">{{ $user->profession }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted py-2 small">所属団体</th>
                                <td class="py-2">{{ $user->affiliation ?: 'なし' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted py-2 small">LINEアカウント名</th>
                                <td class="py-2 fw-semibold text-primary-color">{{ $user->line_display_name }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted py-2 small">得意分野</th>
                                <td class="py-2">
                                    @if(is_array($user->skills) && count($user->skills) > 0)
                                        @foreach($user->skills as $skill)
                                            <span class="badge bg-secondary me-1">{{ $skill }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted small">登録なし</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted py-2 small">紹介者</th>
                                <td class="py-2">
                                    @if($user->referrer)
                                        {{ $user->referrer->name }} 様
                                    @elseif($user->referrer_text)
                                        {{ $user->referrer_text }} （紹介者リスト未登録）
                                    @else
                                        <span class="text-muted small">自己応募・なし</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3 mt-3">📅 承認履歴</h5>
                    <table class="table table-borderless align-middle mb-0">
                        <tbody>
                            <tr>
                                <th class="text-muted w-35 py-2 small">ステータス</th>
                                <td class="py-2"><span class="badge bg-success">正式会員（在籍）</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted py-2 small">承認担当者</th>
                                <td class="py-2">{{ $user->approver ? $user->approver->name . ' 様' : 'システム自動承認' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted py-2 small">承認日時</th>
                                <td class="py-2 text-muted">{{ $user->approved_at ? $user->approved_at->format('Y-m-d H:i') : '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- 右カラム: パスワード変更 & パスキー確認 -->
            <div class="col-lg-6 mb-4">
                <!-- パスワード変更カード -->
                <div class="card p-4 shadow-sm border-0 mb-4">
                    <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">🔑 パスワードの変更</h5>
                    
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('mypage.password') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="current_password" class="form-label small fw-semibold">現在のパスワード</label>
                            <input type="password" name="current_password" id="current_password" class="form-control border-secondary-subtle" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label small fw-semibold">新しいパスワード</label>
                            <input type="password" name="password" id="password" class="form-control border-secondary-subtle" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label small fw-semibold">新しいパスワード（確認用）</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control border-secondary-subtle" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">パスワードを変更する</button>
                        </div>
                    </form>
                </div>
                
                <!-- 登録済みのパスキー一覧 (閲覧のみ) -->
                <div class="card p-4 shadow-sm border-0">
                    <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">🛡️ 登録済みのパスキー (生体認証)</h5>
                    
                    @php
                        $keys = Auth::user()->webAuthnKeys;
                    @endphp
                    
                    @if($keys->count() > 0)
                        <div class="list-group list-group-flush mb-3">
                            @foreach($keys as $key)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <span class="d-block fw-semibold text-dark">📱 {{ $key->device_name }}</span>
                                        <span class="text-muted small d-block">登録日時: {{ $key->created_at->format('Y-m-d H:i') }}</span>
                                        @if($key->last_used_at)
                                            <span class="text-muted small d-block">最終使用: {{ $key->last_used_at->format('Y-m-d H:i') }}</span>
                                        @endif
                                    </div>
                                    <span class="badge bg-light text-dark border small">アクティブ</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted bg-light rounded mb-3">
                            <p class="mb-0 small">パスキーが登録されていません。</p>
                        </div>
                    @endif
                    
                    <div class="alert alert-info py-2 px-3 mb-0 small" role="alert">
                        <p class="mb-0 fw-semibold text-dark">⚠️ パスキーの追加・削除について</p>
                        <p class="mb-0 text-muted mt-1" style="font-size: 0.85em;">本システムでは、セキュリティ管理上の規定により、パスキーの追加登録や削除はシステム管理者のみが操作可能となっています。新しいスマートフォンの追加や、デバイスを紛失した場合は、お手数ですがシステム管理者（幹事）へお申し出ください。</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
