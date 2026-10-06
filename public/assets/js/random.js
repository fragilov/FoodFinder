/* ============================================================
   RANDOM.JS — Tính năng "Hôm nay ăn gì?"
   ============================================================ */

import { CONFIG } from './config.js';
import { fetchRandom } from './api.js';
import { formatDistance, formatPrice, isOpenNow } from './format.js';
import { categoryIcon, icon } from './icons.js';

const STORAGE_KEY = 'food-finder-random-exclude';

/**
 * Lấy danh sách id đã quay gần nhất
 */
function getExcludeIds() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch {
    return [];
  }
}

/**
 * Lưu id vừa quay
 */
function saveExcludeId(id) {
  const ids = getExcludeIds();
  ids.unshift(id);
  // Giữ tối đa RANDOM_EXCLUDE_COUNT id
  const trimmed = ids.slice(0, CONFIG.RANDOM_EXCLUDE_COUNT);
  localStorage.setItem(STORAGE_KEY, JSON.stringify(trimmed));
}

/**
 * Quay ngẫu nhiên
 */
export async function rollRandom(filters) {
  const exclude = getExcludeIds();
  const result = await fetchRandom({
    ...filters,
    exclude,
  });

  if (result.success && result.data) {
    saveExcludeId(result.data.id);
    return result.data;
  }
  return null;
}

/**
 * Hiển thị overlay randomizer
 */
export function showRandomCard(restaurant, { onReroll, onClose, getDirectionsUrl }) {
  const overlay = document.getElementById('random-overlay');
  const nameEl = document.getElementById('random-name');
  const imgEl = document.getElementById('random-img');
  const metaEl = document.getElementById('random-meta');
  const dirBtn = document.getElementById('random-directions');
  const rerollBtn = document.getElementById('random-reroll');
  const closeBtn = document.getElementById('random-close');

  if (!overlay || !restaurant) return;

  // Set data (textContent for XSS safety)
  nameEl.textContent = restaurant.name;
  imgEl.src = restaurant.image_url || CONFIG.PLACEHOLDER_IMG;
  imgEl.alt = restaurant.name;
  imgEl.onerror = () => { imgEl.src = CONFIG.PLACEHOLDER_IMG; };

  // Meta info
  metaEl.innerHTML = '';
  const openStatus = isOpenNow(restaurant.open_time, restaurant.close_time, restaurant.is_24h);

  // Category
  if (restaurant.category?.name) {
    const cat = document.createElement('span');
    cat.className = 'badge badge--category';
    cat.innerHTML = `${categoryIcon(restaurant.category)}<span>${restaurant.category.name}</span>`;
    metaEl.appendChild(cat);
  }

  // Open status badge
  if (openStatus !== null) {
    const badge = document.createElement('span');
    badge.className = `badge ${openStatus ? 'badge--open' : 'badge--closed'}`;
    badge.textContent = openStatus ? 'Đang mở' : 'Đã đóng';
    metaEl.appendChild(badge);
  }

  // Price
  const price = formatPrice(restaurant.price_min, restaurant.price_max);
  if (price) {
    const priceEl = document.createElement('span');
    priceEl.textContent = price;
    metaEl.appendChild(priceEl);
  }

  // Distance
  if (restaurant.distance_km !== undefined) {
    const distEl = document.createElement('span');
    distEl.style.fontWeight = '600';
    distEl.textContent = formatDistance(restaurant.distance_km);
    metaEl.appendChild(distEl);
  }

  // Rating
  if (restaurant.rating > 0) {
    const ratingEl = document.createElement('span');
    ratingEl.innerHTML = `${icon('star', 'ui-icon--star-filled')} <span>${restaurant.rating.toFixed(1)}</span>`;
    ratingEl.style.display = 'inline-flex';
    ratingEl.style.alignItems = 'center';
    ratingEl.style.gap = '3px';
    ratingEl.style.fontWeight = '700';
    metaEl.appendChild(ratingEl);
  }

  // Directions URL
  if (getDirectionsUrl) {
    dirBtn.href = getDirectionsUrl(restaurant);
  }

  // Show overlay
  overlay.classList.add('random-overlay--visible');

  // Event handlers — cleanup previous
  const newReroll = rerollBtn.cloneNode(true);
  rerollBtn.parentNode.replaceChild(newReroll, rerollBtn);
  newReroll.addEventListener('click', () => { if (onReroll) onReroll(); });

  const newClose = closeBtn.cloneNode(true);
  closeBtn.parentNode.replaceChild(newClose, closeBtn);
  newClose.addEventListener('click', () => {
    hideRandomCard();
    if (onClose) onClose();
  });

  // Close on overlay click
  const handleOverlayClick = (e) => {
    if (e.target === overlay) {
      hideRandomCard();
      overlay.removeEventListener('click', handleOverlayClick);
      if (onClose) onClose();
    }
  };
  overlay.addEventListener('click', handleOverlayClick);

  // Close on Esc
  const handleEsc = (e) => {
    if (e.key === 'Escape') {
      hideRandomCard();
      document.removeEventListener('keydown', handleEsc);
      if (onClose) onClose();
    }
  };
  document.addEventListener('keydown', handleEsc);
}

/**
 * Ẩn overlay randomizer
 */
export function hideRandomCard() {
  const overlay = document.getElementById('random-overlay');
  if (overlay) overlay.classList.remove('random-overlay--visible');
}
