/**
 * PDH Nutrition - Diet Order Calculator JS
 */

document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('dietForm');
  if (!form) return;

  const heightInput = document.getElementById('height_cm');
  const genderInput = document.getElementById('gender');
  const kcalSelect = document.getElementById('energy_kcal_per_ibw');
  const proteinSelect = document.getElementById('protein_g_per_ibw');

  const ibwDisplay = document.getElementById('calc_ibw');
  const energyDisplay = document.getElementById('calc_total_energy');
  const proteinDisplay = document.getElementById('calc_total_protein');

  function calculateDiet() {
    const height = parseFloat(heightInput.value) || 0;
    const gender = genderInput.value || 'MALE';
    const kcalFactor = parseFloat(kcalSelect.value) || 30;
    const proteinFactor = parseFloat(proteinSelect.value) || 1.2;

    let ibw = 0;
    if (height > 0) {
      ibw = gender === 'FEMALE' ? Math.max(0, height - 105) : Math.max(0, height - 100);
    }

    const totalEnergy = Math.round(ibw * kcalFactor);
    const totalProtein = Math.round(ibw * proteinFactor * 10) / 10;

    if (ibwDisplay) ibwDisplay.textContent = ibw > 0 ? ibw.toFixed(1) + ' kg' : '-';
    if (energyDisplay) energyDisplay.textContent = totalEnergy > 0 ? totalEnergy + ' kcal/day' : '-';
    if (proteinDisplay) proteinDisplay.textContent = totalProtein > 0 ? totalProtein + ' g/day' : '-';
  }

  form.addEventListener('input', calculateDiet);
  form.addEventListener('change', calculateDiet);

  calculateDiet();
});
