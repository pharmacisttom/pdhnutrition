/**
 * PDH Nutrition - Dynamic Real-Time NAF Calculator JS
 */

document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('nafForm');
  if (!form) return;

  const weightInput = document.getElementById('weight_kg');
  const heightInput = document.getElementById('height_cm');
  const armSpanInput = document.getElementById('arm_span_cm');
  const albuminInput = document.getElementById('albumin');
  const wbcInput = document.getElementById('wbc');
  const lymInput = document.getElementById('lymphocyte');

  const bmiDisplay = document.getElementById('calc_bmi');
  const bmiScoreDisplay = document.getElementById('calc_bmi_score');
  const albScoreDisplay = document.getElementById('calc_alb_score');
  const tlcDisplay = document.getElementById('calc_tlc');
  const tlcScoreDisplay = document.getElementById('calc_tlc_score');
  const totalScoreDisplay = document.getElementById('calc_total_score');
  const gradeBadge = document.getElementById('calc_naf_grade');

  function calculateScore() {
    let totalScore = 0;

    // 1. BMI Calculation
    const weight = parseFloat(weightInput.value) || 0;
    let height = parseFloat(heightInput.value) || 0;
    const armSpan = parseFloat(armSpanInput.value) || 0;

    if (!height && armSpan) height = armSpan;

    let bmi = 0;
    let bmiScore = 0;
    if (weight > 0 && height > 0) {
      const heightM = height / 100.0;
      bmi = (weight / (heightM * heightM)).toFixed(2);
      if (bmi < 17.0) bmiScore = 2;
      else if (bmi >= 17.0 && bmi <= 18.0) bmiScore = 1;
      else if (bmi >= 18.1 && bmi <= 29.9) bmiScore = 0;
      else if (bmi >= 30.0) bmiScore = 1;
    }

    if (bmiDisplay) bmiDisplay.textContent = bmi > 0 ? bmi : '-';
    if (bmiScoreDisplay) bmiScoreDisplay.textContent = bmiScore;
    totalScore += bmiScore;

    // 2. Albumin Score
    const albumin = parseFloat(albuminInput.value) || 0;
    let albScore = 0;
    if (albumin > 0) {
      if (albumin < 2.5) albScore = 3;
      else if (albumin >= 2.6 && albumin <= 2.9) albScore = 2;
      else if (albumin >= 3.0 && albumin <= 3.5) albScore = 1;
      else if (albumin > 3.5) albScore = 0;
    }
    if (albScoreDisplay) albScoreDisplay.textContent = albScore;
    totalScore += albScore;

    // 3. TLC Score
    const wbc = parseFloat(wbcInput.value) || 0;
    const lym = parseFloat(lymInput.value) || 0;
    let tlc = 0;
    let tlcScore = 0;
    if (wbc > 0 && lym > 0) {
      tlc = Math.round((wbc * lym) / 100.0);
      if (tlc <= 1000) tlcScore = 3;
      else if (tlc >= 1001 && tlc <= 1200) tlcScore = 2;
      else if (tlc >= 1201 && tlc <= 1500) tlcScore = 1;
      else if (tlc > 1500) tlcScore = 0;
    }
    if (tlcDisplay) tlcDisplay.textContent = tlc > 0 ? tlc : '-';
    if (tlcScoreDisplay) tlcScoreDisplay.textContent = tlcScore;
    totalScore += tlcScore;

    // 4. Section Items (Radios & Checkboxes)
    const checkedInputs = form.querySelectorAll('input[type="radio"]:checked, input[type="checkbox"]:checked');
    checkedInputs.forEach(input => {
      const score = parseInt(input.getAttribute('data-score')) || 0;
      totalScore += score;
    });

    // 5. Total Score & NAF Grade Translation
    if (totalScoreDisplay) totalScoreDisplay.textContent = totalScore;

    let gradeText = 'NAF A';
    let badgeClass = 'badge-naf-a';

    if (totalScore >= 11) {
      gradeText = 'NAF C (Severe Malnutrition)';
      badgeClass = 'badge-naf-c';
    } else if (totalScore >= 6) {
      gradeText = 'NAF B (Moderate Malnutrition)';
      badgeClass = 'badge-naf-b';
    } else {
      gradeText = 'NAF A (Normal - Mild)';
      badgeClass = 'badge-naf-a';
    }

    if (gradeBadge) {
      gradeBadge.textContent = gradeText;
      gradeBadge.className = 'badge ' + badgeClass + ' fs-5 w-100 py-2';
    }
  }

  // Bind change events
  form.addEventListener('input', calculateScore);
  form.addEventListener('change', calculateScore);

  // Initial run
  calculateScore();
});
