// ============================================
// DRIVEEASE - MAIN JAVASCRIPT
// ============================================

// Toast Notification System
function showToast(message, type = 'success', duration = 3500) {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const icons = { success: '✓', danger: '✕', warning: '⚠' };
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `<span style="font-size:16px">${icons[type] || 'ℹ'}</span><span>${message}</span>`;
  container.appendChild(toast);

  setTimeout(() => {
    toast.style.animation = 'toastOut 0.3s ease forwards';
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

// Modal System
function openModal(id) {
  const modal = document.getElementById(id);
  if (modal) {
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  }
}

// Close modal on backdrop click
document.addEventListener('click', function (e) {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('active');
    document.body.style.overflow = '';
  }
});

// ============================================
// COUNTDOWN TIMER
// ============================================

function updateCountdownTimers() {
  document.querySelectorAll('[data-due-date]').forEach(el => {
    const dueDate = new Date(el.dataset.dueDate + 'T23:59:59');
    const now = new Date();
    const diff = dueDate - now;

    if (diff <= 0) {
      // Overdue
      const overdueDays = Math.ceil(Math.abs(diff) / 86400000);
      el.innerHTML = `<span class="overdue-badge">⚠ ${overdueDays} day${overdueDays > 1 ? 's' : ''} overdue</span>`;
    } else {
      const days = Math.floor(diff / 86400000);
      const hours = Math.floor((diff % 86400000) / 3600000);
      const mins = Math.floor((diff % 3600000) / 60000);

      if (days > 0) {
        el.innerHTML = `
          <div class="rental-timer">
            <div class="timer-block"><div class="timer-value">${days}</div><div class="timer-label">days</div></div>
            <div class="timer-sep">:</div>
            <div class="timer-block"><div class="timer-value">${String(hours).padStart(2,'0')}</div><div class="timer-label">hrs</div></div>
            <div class="timer-sep">:</div>
            <div class="timer-block"><div class="timer-value">${String(mins).padStart(2,'0')}</div><div class="timer-label">min</div></div>
          </div>`;
      } else {
        el.innerHTML = `
          <div class="rental-timer">
            <div class="timer-block"><div class="timer-value" style="color:var(--warning)">${String(hours).padStart(2,'0')}</div><div class="timer-label">hrs</div></div>
            <div class="timer-sep" style="color:var(--warning)">:</div>
            <div class="timer-block"><div class="timer-value" style="color:var(--warning)">${String(mins).padStart(2,'0')}</div><div class="timer-label">min</div></div>
          </div>`;
      }
    }
  });
}

setInterval(updateCountdownTimers, 60000);
document.addEventListener('DOMContentLoaded', updateCountdownTimers);

// ============================================
// RENTAL COST CALCULATOR
// ============================================

function setupRentalCalculator() {
  const rentDate = document.getElementById('rent_date');
  const dueDate = document.getElementById('due_date');
  const priceDisplay = document.getElementById('calc-price');
  const daysDisplay = document.getElementById('calc-days');
  const totalDisplay = document.getElementById('calc-total');

  if (!rentDate || !dueDate) return;

  // Set min dates
  const today = new Date().toISOString().split('T')[0];
  rentDate.min = today;
  dueDate.min = today;

  rentDate.addEventListener('change', function () {
    dueDate.min = this.value;
    calculateCost();
  });

  dueDate.addEventListener('change', calculateCost);

  function calculateCost() {
    if (!rentDate.value || !dueDate.value) return;
    const start = new Date(rentDate.value);
    const end = new Date(dueDate.value);
    const days = Math.max(1, Math.ceil((end - start) / 86400000));
    const pricePerDay = parseFloat(document.querySelector('[data-price]')?.dataset.price || 0);
    const total = days * pricePerDay;

    if (daysDisplay) daysDisplay.textContent = days + ' day' + (days > 1 ? 's' : '');
    if (priceDisplay) priceDisplay.textContent = '$' + pricePerDay.toFixed(2) + '/day';
    if (totalDisplay) totalDisplay.textContent = '$' + total.toFixed(2);
  }
}

// ============================================
// TABLE SEARCH
// ============================================

function setupTableSearch(inputId, tableId) {
  const input = document.getElementById(inputId);
  const table = document.getElementById(tableId);
  if (!input || !table) return;

  input.addEventListener('input', function () {
    const val = this.value.toLowerCase();
    Array.from(table.querySelectorAll('tbody tr')).forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
  });
}

// ============================================
// CONFIRM DELETE
// ============================================

function confirmDelete(message, callback) {
  if (confirm(message || 'Are you sure you want to delete this?')) {
    callback();
  }
}

// ============================================
// FORM VALIDATION
// ============================================

function validateForm(formId) {
  const form = document.getElementById(formId);
  if (!form) return true;
  let valid = true;

  form.querySelectorAll('[required]').forEach(input => {
    if (!input.value.trim()) {
      input.style.borderColor = 'var(--danger)';
      valid = false;
    } else {
      input.style.borderColor = '';
    }
  });

  return valid;
}

// Sidebar toggle for mobile
function toggleSidebar() {
  document.querySelector('.sidebar').classList.toggle('open');
}

// Animated number counter
function animateCounter(el, target, duration = 1000) {
  let start = 0;
  const step = Math.ceil(target / (duration / 16));
  const timer = setInterval(() => {
    start = Math.min(start + step, target);
    el.textContent = start.toLocaleString();
    if (start >= target) clearInterval(timer);
  }, 16);
}

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-count]').forEach(el => {
    const target = parseFloat(el.dataset.count);
    if (!isNaN(target)) {
      el.textContent = '0';
      setTimeout(() => animateCounter(el, target), 200);
    }
  });

  setupRentalCalculator();
  setupTableSearch('search-cars', 'cars-table');
  setupTableSearch('search-rentals', 'rentals-table');
  setupTableSearch('search-users', 'users-table');
  setupTableSearch('search-payments', 'payments-table');
});
