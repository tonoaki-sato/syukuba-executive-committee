@extends('layouts.app')

@section('title', '募集設定・料金マスタ管理')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold text-dark mb-1">募集設定・料金マスタ管理（管理者限定）</h3>
    <p class="text-muted small">ござ市の募集スケジュールおよび各種料金単価を設定します。</p>
</div>

<!-- バリデーションエラーやステータスメッセージ -->
@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0 small">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm p-4">
            <form action="{{ route('goza.settings.update') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- 募集スケジュール設定 -->
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-4">1. 募集スケジュール</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="recruitment_start_at" class="form-label fw-bold">募集開始日時</label>
                        <input type="datetime-local" class="form-control border-secondary-subtle" id="recruitment_start_at" name="recruitment_start_at" value="{{ old('recruitment_start_at', $event->recruitment_start_at ? $event->recruitment_start_at->format('Y-m-d\TH:i') : '') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="recruitment_end_at" class="form-label fw-bold">募集締め切り日時</label>
                        <input type="datetime-local" class="form-control border-secondary-subtle" id="recruitment_end_at" name="recruitment_end_at" value="{{ old('recruitment_end_at', $event->recruitment_end_at ? $event->recruitment_end_at->format('Y-m-d\TH:i') : '') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="recruitment_status" class="form-label fw-bold">募集ステータス（手動制御）</label>
                        <select name="recruitment_status" id="recruitment_status" class="form-select border-secondary-subtle">
                            <option value="closed" {{ old('recruitment_status', $event->recruitment_status) === 'closed' ? 'selected' : '' }}>募集停止・クローズ</option>
                            <option value="open" {{ old('recruitment_status', $event->recruitment_status) === 'open' ? 'selected' : '' }}>募集中・オープン</option>
                        </select>
                    </div>
                </div>

                <!-- 料金マスタ設定 -->
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-4">2. 料金単価設定（年度マスタ）</h5>
                
                @php
                    $feesMap = $event->fees;
                    $labels = [
                        // 加盟
                        'member_1st' => '加盟店: 1区画目の出店料 (円)',
                        'member_general_2nd' => '加盟店: 2区画目以降の一般(物販)単価 (円)',
                        'member_A_2nd' => '加盟店: 2区画目以降のA(火器なし食品)単価 (円)',
                        'member_B_2nd' => '加盟店: 2区画目以降のB(火器使用飲食)単価 (円)',
                        // 一般（非加盟）
                        'general_1st' => '一般出店者: 1区画目の一般(物販)出店料 (円)',
                        'general_A_1st' => '一般出店者: 1区画目のA(火器なし食品)出店料 (円)',
                        'general_B_1st' => '一般出店者: 1区画目のB(火器使用飲食)出店料 (円)',
                        'general_2nd' => '一般出店者: 2区画目以降の一般(物販)単価 (円)',
                        'general_A_2nd' => '一般出店者: 2区画目以降のA(火器なし食品)単価 (円)',
                        'general_B_2nd' => '一般出店者: 2区画目以降のB(火器使用飲食)単価 (円)',
                        // 備品
                        'tent' => '備品: テント貸出料 (1張あたり) (円)',
                        'weight' => '備品: ウエイト貸出料 (1個あたり) (円)',
                        'desk' => '備品: 机貸出料 (1台あたり) (円)',
                        'chair' => '備品: 椅子貸出料 (1脚あたり) (円)',
                        // ゴミ袋
                        'trash_45' => 'ゴミ袋: 45L追加ゴミ袋単価 (1枚あたり) (円)',
                        'trash_70' => 'ゴミ袋: 70L追加ゴミ袋単価 (1枚あたり) (円)',
                    ];
                @endphp

                <div class="row g-3 mb-4">
                    @foreach($labels as $key => $label)
                        <div class="col-md-6">
                            <label for="fee_{{ $key }}" class="form-label small fw-bold text-secondary">{{ $label }}</label>
                            <input type="number" class="form-control border-secondary-subtle" id="fee_{{ $key }}" name="fees[{{ $key }}]" value="{{ old('fees.' . $key, $feesMap[$key] ?? '') }}" required min="0">
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-4">
                    <a href="{{ route('goza.index') }}" class="btn btn-outline-secondary px-4">キャンセル</a>
                    <button type="submit" class="btn btn-primary px-5">設定を保存する</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
