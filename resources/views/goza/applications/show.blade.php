@extends('layouts.app')

@section('title', '出店応募詳細')

@section('content')
<div class="mb-4">
    <a href="{{ route('goza.applications.index') }}" class="btn btn-outline-secondary btn-sm mb-2">← 応募一覧に戻る</a>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold text-dark mb-1">出店応募詳細</h3>
            <p class="text-muted small">屋号: {{ $app->shop_name }} の応募情報と料金内訳</p>
        </div>
        <div>
            <a href="{{ route('goza.applications.edit', $app->id) }}" class="btn btn-primary px-4">
                応募情報の編集
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- 詳細情報 -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h5 class="fw-bold text-dark mb-0">応募者基本情報</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered align-middle">
                    <tbody>
                        <tr>
                            <th class="w-25 bg-light fw-bold text-secondary">屋号・団体名</th>
                            <td class="fw-bold">{{ $app->shop_name }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light fw-bold text-secondary">出店者氏名</th>
                            <td>{{ $app->exhibitor_name }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light fw-bold text-secondary">商店街加盟状況</th>
                            <td>
                                @if($app->is_member)
                                    <span class="badge bg-success">加盟店</span>
                                @else
                                    <span class="badge bg-secondary">一般（非加盟）</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light fw-bold text-secondary">紹介者情報</th>
                            <td>
                                @if($app->introducer_name)
                                    <strong>紹介者:</strong> {{ $app->introducer_name }} <br>
                                    <strong>連絡先:</strong> {{ $app->introducer_contact ?? 'なし' }}
                                @else
                                    <span class="text-muted">紹介者なし</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light fw-bold text-secondary">希望区画数</th>
                            <td>
                                <span class="badge bg-primary fs-6">{{ $app->section_count }} 区画</span>
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light fw-bold text-secondary">区画種類</th>
                            <td>
                                <strong>1区画目:</strong> 
                                @if($app->first_section_type === 'general') 一般（物販）
                                @elseif($app->first_section_type === 'A') A（火器なし食品）
                                @elseif($app->first_section_type === 'B') B（火器使用飲食）
                                @endif
                                <br>
                                @if($app->section_count > 1)
                                    <strong>2区画目以降:</strong> 
                                    @if($app->subsequent_section_type === 'general') 一般（物販）
                                    @elseif($app->subsequent_section_type === 'A') A（火器なし食品）
                                    @elseif($app->subsequent_section_type === 'B') B（火器使用飲食）
                                    @endif
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h5 class="fw-bold text-dark mb-0">火気・食品の取扱</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered align-middle">
                    <tbody>
                        <tr>
                            <th class="w-25 bg-light fw-bold text-secondary">火気使用</th>
                            <td>
                                @if($app->has_fire)
                                    <span class="badge bg-danger mb-2">有</span>
                                    <ul class="mb-0 small">
                                        <li><strong>使用器具:</strong> {{ $app->fire_equipment }}</li>
                                        <li><strong>台数:</strong> {{ $app->fire_equipment_count }} 台</li>
                                        <li><strong>使用燃料:</strong> {{ $app->fire_fuel }}</li>
                                    </ul>
                                @else
                                    <span class="badge bg-secondary">無</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light fw-bold text-secondary">食品取扱</th>
                            <td>
                                @if($app->has_food)
                                    <span class="badge bg-warning text-dark mb-2">有</span>
                                    <div>
                                        @if($app->has_food_pledge)
                                            <span class="text-success small fw-bold">✓ 保健所指導および食品表示法遵守への同意済み</span>
                                        @else
                                            <span class="text-danger small fw-bold">✗ 同意未完了</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="badge bg-secondary">無</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 料金内訳・ステータス -->
    <div class="col-lg-4">
        <!-- 応募ステータス -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h5 class="fw-bold text-dark mb-0">応募ステータス</h5>
            </div>
            <div class="card-body">
                <div class="mb-3 text-center">
                    @if($app->status === 'accepted')
                        <span class="badge bg-success px-4 py-2 fs-5">当選（出店許可）</span>
                    @elseif($app->status === 'rejected')
                        <span class="badge bg-danger px-4 py-2 fs-5">落選</span>
                    @elseif($app->status === 'submitted')
                        <span class="badge bg-primary px-4 py-2 fs-5">応募済</span>
                    @else
                        <span class="badge bg-secondary px-4 py-2 fs-5">下書き</span>
                    @endif
                </div>
                
                <form action="{{ route('goza.applications.updateStatus', $app->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="status" class="form-label small fw-bold">ステータス変更</label>
                        <select name="status" id="status" class="form-select border-secondary-subtle">
                            <option value="draft" {{ $app->status === 'draft' ? 'selected' : '' }}>下書き</option>
                            <option value="submitted" {{ $app->status === 'submitted' ? 'selected' : '' }}>応募済</option>
                            <option value="accepted" {{ $app->status === 'accepted' ? 'selected' : '' }}>当選（許可）</option>
                            <option value="rejected" {{ $app->status === 'rejected' ? 'selected' : '' }}>落選</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">ステータスを更新する</button>
                </form>
            </div>
        </div>

        <!-- 料金・集金サマリー -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h5 class="fw-bold text-dark mb-0">料金・集金内訳</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="text-muted small">総合計金額</span>
                    <div class="fs-2 fw-bold text-primary-color">
                        {{ number_format($app->total_fee) }} <span class="fs-6 text-muted">円</span>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1 small">
                        <span>① 出店料:</span>
                        <span class="fw-bold">{{ number_format($app->exhibition_fee) }} 円</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1 small">
                        <span>② 備品貸出料:</span>
                        <span class="fw-bold">
                            {{ number_format($app->equipment_fee_override !== null ? $app->equipment_fee_override : $app->equipment_fee) }} 円
                            @if($app->equipment_fee_override !== null)
                                <br><span class="text-danger" style="font-size: 0.75rem;">(手動上書き調整適用前: {{ number_format($app->equipment_fee) }} 円)</span>
                            @endif
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-1 small">
                        <span>③ ゴミ袋料:</span>
                        <span class="fw-bold">{{ number_format($app->trash_bag_fee) }} 円</span>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    @php
                        $rentals = $app->rentals ?: [];
                    @endphp
                    <span class="fw-bold small text-secondary">希望備品内訳:</span>
                    <ul class="mb-0 small text-muted mt-1" style="padding-left: 1.25rem;">
                        <li>テント: {{ $rentals['tent'] ?? 0 }} 張</li>
                        <li>ウエイト: {{ $rentals['weight'] ?? 0 }} 個</li>
                        <li>机: {{ $rentals['desk'] ?? 0 }} 台</li>
                        <li>椅子: {{ $rentals['chair'] ?? 0 }} 脚</li>
                        <li>ゴミ袋 45L: {{ $rentals['trash_bag_45'] ?? 0 }} 枚 @if(!$app->is_member) (内 2 枚無料枠) @endif</li>
                        <li>ゴミ袋 70L: {{ $rentals['trash_bag_70'] ?? 0 }} 枚</li>
                    </ul>
                </div>

                <hr>

                <div>
                    <span class="small d-block mb-2 text-secondary fw-bold">集金ステータス:</span>
                    @if($app->is_paid)
                        <div class="alert alert-success py-2 px-3 mb-0 small">
                            🟢 <strong>受領完了</strong> <br>
                            <span style="font-size: 0.75rem;">受領日時: {{ $app->payment_received_at->format('Y/m/d H:i') }}</span>
                            <div class="mt-2">
                                <a href="{{ route('goza.payments.receipt', $app->id) }}" target="_blank" class="btn btn-sm btn-outline-success w-100 mb-1">領収書印刷</a>
                                <a href="{{ route('goza.payments.permit', $app->id) }}" target="_blank" class="btn btn-sm btn-outline-dark w-100">出店許可証印刷</a>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning py-2 px-3 mb-0 small">
                            🔴 <strong>未受領</strong>
                            @if($app->status === 'accepted')
                                <div class="mt-2">
                                    <a href="{{ route('goza.payments.index', ['search' => $app->shop_name]) }}" class="btn btn-sm btn-warning w-100">当日集金画面へ</a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
