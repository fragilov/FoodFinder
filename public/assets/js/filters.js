/* ============================================================
   FILTERS.JS — Trạng thái bộ lọc + debounce
   ============================================================ */

import { CONFIG } from './config.js';
import { categoryIcon } from './icons.js';

let onChange = null;
let debounceTimer = null;

/**
 * Đặt callback khi bộ lọc thay đổi
 */
export function setFilterChangeHandler(handler) {
  onChange = handler;
}

/**
 * Lấy các giá trị bộ lọc hiện tại từ state
 */
export function getActiveFilters(state) {
  const f = {};
  f.lat = state.user.lat;
  f.lng = state.user.lng;
  f.radius = state.filters.radius;

  if (state.filters.categoryId) f.category_id = state.filters.categoryId;
  if (state.filters.priceLevel) f.price_level = state.filters.priceLevel;
  if (state.filters.minRating) f.min_rating = state.filters.minRating;
  if (state.filters.openNow) f.open_now = 1;
  if (state.filters.q) f.q = state.filters.q;
  f.sort = state.filters.sort || 'distance';
  f.limit = CONFIG.DEFAULT_LIMIT;

  return f;
}

/**
 * Khởi tạo sự kiện cho bộ lọc
 */
export function initFilters(state) {
  // ── Radius selector ──
  const radiusSelect = document.getElementById('filter-radius');
  if (radiusSelect) {
    radiusSelect.value = state.filters.radius;
    radiusSelect.addEventListener('change', () => {
      state.filters.radius = parseFloat(radiusSelect.value);
      triggerChange();
    });
  }

  // ── Price chips ──
  document.querySelectorAll('[data-filter="price"]').forEach(chip => {
    chip.addEventListener('click', () => {
      const val = parseInt(chip.dataset.value);
      if (state.filters.priceLevel === val) {
        state.filters.priceLevel = null;
        chip.classList.remove('chip--active');
      } else {
        // Deactivate other price chips
        document.querySelectorAll('[data-filter="price"]').forEach(c => c.classList.remove('chip--active'));
        state.filters.priceLevel = val;
        chip.classList.add('chip--active');
      }
      triggerChange();
    });
  });

  // ── Open now chip ──
  const openChip = document.getElementById('filter-open');
  if (openChip) {
    openChip.addEventListener('click', () => {
      state.filters.openNow = !state.filters.openNow;
      openChip.classList.toggle('chip--active', state.filters.openNow);
      triggerChange();
    });
  }

  // ── Search inputs (debounced) ──
  const searchInputs = [
    document.getElementById('sidebar-search'),
    document.getElementById('sheet-search'),
  ];

  searchInputs.forEach(input => {
    if (!input) return;
    input.addEventListener('input', () => {
      state.filters.q = input.value.trim();
      // Sync both inputs
      searchInputs.forEach(other => {
        if (other && other !== input) other.value = input.value;
      });
      debounceChange();
    });
  });

  // ── Keyboard shortcut: / to focus search ──
  document.addEventListener('keydown', (e) => {
    if (e.key === '/' && !isInputFocused()) {
      e.preventDefault();
      const visibleInput = window.innerWidth >= 1024
        ? document.getElementById('sidebar-search')
        : document.getElementById('sheet-search');
      if (visibleInput) visibleInput.focus();
    }
  });
}

/**
 * Render category chips
 */
export function renderCategoryChips(categories, state) {
  const container = document.getElementById('category-chips');
  if (!container) return;

  container.innerHTML = '';
  categories.forEach(cat => {
    const chip = document.createElement('button');
    chip.className = 'chip';
    chip.type = 'button';
    chip.setAttribute('data-category-id', cat.id);
    chip.innerHTML = `${categoryIcon(cat)}<span>${cat.name}</span>`;

    chip.addEventListener('click', () => {
      if (state.filters.categoryId === cat.id) {
        state.filters.categoryId = null;
        chip.classList.remove('chip--active');
      } else {
        container.querySelectorAll('.chip').forEach(c => c.classList.remove('chip--active'));
        state.filters.categoryId = cat.id;
        chip.classList.add('chip--active');
      }
      triggerChange();
    });

    container.appendChild(chip);
  });
}

function triggerChange() {
  if (onChange) onChange();
}

function debounceChange() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    triggerChange();
  }, CONFIG.DEBOUNCE_MS);
}

function isInputFocused() {
  const el = document.activeElement;
  return el && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.isContentEditable);
}
