@extends('layouts.app')

@section('title', '登録承認待ち一覧')

@section('content')
<div class="row">
    <div class="col-12">
        <h3 class="fw-bold text-dark mb-1">新規実行委員 承認待ち一覧</h3>
        <p class="text-muted small mb-4">実行委員会に新規登録申請中の仮会員です。出自や紹介関係を確認の上、承認または却下を行ってください。</p>

        <!-- 承認完了直後のワンタイム登録URL表示エリア -->
        @if(session('register_url'))
            <div class="card border-success border-2 shadow-sm mb-4">
                <div class="card-header bg-success text-white py-3 fw-bold">
                    🎉 会員「{{ session('approved_user_name') }}」の承認が完了しました
                </div>
                <div class="card-body">
                    <p class="small text-dark fw-semibold mb-2">新規会員のデバイス登録用ワンタイムURLを発行しました：</p>
                    
                    <div class="input-group mb-3">
                        <input type="text" id="admin-copy-url" class="form-control text-success-emphasis border-success bg-light" 
                               value="{{ session('register_url') }}" readonly>
                        <button class="btn btn-success" type="button" id="btn-admin-copy-url">
                            📋 URLをコピー
                        </button>
                    </div>
                    
                    <div class="alert alert-warning py-2 mb-0 small" role="alert">
                        <strong>⚠️ 注意:</strong> この登録用URLはセキュアなワンタイムURLであり、<strong>有効期限は24時間</strong>です。LINE等で新規会員本人へ直接共有し、指紋・顔認証（パスキー）の初期設定を行うようお伝えください。
                    </div>
                </div>
            </div>
        @endif

        <div class="card p-4 shadow-sm border-0">
            @if($pendingUsers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>氏名 (かな)</th>
                                <th>LINEアカウント名</th>
                                <th>出自 (本業 / 所属)</th>
                                <th>紹介者</th>
                                <th>申請日時</th>
                                <th class="text-center">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingUsers as $user)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $user->name }}</div>
                                        <div class="text-muted small">{{ $user->name_kana }}</div>
                                        <span class="badge bg-secondary-subtle text-secondary small" style="font-size: 0.75em;">{{ $user->email }}</span>
                                    </td>
                                    <td class="fw-semibold text-primary-color">
                                        {{ $user->line_display_name }}
                                    </td>
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
                                    <td>
                                        @if($user->referrer)
                                            <span class="fw-semibold">{{ $user->referrer->name }}</span>
                                            <span class="text-muted small d-block" style="font-size: 0.8em;">(本業: {{ $user->referrer->profession }})</span>
                                        @elseif($user->referrer_text)
                                            <span class="text-warning-emphasis fw-semibold">{{ $user->referrer_text }}</span>
                                            <span class="text-muted small d-block" style="font-size: 0.8em;">(会員データ未紐付け)</span>
                                        @else
                                            <span class="text-muted small">なし(自己応募)</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="small text-muted">{{ $user->created_at->format('Y-m-d H:i') }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2 justify-content-center">
                                            <!-- 承認モーダルをトリガー (インラインスクリプトなし) -->
                                            <button type="button" class="btn btn-success btn-sm px-3" data-bs-toggle="modal" data-bs-target="#approveModal-{{ $user->id }}">
                                                承認
                                            </button>
                                            
                                            <!-- 却下フォーム -->
                                            <form action="{{ route('admin.users.reject', $user) }}" method="POST" onsubmit="return confirm('本当に申請を却下しますか？');" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    却下
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- 承認モーダル (ユーザーごとに一意のIDで生成) -->
                                <div class="modal fade" id="approveModal-{{ $user->id }}" data-bs-backdrop="static" tabindex="-1" aria-labelledby="approveLabel-{{ $user->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.users.approve', $user) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold" id="approveLabel-{{ $user->id }}">会員の承認・権限設定</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="alert alert-light border small text-muted mb-4">
                                                        申請者: <strong>{{ $user->name }}</strong> (本業: {{ $user->profession }})<br>
                                                        紹介者: {{ $user->referrer ? $user->referrer->name : ($user->referrer_text ?: 'なし') }}
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small text-dark d-block">付与する会員属性・役割（ロール）</label>
                                                        <div class="form-text small mb-3">承認されたユーザーは、選択された属性に応じた画面にアクセスできるようになります（複数選択可）。</div>
                                                        
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" name="roles[]" value="general" id="role-general-{{ $user->id }}" checked>
                                                            <label class="form-check-label fw-bold text-success" for="role-general-{{ $user->id }}">
                                                                一般会員 (実行委員の基本メンバー)
                                                            </label>
                                                        </div>
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" name="roles[]" value="kanji" id="role-kanji-{{ $user->id }}" {{ $user->hasRole('kanji') ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-bold text-warning-emphasis" for="role-kanji-{{ $user->id }}">
                                                                幹事 (幹事会への参加、会議スケジュール・議事録の作成権限)
                                                            </label>
                                                        </div>
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" name="roles[]" value="admin" id="role-admin-{{ $user->id }}" {{ $user->hasRole('admin') ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-bold text-danger" for="role-admin-{{ $user->id }}">
                                                                システム管理 (新規会員の承認、パスキーの発行・無効化の最上位権限)
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                                                    <button type="submit" class="btn btn-success">承認してワンタイムURLを発行</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <p class="mb-0">承認申請中の新規メンバーはいません。</p>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
