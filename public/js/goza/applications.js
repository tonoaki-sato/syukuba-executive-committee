document.addEventListener('DOMContentLoaded', function() {
    const sectionCountSelect = document.getElementById('section_count');
    const subsequentContainer = document.getElementById('subsequent_section_container');
    const subsequentSelect = document.getElementById('subsequent_section_type');

    function toggleSubsequent() {
        if (sectionCountSelect && subsequentContainer && subsequentSelect) {
            if (parseInt(sectionCountSelect.value) >= 2) {
                subsequentContainer.style.display = 'block';
                subsequentSelect.setAttribute('required', 'required');
            } else {
                subsequentContainer.style.display = 'none';
                subsequentSelect.removeAttribute('required');
            }
        }
    }
    if (sectionCountSelect) {
        sectionCountSelect.addEventListener('change', toggleSubsequent);
        toggleSubsequent();
    }

    const hasFireYes = document.getElementById('has_fire_yes');
    const hasFireNo = document.getElementById('has_fire_no');
    const fireFields = document.getElementById('fire_fields');
    const fireEquipmentInput = document.getElementById('fire_equipment');
    const fireEquipmentCountInput = document.getElementById('fire_equipment_count');
    const fireFuelInput = document.getElementById('fire_fuel');
    
    function toggleFire() {
        if (hasFireYes && fireFields && fireEquipmentInput && fireEquipmentCountInput && fireFuelInput) {
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
    }
    if (hasFireYes && hasFireNo) {
        hasFireYes.addEventListener('change', toggleFire);
        hasFireNo.addEventListener('change', toggleFire);
        toggleFire();
    }

    const hasFoodYes = document.getElementById('has_food_yes');
    const hasFoodNo = document.getElementById('has_food_no');
    const foodFields = document.getElementById('food_fields');
    const foodPledgeCheckbox = document.getElementById('has_food_pledge');

    function toggleFood() {
        if (hasFoodYes && foodFields && foodPledgeCheckbox) {
            if (hasFoodYes.checked) {
                foodFields.style.display = 'block';
                foodPledgeCheckbox.setAttribute('required', 'required');
            } else {
                foodFields.style.display = 'none';
                foodPledgeCheckbox.removeAttribute('required');
            }
        }
    }
    if (hasFoodYes && hasFoodNo) {
        hasFoodYes.addEventListener('change', toggleFood);
        hasFoodNo.addEventListener('change', toggleFood);
        toggleFood();
    }

    const form = document.getElementById('appForm');
    const generatorAlert = document.getElementById('generator_alert');
    
    function checkGenerator() {
        if (!fireEquipmentInput || !fireFuelInput || !generatorAlert) return false;
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
    
    if (fireEquipmentInput) {
        fireEquipmentInput.addEventListener('input', checkGenerator);
    }
    if (fireFuelInput) {
        fireFuelInput.addEventListener('input', checkGenerator);
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            if (hasFireYes && hasFireYes.checked && checkGenerator()) {
                e.preventDefault();
                alert('発電機は使用できません。記入内容を修正してください。');
            }
        });
    }
});
