@extends('layouts.app')

@section('title', '出店応募 編集')

@section('content')
<div class="mb-4">
    <a href="{{ route('goza.applications.show', $app->id) }}" class="btn btn-outline-secondary btn-sm mb-2">← 詳細に戻る</a>
    <h3 class="fw-bold text-dark">出店応募 編集</h3>
    <p class="text-muted small">応募情報を変更・更新します。</p>
</div>

<!-- バリデーションエラー -->
@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0 small">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card border-0 shadow-sm p-4 mb-5">
    <form action="{{ route('goza.applications.update', $app->id) }}" method="POST" id="appForm">
        @csrf
        @method('PUT')

        <!-- 出店基本情報 -->
        <h5 class="fw-bold text-dark border-bottom pb-2 mb-4">1. 基本情報</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label for="shop_name" class="form-label fw-bold">屋号・団体名 <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="shop_name" name="shop_name" value="{{ old('shop_name', $app->shop_name) }}" required>
            </div>
            <div class="col-md-6">
                <label for="exhibitor_name" class="form-label fw-bold">出店者氏名 <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="exhibitor_name" name="exhibitor_name" value="{{ old('exhibitor_name', $app->exhibitor_name) }}" required>
            </div>
            
            <div class="col-md-6">
                <label class="form-label fw-bold d-block">商店街加盟状況 <span class="text-danger">*</span></label>
                <div class="form-check form-check-inline mt-2">
                    <input class="form-check-input" type="radio" name="is_member" id="is_member_yes" value="1" {{ old('is_member', $app->is_member ? '1' : '0') === '1' ? 'checked' : '' }} required>
                    <label class="form-check-label" for="is_member_yes">加盟店</label>
                </div>
                <div class="form-check form-check-inline mt-2">
                    <input class="form-check-input" type="radio" name="is_member" id="is_member_no" value="0" {{ old('is_member', $app->is_member ? '1' : '0') === '0' ? 'checked' : '' }} required>
                    <label class="form-check-label" for="is_member_no">なし（一般）</label>
                </div>
            </div>

            <div class="col-md-3">
                <label for="introducer_name" class="form-label fw-bold">紹介者名</label>
                <input type="text" class="form-control" id="introducer_name" name="introducer_name" value="{{ old('introducer_name', $app->introducer_name) }}">
            </div>
            <div class="col-md-3">
                <label for="introducer_contact" class="form-label fw-bold">紹介者連絡先</label>
                <input type="text" class="form-control" id="introducer_contact" name="introducer_contact" value="{{ old('introducer_contact', $app->introducer_contact) }}">
            </div>
        </div>

        <!-- 希望区画情報 -->
        <h5 class="fw-bold text-dark border-bottom pb-2 mb-4">2. 希望区画</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label for="section_count" class="form-label fw-bold">希望区画数 <span class="text-danger">*</span></label>
                <select name="section_count" id="section_count" class="form-select" required>
                    <option value="1" {{ old('section_count', $app->section_count) == 1 ? 'selected' : '' }}>1区画</option>
                    <option value="2" {{ old('section_count', $app->section_count) == 2 ? 'selected' : '' }}>2区画</option>
                    <option value="3" {{ old('section_count', $app->section_count) == 3 ? 'selected' : '' }}>3区画</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="first_section_type" class="form-label fw-bold">1区画目の種類 <span class="text-danger">*</span></label>
                <select name="first_section_type" id="first_section_type" class="form-select" required>
                    <option value="general" {{ old('first_section_type', $app->first_section_type) === 'general' ? 'selected' : '' }}>一般（物販）</option>
                    <option value="A" {{ old('first_section_type', $app->first_section_type) === 'A' ? 'selected' : '' }}>A（火器なし食品）</option>
                    <option value="B" {{ old('first_section_type', $app->first_section_type) === 'B' ? 'selected' : '' }}>B（火器使用飲食）</option>
                </select>
            </div>
            <div class="col-md-4" id="subsequent_section_container" style="display: none;">
                <label for="subsequent_section_type" class="form-label fw-bold">2区画目以降の種類 <span class="text-danger">*</span></label>
                <select name="subsequent_section_type" id="subsequent_section_type" class="form-select">
                    <option value="general" {{ old('subsequent_section_type', $app->subsequent_section_type) === 'general' ? 'selected' : '' }}>一般（物販）</option>
                    <option value="A" {{ old('subsequent_section_type', $app->subsequent_section_type) === 'A' ? 'selected' : '' }}>A（火器なし食品）</option>
                    <option value="B" {{ old('subsequent_section_type', $app->subsequent_section_type) === 'B' ? 'selected' : '' }}>B（火器使用飲食）</option>
                </select>
            </div>
        </div>

        <!-- 火気・食品取扱 -->
        <h5 class="fw-bold text-dark border-bottom pb-2 mb-4">3. 火気・食品の取扱</h5>
        <div class="row g-3 mb-4">
            <!-- 火気使用 -->
            <div class="col-md-6 border-end">
                <label class="form-label fw-bold d-block">火気・燃焼器具使用有無 <span class="text-danger">*</span></label>
                <div class="form-check form-check-inline mt-2">
                    <input class="form-check-input" type="radio" name="has_fire" id="has_fire_yes" value="1" {{ old('has_fire', $app->has_fire ? '1' : '0') === '1' ? 'checked' : '' }} required>
                    <label class="form-check-label" for="has_fire_yes">有</label>
                </div>
                <div class="form-check form-check-inline mt-2">
                    <input class="form-check-input" type="radio" name="has_fire" id="has_fire_no" value="0" {{ old('has_fire', $app->has_fire ? '1' : '0') === '0' ? 'checked' : '' }} required>
                    <label class="form-check-label" for="has_fire_no">無</label>
                </div>

                <div id="fire_fields" class="mt-3 p-3 bg-light border rounded" style="display: none;">
                    <div class="alert alert-warning py-1 px-2 small mb-3">
                        ⚠️ <strong>注意:</strong> 発電機は使用不可です。
                    </div>
                    <div class="mb-2">
                        <label for="fire_equipment" class="form-label small fw-bold">使用器具名 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="fire_equipment" name="fire_equipment" value="{{ old('fire_equipment', $app->fire_equipment) }}" placeholder="例: ガスコンロ、電気プレート等">
                        <div id="generator_alert" class="text-danger small mt-1" style="display: none;">「発電機」は記入できません。</div>
                    </div>
                    <div class="mb-2">
                        <label for="fire_equipment_count" class="form-label small fw-bold">使用器具台数 <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-sm" id="fire_equipment_count" name="fire_equipment_count" value="{{ old('fire_equipment_count', $app->fire_equipment_count) }}" min="0">
                    </div>
                    <div>
                        <label for="fire_fuel" class="form-label small fw-bold">使用燃料 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="fire_fuel" name="fire_fuel" value="{{ old('fire_fuel', $app->fire_fuel) }}" placeholder="例: プロパンガス、カセットガス、電気等">
                    </div>
                </div>
            </div>

            <!-- 食品取扱 -->
            <div class="col-md-6">
                <label class="form-label fw-bold d-block">食品取扱有無 <span class="text-danger">*</span></label>
                <div class="form-check form-check-inline mt-2">
                    <input class="form-check-input" type="radio" name="has_food" id="has_food_yes" value="1" {{ old('has_food', $app->has_food ? '1' : '0') === '1' ? 'checked' : '' }} required>
                    <label class="form-check-label" for="has_food_yes">有</label>
                </div>
                <div class="form-check form-check-inline mt-2">
                    <input class="form-check-input" type="radio" name="has_food" id="has_food_no" value="0" {{ old('has_food', $app->has_food ? '1' : '0') === '0' ? 'checked' : '' }} required>
                    <label class="form-check-label" for="has_food_no">無</label>
                </div>

                <div id="food_fields" class="mt-3 p-3 bg-light border rounded" style="display: none;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="has_food_pledge" id="has_food_pledge" value="1" {{ old('has_food_pledge', $app->has_food_pledge ? '1' : '0') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label small fw-bold text-danger" for="has_food_pledge">
                            保健所指導および食品表示法を遵守することに同意する <span class="text-danger">*</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- 貸出希望備品 -->
        @php
            $rentals = $app->rentals ?: [];
        @endphp
        <h5 class="fw-bold text-dark border-bottom pb-2 mb-4">4. 貸出希望備品・ゴミ袋</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <label for="tent" class="form-label small fw-bold">テント希望数</label>
                <input type="number" class="form-control" id="tent" name="tent" value="{{ old('tent', $rentals['tent'] ?? 0) }}" min="0">
            </div>
            <div class="col-md-2">
                <label for="weight" class="form-label small fw-bold">ウエイト希望数</label>
                <input type="number" class="form-control" id="weight" name="weight" value="{{ old('weight', $rentals['weight'] ?? 0) }}" min="0">
            </div>
            <div class="col-md-2">
                <label for="desk" class="form-label small fw-bold">机希望数</label>
                <input type="number" class="form-control" id="desk" name="desk" value="{{ old('desk', $rentals['desk'] ?? 0) }}" min="0">
            </div>
            <div class="col-md-2">
                <label for="chair" class="form-label small fw-bold">椅子希望数</label>
                <input type="number" class="form-control" id="chair" name="chair" value="{{ old('chair', $rentals['chair'] ?? 0) }}" min="0">
            </div>
            <div class="col-md-2">
                <label for="trash_bag_45" class="form-label small fw-bold">ゴミ袋 45L 希望数</label>
                <input type="number" class="form-control" id="trash_bag_45" name="trash_bag_45" value="{{ old('trash_bag_45', $rentals['trash_bag_45'] ?? 0) }}" min="0">
            </div>
            <div class="col-md-2">
                <label for="trash_bag_70" class="form-label small fw-bold">ゴミ袋 70L 希望数</label>
                <input type="number" class="form-control" id="trash_bag_70" name="trash_bag_70" value="{{ old('trash_bag_70', $rentals['trash_bag_70'] ?? 0) }}" min="0">
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 border-top pt-4">
            <a href="{{ route('goza.applications.show', $app->id) }}" class="btn btn-outline-secondary px-4">キャンセル</a>
            <button type="submit" class="btn btn-primary px-5" id="submitBtn">更新する</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sectionCountSelect = document.getElementById('section_count');
        const subsequentContainer = document.getElementById('subsequent_section_container');
        const subsequentSelect = document.getElementById('subsequent_section_type');

        function toggleSubsequent() {
            if (parseInt(sectionCountSelect.value) >= 2) {
                subsequentContainer.style.display = 'block';
                subsequentSelect.setAttribute('required', 'required');
            } else {
                subsequentContainer.style.display = 'none';
                subsequentSelect.removeAttribute('required');
            }
        }
        sectionCountSelect.addEventListener('change', toggleSubsequent);
        toggleSubsequent();

        const hasFireYes = document.getElementById('has_fire_yes');
        const hasFireNo = document.getElementById('has_fire_no');
        const fireFields = document.getElementById('fire_fields');
        const fireEquipmentInput = document.getElementById('fire_equipment');
        const fireEquipmentCountInput = document.getElementById('fire_equipment_count');
        const fireFuelInput = document.getElementById('fire_fuel');
        
        function toggleFire() {
            if (hasFireYes.checked) {
                fireFields.style.display = 'block';
                fireEquipmentInput.setAttribute('required', 'required');
                fireEquipmentCountInput.setAttribute('required', 'required');
                fireFuelInput.setAttribute('required', 'required');
            } else {
                fireFields.style.display = 'none';
                fireEquipmentInput.removeAttribute('required');
                fireEquipmentCountInput.removeAttribute('required');
                fireFuelInput.removeAttribute('required');
            }
        }
        hasFireYes.addEventListener('change', toggleFire);
        hasFireNo.addEventListener('change', toggleFire);
        toggleFire();

        const hasFoodYes = document.getElementById('has_food_yes');
        const hasFoodNo = document.getElementById('has_food_no');
        const foodFields = document.getElementById('food_fields');
        const foodPledgeCheckbox = document.getElementById('has_food_pledge');

        function toggleFood() {
            if (hasFoodYes.checked) {
                foodFields.style.display = 'block';
                foodPledgeCheckbox.setAttribute('required', 'required');
            } else {
                foodFields.style.display = 'none';
                foodPledgeCheckbox.removeAttribute('required');
            }
        }
        hasFoodYes.addEventListener('change', toggleFood);
        hasFoodNo.addEventListener('change', toggleFood);
        toggleFood();

        const form = document.getElementById('appForm');
        const generatorAlert = document.getElementById('generator_alert');
        
        function checkGenerator() {
            const eqVal = fireEquipmentInput.value;
            const fuelVal = fireFuelInput.value;
            if (eqVal.includes('発電機') || fuelVal.includes('発電機')) {
                generatorAlert.style.display = 'block';
                return true;
            } else {
                generatorAlert.style.display = 'none';
                return false;
            }
        }
        
        fireEquipmentInput.addEventListener('input', checkGenerator);
        fireFuelInput.addEventListener('input', checkGenerator);

        form.addEventListener('submit', function(e) {
            if (hasFireYes.checked && checkGenerator()) {
                e.preventDefault();
                alert('発電機は使用できません。記入内容を修正してください。');
            }
        });
    });
</script>
@endsection
